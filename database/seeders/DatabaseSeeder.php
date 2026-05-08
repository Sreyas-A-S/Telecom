<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create(); // Commented out as it's not used

        // $this->call(PermissionSeeder::class); // Uncommented        
        // // Call Dealership and Zone Seeders first as they are dependencies for many others
        // $this->call(AgentSeeder::class); // Ensure agents are seeded
        // $this->call(CategorySeeder::class);
        // $this->call(SubCategorySeeder::class);
        // $this->call(ProductSeeder::class);
        // $this->call(ProductModelSeeder::class);
        // $this->call(ModelSeriesSeeder::class);
        // $this->call(ClientSeeder::class); // Call ClientSeeder before LeadSeeder
        // $this->call(InterviewSeeder::class); // Call InterviewSeeder
        // $this->call(SettlementSeeder::class); // Call SettlementSeeder
        // $this->call(LeadSeeder::class);
        // $this->call(ServiceSeeder::class);
        // $this->call(TaskSeeder::class);
        // $this->call(FSRReportSeeder::class);
        // $this->call(PackageKitSeeder::class); // PackageKitSeeder before PartSeeder
        // $this->call(PartSeeder::class); // PartSeeder depends on Dealerships and PackageKits
        // $this->call(FSRQuotationSeeder::class); // FSRQuotationSeeder depends on FSR Reports, Parts, and Users
        // $this->call(LeadFollowupSeeder::class); // Call the new seeder
        // $this->call(LeaveRequestSeeder::class); // Call the new LeaveRequestSeeder
        // $this->call(ExpenseRequestSeeder::class); // Call the new ExpenseRequestSeeder
        // $this->call(DocumentTypeSeeder::class); // Call the new DocumentTypeSeeder
        // $this->call(DocumentRequestSeeder::class); // Call the new DocumentRequestSeeder
        // $this->call(LoanRequestSeeder::class); // Call the new LoanRequestSeeder
        // $this->call(LossOrderSeeder::class);
        // $this->call(IndianStatesAndDistrictsSeeder::class); // Add this line
        // $this->call(SettingSeeder::class);
        // $this->call(UserGpsTraceSeeder::class);
        // $this->call(UserEmployeeSeeder::class);
        // $this->call(PerformanceReviewSeeder::class);
        // $this->call(ZoneSeeder::class);


        $this->call(RoleSeeder::class);
        $this->call(UserRoleSeeder::class);
        $this->call(UserSeeder::class); // UserSeeder depends on Dealerships and Departments
        $this->call(EmployeeSeeder::class); // Call Employee Seeder last
        $this->call(MenuGroupSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(MenuPermissionSeeder::class); // Uncommented
        $this->call(DealershipSeeder::class);
        $this->call(DepartmentSeeder::class); // Moved to be called before UserSeeder
        $this->call(NotificationSeeder::class); // NotificationSeeder depends on Users
        $this->call(LeadSourceSeeder::class); // Ensure lead sources are seeded
        $this->call(LeadCategorySeeder::class);
        $this->call(TaxSeeder::class);
    
}
}