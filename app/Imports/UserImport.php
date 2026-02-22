<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class UserImport implements ToCollection, WithHeadingRow, WithChunkReading
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
        $emails = $rows->pluck('email')->filter()->toArray();
        if (empty($emails) && empty($uids)) return;

        $existingByUid = User::whereIn('import_uid', $uids)->with('roles')->get()->keyBy('import_uid');
        $existingByName = User::whereIn('email', $emails)->with('roles')->get()->keyBy(function($item) {
            return strtolower($item->email);
        });

        foreach ($rows as $row) {
            $this->currentRow++;

            $importUid = trim($row['import_uid_do_not_modify'] ?? ($row['import_uid'] ?? ''));
            $email = trim($row['email'] ?? '');
            if (!$email) {
                $this->failedCount++;
                $this->errors[] = [$this->currentRow, 'email', '', 'User Email missing', 'Required'];
                continue;
            }

            $name = trim($row['name'] ?? explode('@', $email)[0]);
            $roleRaw = trim($row['role'] ?? '');
            
            $allowedRoles = ['admin', 'infra_admin', 'infra_user'];
            $role = null;
            if ($roleRaw) {
                $role = $this->fuzzyMatch($roleRaw, $allowedRoles);
                if (!$role) {
                    $this->failedCount++;
                    $this->errors[] = [$this->currentRow, 'role', $roleRaw, 'Invalid role.', 'Leave blank for viewer, or: ' . implode(', ', $allowedRoles)];
                    continue;
                }
            }

            $password = !empty($row['password']) ? Hash::make($row['password']) : null;
            
            $record = null;
            if ($importUid) {
                $record = $existingByUid->get($importUid);
                if (!$record) {
                    $this->failedCount++;
                    $this->errors[] = [$this->currentRow, 'import_uid', $importUid, 'Invalid import_uid', 'Record not found. Do not invent UUIDs.'];
                    continue;
                }
            } else {
                $record = $existingByName->get(strtolower($email));
            }

            if ($record) {
                $changed = false;
                $updateData = [];

                if ($record->name !== $name) $updateData['name'] = $name;
                if ($record->email !== $email) $updateData['email'] = $email;

                if (!empty($updateData)) {
                    $record->update($updateData);
                }
                
                // Role sync logic
                $currentRole = $record->roles->first() ? $record->roles->first()->name : null;
                $roleChanged = false;

                if ($role && $currentRole !== $role) {
                    $record->syncRoles([$role]);
                    $roleChanged = true;
                } elseif (!$role && $currentRole) {
                    $record->roles()->detach();
                    $roleChanged = true;
                }

                if (!empty($updateData) || $roleChanged) {
                    $this->updatedCount++;
                } else {
                    $this->skippedCount++;
                }
            } else {
                $newUser = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                ]);
                $existingByName->put(strtolower($email), $newUser);
                if ($role) $newUser->assignRole($role);
                $this->createdCount++;
            }
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
