<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Lead;
use App\Models\Followup;

class LeadFollowupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $leads = Lead::all();

        $leads->each(function ($lead) {
            // For each lead, create 0 to 5 follow-ups
            Followup::factory(rand(0, 5))->create(['lead_id' => $lead->id]);
        });
    }
}
