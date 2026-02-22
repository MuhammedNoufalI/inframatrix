<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\Server;
use App\Models\GitProvider;
use App\Models\IntegrationType;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProjectImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    use HandlesFuzzyMatching;

    public int $createdCount = 0;
    public int $updatedCount = 0;
    public int $skippedCount = 0;
    public int $failedCount = 0;
    public array $errors = [];
    public int $currentRow = 1;

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            $pUids = $rows->map(fn($row) => trim($row['project_import_uid_do_not_modify'] ?? ($row['project_import_uid'] ?? '')))->filter()->toArray();
            $projectNames = $rows->pluck('project_name')->filter()->unique()->toArray();
            if (empty($projectNames) && empty($pUids)) return;

            // Unified Object Memory Mapping
            $allProjects = Project::with(['environments.integrations', 'users'])
                ->whereIn('name', $projectNames)
                ->orWhereIn('import_uid', $pUids)
                ->get();

            $projectsByUid = $allProjects->keyBy('import_uid');
            $projectsByName = $allProjects->keyBy(function($item) { return strtolower($item->name); });
            $servers = Server::whereIn('server_name', $rows->pluck('server_name')->filter()->unique()->toArray())->get()->keyBy(function($item) { return strtolower($item->server_name); });
            $gitProviders = GitProvider::whereIn('name', $rows->pluck('git_provider_name')->filter()->unique()->toArray())->get()->keyBy(function($item) { return strtolower($item->name); });
            $integrationTypes = IntegrationType::whereIn('name', $rows->pluck('integration_type_name')->filter()->unique()->toArray())->get()->keyBy(function($item) { return strtolower($item->name); });
            $accounts = Account::whereIn('account_name', $rows->pluck('integration_account_name')->filter()->unique()->toArray())->get()->keyBy(function($item) { return strtolower($item->account_name); });
            $users = User::whereIn('email', $rows->pluck('assignment_user_email')->filter()->unique()->toArray())->get()->keyBy(function($item) { return strtolower($item->email); });

            $allowedStatuses = ['active', 'on_hold', 'archived'];
            $allowedEnvTypes = ['staging', 'uat', 'live'];
            $allowedRoles = ['owner', 'editor', 'viewer'];

            foreach ($rows as $row) {
                $this->currentRow++;
                $pUid = trim($row['project_import_uid_do_not_modify'] ?? ($row['project_import_uid'] ?? ''));
                $projectName = trim($row['project_name'] ?? '');
                
                $project = null;
                if ($pUid) {
                    $project = $projectsByUid->get($pUid);
                    if (!$project) {
                        $this->failedCount++;
                        $this->errors[] = [$this->currentRow, 'project_import_uid', $pUid, 'Invalid project UUID.', 'Record not found. Do not invent UUIDs.'];
                        continue;
                    }
                } else {
                    if (!$projectName) {
                        $this->failedCount++;
                        $this->errors[] = [$this->currentRow, 'project_name', '', 'Project Name missing', 'Required constraints'];
                        continue;
                    }
                    $project = $projectsByName->get(strtolower($projectName));
                }

                $rowChanged = false;
                
                $statusRaw = trim($row['project_status'] ?? 'active');
                $status = $this->fuzzyMatch($statusRaw, $allowedStatuses);
                if (!$status) {
                    $this->failedCount++;
                    $this->errors[] = [$this->currentRow, 'project_status', $statusRaw, 'Invalid status.', implode(', ', $allowedStatuses)];
                    continue;
                }

                $pData = [
                    'status' => $status,
                    'notes' => $row['project_notes'] ?? null,
                ];

                if ($project) {
                    $pNeedsUpdate = false;
                    $pUpdateData = [];
                    if ($projectName && $project->name !== $projectName) {
                        $pUpdateData['name'] = $projectName;
                        $project->name = $projectName;
                        $pNeedsUpdate = true;
                    }
                    if ($project->status !== $pData['status']) {
                        $pUpdateData['status'] = $pData['status'];
                        $project->status = $pData['status'];
                        $pNeedsUpdate = true;
                    }
                    if ($project->notes !== $pData['notes']) {
                        $pUpdateData['notes'] = $pData['notes'];
                        $project->notes = $pData['notes'];
                        $pNeedsUpdate = true;
                    }
                    
                    if ($pNeedsUpdate) {
                        $project->update($pUpdateData);
                        $this->updatedCount++;
                        $rowChanged = true;
                    }
                } else {
                    $project = Project::create(array_merge(['name' => $projectName], $pData));
                    $project->setRelation('environments', collect()); // Init
                    $project->setRelation('users', collect()); // Init
                    $projectsByName->put(strtolower($projectName), $project);
                    $projectsByUid->put($project->import_uid, $project);
                    $this->createdCount++;
                    $rowChanged = true;
                }

                // 2. Process Environment
                $envUid = trim($row['environment_import_uid_do_not_modify'] ?? ($row['environment_import_uid'] ?? ''));
                $envTypeRaw = trim($row['environment_type'] ?? '');
                $envType = null;
                if ($envTypeRaw) {
                    $envType = $this->fuzzyMatch($envTypeRaw, $allowedEnvTypes);
                    if (!$envType) {
                        $this->failedCount++;
                        $this->errors[] = [$this->currentRow, 'environment_type', $envTypeRaw, 'Invalid environment type selected.', implode(', ', $allowedEnvTypes)];
                        continue;
                    }
                }

                $environment = null;
                if ($envUid) {
                    $environment = $project->environments->where('import_uid', $envUid)->first();
                    if (!$environment) {
                        $this->failedCount++;
                        $this->errors[] = [$this->currentRow, 'environment_import_uid', $envUid, 'Invalid environment UUID.', 'Record not found. Do not invent UUIDs.'];
                        continue;
                    }
                } else if ($envType) {
                    $environment = $project->environments->where('type', $envType)->first();
                }

                if ($envUid || $envType) {

                    $sNameRaw = trim($row['server_name'] ?? '');
                    $serverId = null;
                    if ($sNameRaw) {
                        $sName = $this->fuzzyMatch($sNameRaw, array_keys($servers->toArray()));
                        if (!$sName) {
                            $this->failedCount++;
                            $this->errors[] = [$this->currentRow, 'server_name', $sNameRaw, 'Server does not exist in master data.', 'Allowed server names'];
                            continue;
                        }
                        $serverId = $servers->get($sName)->id;
                    }

                    $gNameRaw = trim($row['git_provider_name'] ?? '');
                    $gitId = null;
                    if ($gNameRaw) {
                        $gName = $this->fuzzyMatch($gNameRaw, array_keys($gitProviders->toArray()));
                        if (!$gName) {
                            $this->failedCount++;
                            $this->errors[] = [$this->currentRow, 'git_provider_name', $gNameRaw, 'Git Provider does not exist in master data.', 'Allowed providers'];
                            continue;
                        }
                        $gitId = $gitProviders->get($gName)->id;
                    }

                    $eData = [
                        'url' => $row['environment_url'] ?? null,
                        'server_id' => $serverId,
                        'git_provider_id' => $gitId,
                        'repo_url' => $row['repo_url'] ?? null,
                        'repo_branch' => $row['repo_branch'] ?? null,
                        'cicd_configured' => strtolower(trim($row['cicd_configured'] ?? 'no')) === 'yes' || $row['cicd_configured'] == 1,
                        'cicd_not_configured_reason' => $row['cicd_reason'] ?? null,
                    ];

                    if ($environment) {
                        $eChanged = false;
                        $eUpdateData = [];
                        
                        if ($envType && $environment->type !== $envType) {
                            $eUpdateData['type'] = $envType;
                            $environment->type = $envType;
                            $eChanged = true;
                        }

                        foreach($eData as $k => $v) {
                            if ($environment->$k != $v) {
                                $eUpdateData[$k] = $v;
                                $environment->$k = $v;
                                $eChanged = true;
                            }
                        }
                        if ($eChanged) {
                            $environment->update($eUpdateData);
                            $this->updatedCount++;
                            $rowChanged = true;
                        }
                    } else {
                        if (!$envType) {
                            $this->failedCount++;
                            $this->errors[] = [$this->currentRow, 'environment_type', '', 'Missing Type for new Environment', 'Required for new records'];
                            continue;
                        }
                        $environment = $project->environments()->create(array_merge(['type' => $envType], $eData));
                        $environment->setRelation('integrations', collect());
                        $project->environments->push($environment);
                        $this->createdCount++;
                        $rowChanged = true;
                    }
                }

                // 3. Process Integration (Requires Environment)
                $iTypeNameRaw = trim($row['integration_type_name'] ?? '');
                if ($environment && $iTypeNameRaw) {
                    $iTypeName = $this->fuzzyMatch($iTypeNameRaw, array_keys($integrationTypes->toArray()));
                    if (!$iTypeName) {
                        $this->failedCount++;
                        $this->errors[] = [$this->currentRow, 'integration_type_name', $iTypeNameRaw, 'Integration type does not exist in master data.', 'Allowed integration types'];
                        continue;
                    }

                    $iType = $integrationTypes->get($iTypeName);
                    
                    $aNameRaw = trim($row['integration_account_name'] ?? '');
                    $accountId = null;
                    if ($aNameRaw) {
                        $aName = $this->fuzzyMatch($aNameRaw, array_keys($accounts->toArray()));
                        if (!$aName) {
                            $this->failedCount++;
                            $this->errors[] = [$this->currentRow, 'integration_account_name', $aNameRaw, 'Integration account does not exist in master data.', 'Allowed integration accounts'];
                            continue;
                        }
                        $accountId = $accounts->get($aName)->id;
                    }

                    $iValue = $row['integration_identifier'] ?? null;
                        
                        $integration = $environment->integrations->where('integration_type_id', $iType->id)
                            ->filter(function($i) use ($accountId, $iValue) {
                                if ($accountId) return $i->account_id === $accountId;
                                return $i->value === $iValue;
                            })->first();

                        if ($integration) {
                            if ($accountId && $iValue && $integration->value !== $iValue) {
                                $integration->update(['value' => $iValue]);
                                $this->updatedCount++;
                                $rowChanged = true;
                            }
                        } else {
                            $newInteg = $environment->integrations()->create([
                                'integration_type_id' => $iType->id,
                                'account_id' => $accountId,
                                'value' => $iValue,
                            ]);
                            $environment->integrations->push($newInteg);
                            $this->createdCount++;
                        }
                }

                // 4. Process Assignment
                $userEmail = trim($row['assignment_user_email'] ?? '');
                if ($userEmail) {
                    $user = $users->get(strtolower($userEmail));
                    if (!$user) {
                        $user = User::create([
                            'name' => explode('@', $userEmail)[0],
                            'email' => $userEmail,
                            'password' => Hash::make('password')
                        ]);
                        $users->put(strtolower($userEmail), $user);
                    }

                    $roleRaw = trim($row['assignment_role'] ?? 'viewer');
                    $role = $this->fuzzyMatch($roleRaw, $allowedRoles);
                    if (!$role) {
                        $this->failedCount++;
                        $this->errors[] = [$this->currentRow, 'assignment_role', $roleRaw, 'Invalid assignment role.', implode(', ', $allowedRoles)];
                        continue;
                    }

                    $assignment = $project->users->where('id', $user->id)->first();

                    if ($assignment) {
                        if ($assignment->pivot->role !== $role) {
                            $project->users()->updateExistingPivot($user->id, ['role' => $role]);
                            // Manually patch pivot in memory collection
                            $assignment->pivot->role = $role;
                            $this->updatedCount++;
                            $rowChanged = true;
                        }
                    } else {
                        $project->users()->attach($user->id, ['role' => $role]);
                        $freshPivot = \Illuminate\Database\Eloquent\Relations\Pivot::fromAttributes($project, ['role' => $role, 'project_id' => $project->id, 'user_id' => $user->id], 'project_user', true);
                        $user->setRelation('pivot', $freshPivot);
                        $project->users->push($user);
                        $this->createdCount++;
                        $rowChanged = true;
                    }
                }

                if (!$rowChanged) {
                    $this->skippedCount++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
