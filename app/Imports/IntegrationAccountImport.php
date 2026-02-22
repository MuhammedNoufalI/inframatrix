<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\IntegrationType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class IntegrationAccountImport implements ToCollection, WithHeadingRow, WithChunkReading
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
        $names = $rows->pluck('account_name')->filter()->toArray();
        if (empty($names) && empty($uids)) return;

        $integrationTypes = IntegrationType::all()->keyBy('name');
        
        $existingByUid = Account::whereIn('import_uid', $uids)->get()->keyBy('import_uid');
        
        // Key by lowercase composite string to protect against case-insensitive MySQL dupes
        $existingByName = Account::whereIn('account_name', $names)->get()->keyBy(function($item) {
            return strtolower($item->account_name) . '-' . $item->integration_type_id;
        });

        foreach ($rows as $row) {
            $this->currentRow++;

            $importUid = trim($row['import_uid_do_not_modify'] ?? ($row['import_uid'] ?? ''));

            $iTypeNameRaw = trim($row['integration_type_name'] ?? '');
            if (!$iTypeNameRaw) {
                $this->failedCount++;
                $this->errors[] = [$this->currentRow, 'integration_type_name', '', 'Integration Type missing', 'Required'];
                continue;
            }

            $iTypeName = $this->fuzzyMatch($iTypeNameRaw, array_keys($integrationTypes->toArray()));
            if (!$iTypeName) {
                $this->failedCount++;
                $this->errors[] = [$this->currentRow, 'integration_type_name', $iTypeNameRaw, 'Integration Type does not exist.', 'Provide a valid integration type'];
                continue;
            }

            $iType = $integrationTypes->get($iTypeName);

            $name = trim($row['account_name'] ?? '');
            if (!$name) {
                $this->failedCount++;
                $this->errors[] = [$this->currentRow, 'account_name', '', 'Account Name cannot be empty', 'Provide a valid string'];
                continue;
            }

            $notes = $row['notes'] ?? null;
            $record = null;

            if ($importUid) {
                $record = $existingByUid->get($importUid);
                if (!$record) {
                    $this->failedCount++;
                    $this->errors[] = [$this->currentRow, 'import_uid', $importUid, 'Invalid import_uid', 'Record not found. Do not invent UUIDs.'];
                    continue;
                }
            } else {
                $record = $existingByName->get(strtolower($name) . '-' . $iType->id);
            }

            if ($record) {
                $needsUpdate = false;
                $updateData = [];

                if ($record->notes !== $notes) {
                    $updateData['notes'] = $notes;
                    $record->notes = $notes;
                    $needsUpdate = true;
                }
                if ($record->account_name !== $name) {
                    $updateData['account_name'] = $name;
                    $record->account_name = $name;
                    $needsUpdate = true;
                }
                if ($record->integration_type_id !== $iType->id) {
                    $updateData['integration_type_id'] = $iType->id;
                    $record->integration_type_id = $iType->id;
                    $needsUpdate = true;
                }

                if ($needsUpdate) {
                    $record->update($updateData);
                    $this->updatedCount++;
                } else {
                    $this->skippedCount++;
                }
            } else {
                $newRecord = Account::create([
                    'integration_type_id' => $iType->id,
                    'account_name' => $name,
                    'notes' => $notes,
                ]);
                $existingByName->put(strtolower($name) . '-' . $iType->id, $newRecord);
                $this->createdCount++;
            }
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
