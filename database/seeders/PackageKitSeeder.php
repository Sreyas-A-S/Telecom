<?php

namespace Database\Seeders;

use App\Models\PackageKit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageKitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PackageKit::firstOrCreate(
            ['name' => 'Basic Service Package'],
            [
                'description' => 'Essential services for basic maintenance.',
                'price' => 99.99,
                'features' => ['Oil Change', 'Tire Rotation', 'Multi-point Inspection'],
                'is_active' => true,
            ]
        );

        PackageKit::firstOrCreate(
            ['name' => 'Standard Service Package'],
            [
                'description' => 'Comprehensive services for regular upkeep.',
                'price' => 199.99,
                'features' => ['Oil Change', 'Tire Rotation', 'Multi-point Inspection', 'Brake Check', 'Fluid Top-off'],
                'is_active' => true,
            ]
        );

        PackageKit::firstOrCreate(
            ['name' => 'Premium Service Package'],
            [
                'description' => 'All-inclusive services for optimal performance.',
                'price' => 349.99,
                'features' => ['Oil Change', 'Tire Rotation', 'Multi-point Inspection', 'Brake Check', 'Fluid Top-off', 'Engine Tune-up', 'AC Performance Check'],
                'is_active' => true,
            ]
        );
    }
}
