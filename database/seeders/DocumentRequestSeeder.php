<?php

namespace Database\Seeders;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $documentTypeNames = ['NOC', 'Salary Slip'];
        $statuses = ['pending', 'approved', 'rejected', 'processed', 'forwarded', 'approved and forwarded'];

        // Ensure initial document types exist
        foreach ($documentTypeNames as $typeName) {
            DocumentType::firstOrCreate(['name' => $typeName]);
        }

        foreach ($users as $user) {
            for ($i = 0; $i < 2; $i++) { // Create 2 requests per user
                $selectedDocumentTypeName = $documentTypeNames[$i % 2];
                $documentType = DocumentType::where('name', $selectedDocumentTypeName)->first();

                DocumentRequest::create([
                    'user_id' => $user->id,
                    'remarks' => 'Remarks for ' . $selectedDocumentTypeName . ' request by ' . $user->name,
                    'document_type_id' => $documentType->id,
                    'status' => $statuses[array_rand($statuses)],
                    'requested_date' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
}
