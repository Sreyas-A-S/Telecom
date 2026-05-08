<?php

namespace Database\Seeders;

use App\Models\MenuGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MenuGroup::updateOrCreate(['id' => 1], ['name' => 'Settings']);
        MenuGroup::updateOrCreate(['id' => 2], ['name' => 'Human Resources']);
        MenuGroup::updateOrCreate(['id' => 3], ['name' => 'Sales']);
        MenuGroup::updateOrCreate(['id' => 4], ['name' => 'Clients']);
        MenuGroup::updateOrCreate(['id' => 5], ['name' => 'Services']);
        MenuGroup::updateOrCreate(['id' => 6], ['name' => 'Parts']);
        MenuGroup::updateOrCreate(['id' => 7], ['name' => 'Requests']);
        MenuGroup::updateOrCreate(['id' => 8], ['name' => 'Tasks']);
        MenuGroup::updateOrCreate(['id' => 9], ['name' => 'Monitoring']);
        MenuGroup::updateOrCreate(['id' => 10], ['name' => 'Reports']);
    }
}
