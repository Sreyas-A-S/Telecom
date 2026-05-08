<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Product;
use App\Models\ProductModel;
use Faker\Factory as Faker;
use App\Models\Dealership;
use App\Models\Employee;
use App\Models\Agent;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

        public function run(): void

        {

            $faker = Faker::create();

            $leads = Lead::whereNull('client_id')->get(); // Get leads not yet converted to clients

            $productIds = Product::pluck('id')->toArray();

            $dealershipIds = Dealership::pluck('id')->toArray();

            $employees = Employee::all();

            $agents = Agent::all();

    

            if (empty($dealershipIds) || $employees->isEmpty() || $agents->isEmpty()) {

                $this->command->info('Cannot run ClientSeeder. Please make sure there are dealerships, employees, and agents in the database.');

                return;

            }

    

            $leadsNeeded = 20 - $leads->count();

            if ($leadsNeeded > 0) {

                Lead::factory()->count($leadsNeeded)->create();

            }

    

            Client::factory()->count(20)->create();

        }
}