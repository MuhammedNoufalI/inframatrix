<?php

namespace App\Imports;

use App\Models\Server;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ServerImport implements ToCollection, WithHeadingRow, WithChunkReading
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
        $uids = $rows->map(fn($row) => trim($row['import_uid_do_not_modify'] ?? ($row['import_uid'] ?? '')))->filter()->toArray();
        $names = $rows->pluck('server_name')->filter()->toArray();
        if (empty($names) && empty($uids)) return;

        $existingByUid = Server::whereIn('import_uid', $uids)->get()->keyBy('import_uid');
        $existingByName = Server::whereIn('server_name', $names)->get()->keyBy(function($item) {
            return strtolower($item->server_name);
        });
        
        // Cache dynamic providers/panels for fuzzy matching
        $allowedProviders = array_unique(array_merge(['Azure', 'AWS', 'Contabo', 'On-prem', 'Other'], Server::whereNotNull('provider')->pluck('provider')->toArray()));
        $allowedPanels = array_unique(array_merge(['CloudPanel', 'Plesk', 'None'], Server::whereNotNull('panel')->pluck('panel')->toArray()));
        $allowedStatuses = ['active', 'maintenance', 'decommissioned'];

        foreach ($rows as $row) {
            $this->currentRow++;
            
            $importUid = trim($row['import_uid_do_not_modify'] ?? ($row['import_uid'] ?? ''));
            
            $name = trim($row['server_name'] ?? '');
            if (!$name) {
                $this->failedCount++;
                $this->errors[] = [$this->currentRow, 'server_name', '', 'Server Name missing', 'Required'];
                continue;
            }

            $providerRaw = trim($row['provider'] ?? 'Other');
            $provider = $this->fuzzyMatch($providerRaw, $allowedProviders);
            if (!$provider) {
                // If fuzzy match fails and standard match fails, we mark as validation error per user request (Do not auto-create master data strings)
                 $this->failedCount++;
                  $this->errors[] = [$this->currentRow, 'provider', $providerRaw, "Could not map provider using fuzzy rules.", implode(', ', $allowedProviders)];
                 continue;
            }

            $panelRaw = trim($row['panel'] ?? 'None');
            $panel = $this->fuzzyMatch($panelRaw, $allowedPanels);
            if (!$panel) {
                 $this->failedCount++;
                 $this->errors[] = [$this->currentRow, 'panel', $panelRaw, "Could not map panel using fuzzy rules.", implode(', ', $allowedPanels)];
                 continue;
            }

            $statusRaw = trim($row['status'] ?? 'active');
            $status = $this->fuzzyMatch($statusRaw, $allowedStatuses);
            if (!$status) {
                 $this->failedCount++;
                 $this->errors[] = [$this->currentRow, 'status', $statusRaw, "Invalid status.", implode(', ', $allowedStatuses)];
                 continue;
            }

            // Process booleans
            $amcRaw = trim($row['amc'] ?? 'no');
            $amc = strtolower($amcRaw) === 'yes' || $amcRaw == 1;

            $isActiveRaw = trim($row['is_active'] ?? 'yes');
            $isActive = strtolower($isActiveRaw) === 'yes' || $isActiveRaw == 1;

            $data = [
                'server_name' => $name,
                'subscription_name' => $row['subscription_name'] ?? null,
                'location' => $row['location'] ?? null,
                'provider' => $provider,
                'panel' => $panel,
                'os_name' => $row['os_name'] ?? null,
                'os_version' => $row['os_version'] ?? null,
                'public_ip' => $row['public_ip'] ?? null,
                'private_ip' => $row['private_ip'] ?? null,
                'status' => $status,
                'amc' => $amc,
                'is_active' => $isActive,
            ];

            $record = null;
            if ($importUid) {
                $record = $existingByUid->get($importUid);
                if (!$record) {
                    $this->failedCount++;
                    $this->errors[] = [$this->currentRow, 'import_uid', $importUid, 'Invalid import_uid', 'Record not found. Do not invent UUIDs.'];
                    continue;
                }
            } else {
                $record = $existingByName->get(strtolower($name));
            }
            
            if ($record) {
                $changed = false;
                foreach($data as $key => $val) {
                    if ($record->$key != $val) {
                        $changed = true;
                        break;
                    }
                }

                if ($changed) {
                    $record->update($data);
                    $this->updatedCount++;
                } else {
                    $this->skippedCount++;
                }
            } else {
                $newServer = Server::create($data);
                $existingByName->put(strtolower($name), $newServer);
                $this->createdCount++;
            }
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
