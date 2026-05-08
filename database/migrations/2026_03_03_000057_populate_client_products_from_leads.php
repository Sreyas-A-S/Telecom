<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all leads that represent a client product (e.g. status converted_to_client or win)
        $leads = DB::table('leads')
            ->whereNotNull('client_id')
            ->where(function($query) {
                $query->whereIn('status', ['converted_to_client', 'win']);
            })
            ->get();

        foreach ($leads as $lead) {
            // Check if it already exists in client_products to avoid duplicates
            $exists = DB::table('client_products')
                ->where('client_id', $lead->client_id)
                ->where('product_id', $lead->product_id)
                ->where('product_model_id', $lead->product_model_id)
                ->where('model_series_id', $lead->model_series_id)
                ->exists();

            if (!$exists) {
                DB::table('client_products')->insert([
                    'client_id' => $lead->client_id,
                    'product_id' => $lead->product_id,
                    'product_model_id' => $lead->product_model_id,
                    'model_series_id' => $lead->model_series_id,
                    'doc' => $lead->doc,
                    'engine_model' => $lead->engine_model,
                    'dealership_id' => $lead->dealership_id,
                    'import_id' => $lead->import_id,
                    'created_at' => $lead->created_at,
                    'updated_at' => $lead->updated_at,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('client_products')->truncate();
    }
};
