<?php

namespace App\Imports;

use App\Models\IntegrationType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class IntegrationTypeImport implements ToCollection, WithHeadingRow, WithChunkReading
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
        $names = $rows->pluck('name')->filter()->toArray();
        if (empty($names) && empty($uids)) return;

        // Eager load existing to prevent N+1
        $existingByUid = IntegrationType::whereIn('import_uid', $uids)->get()->keyBy('import_uid');
        $existingByName = IntegrationType::whereIn('name', $names)->get()->keyBy(function($item) {
            return strtolower($item->name);
        });
        $allowedBehaviors = ['generic_value', 'account_select_optional', 'sendgrid_id', 'recaptcha_account'];

        foreach ($rows as $row) {
            $this->currentRow++;

            $importUid = trim($row['import_uid_do_not_modify'] ?? ($row['import_uid'] ?? ''));
            $name = trim($row['name'] ?? '');
            
            if (!$name) {
                $this->failedCount++;
                $this->errors[] = [$this->currentRow, 'name', '', 'Integration Type Name missing', 'Required'];
                continue;
            }

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
            
            $behaviorRaw = trim($row['behavior'] ?? 'generic_value');
            $behavior = $this->fuzzyMatch($behaviorRaw, $allowedBehaviors);
            if (!$behavior) {
                $this->failedCount++;
                $this->errors[] = [$this->currentRow, 'behavior', $behaviorRaw, 'Invalid behavior mapped.', implode(', ', $allowedBehaviors)];
                continue;
            }

            $isActiveRaw = trim($row['is_active'] ?? 'yes');
            $isActive = strtolower($isActiveRaw) === 'yes' || $isActiveRaw == 1;

            if ($record) {
                $needsUpdate = false;
                $updateData = [];

                if ($record->behavior !== $behavior) {
                    $updateData['behavior'] = $behavior;
                    $record->behavior = $behavior;
                    $needsUpdate = true;
                }
                if ($record->is_active != $isActive) {
                    $updateData['is_active'] = $isActive;
                    $record->is_active = $isActive;
                    $needsUpdate = true;
                }
                if ($record->name !== $name) {
                    $updateData['name'] = $name;
                    $record->name = $name;
                    $needsUpdate = true;
                }

                if ($needsUpdate) {
                    $record->update($updateData);
                    $this->updatedCount++;
                } else {
                    $this->skippedCount++;
                }
            } else {
                $newIntegrationType = IntegrationType::create([
                    'name' => $name,
                    'behavior' => $behavior,
                    'is_active' => $isActive,
                ]);
                $existingByName->put(strtolower($name), $newIntegrationType);
                $this->createdCount++;
            }
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
