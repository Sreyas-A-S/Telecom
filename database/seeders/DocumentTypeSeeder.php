<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentType;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $documentTypes = [
            ['name' => 'NOC'],
            ['name' => 'Salary Slip'],
            ['name' => 'Experience Letter'],
            ['name' => 'Relieving Letter'],
            ['name' => 'Offer Letter'],
        ];

        foreach ($documentTypes as $type) {
            DocumentType::firstOrCreate($type);
        }
    }
}
