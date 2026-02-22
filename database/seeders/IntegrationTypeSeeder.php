<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IntegrationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'SendGrid',
                'behavior' => 'generic_value',
            ],
            [
                'name' => 'reCAPTCHA',
                'behavior' => 'account_select_optional',
            ],
        ];

        foreach ($types as $type) {
            \App\Models\IntegrationType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
