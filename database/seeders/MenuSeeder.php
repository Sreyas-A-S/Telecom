<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            ['name' => 'Dealerships', 'menu_group_id' => 1], // 1
            ['name' => 'Zones', 'menu_group_id' => 1], // 2
            ['name' => 'Roles', 'menu_group_id' => 1], // 3
            ['name' => 'Employees', 'menu_group_id' => 2], // 4
            ['name' => 'Assigned Leads', 'menu_group_id' => 3], // 5
            ['name' => 'Products', 'menu_group_id' => 3], // 6
            ['name' => 'Products Meta', 'menu_group_id' => 3], // 7
            ['name' => 'Clients Management', 'menu_group_id' => 4], // 8
            ['name' => 'Loss Order', 'menu_group_id' => 3], // 9
            ['name' => 'Pipeline', 'menu_group_id' => 3], // 10
            ['name' => 'Follow Up', 'menu_group_id' => 3], // 11
            ['name' => 'Unassigned Leads', 'menu_group_id' => 3], // 12
            ['name' => 'Assign Employee to Lead', 'menu_group_id' => 3], // 13
            ['name' => 'Convert Lead Into Client', 'menu_group_id' => 3], // 14
            ['name' => 'Manage Permissions', 'menu_group_id' => 1], // 15
            ['name' => 'Agents', 'menu_group_id' => 3], // 16
            ['name' => 'Unassigned Services', 'menu_group_id' => 5], // 17
            ['name' => 'Assigned Services', 'menu_group_id' => 5], // 18
            ['name' => 'Service Kits', 'menu_group_id' => 5], // 19
            ['name' => 'Parts Management', 'menu_group_id' => 6], // 20
            ['name' => 'FSR Quotation', 'menu_group_id' => 6], // 21
            ['name' => 'Tasks Continuation', 'menu_group_id' => 8], // 22
            ['name' => 'Interview', 'menu_group_id' => 2], // 23
            ['name' => 'Settlements', 'menu_group_id' => 2], // 24
            ['name' => 'Attendance', 'menu_group_id' => 2], // 25
            ['name' => 'Performance Review', 'menu_group_id' => 2], // 26
            ['name' => 'Brand Settings', 'menu_group_id' => 1], // 27
            ['name' => 'Location Reports', 'menu_group_id' => 9], // 28
            ['name' => 'Timeline', 'menu_group_id' => 9], // 29
            ['name' => 'Expense Requests', 'menu_group_id' => 7], // 30
            ['name' => 'Document Requests', 'menu_group_id' => 7], // 31
            ['name' => 'Loan Requests', 'menu_group_id' => 7], // 32
            ['name' => 'Job Vacancies', 'menu_group_id' => 2], // 33
            ['name' => 'General Report', 'menu_group_id' => 10], // 34
            ['name' => 'Task Reports', 'menu_group_id' => 10], // 35

        ];

        foreach ($menus as $menu) {
            Menu::updateOrCreate(
                ['name' => $menu['name'], 'menu_group_id' => $menu['menu_group_id']],
                $menu
            );
        }
    }
}
