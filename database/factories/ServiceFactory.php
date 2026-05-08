<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\ModelSeries;
use App\Models\Dealership;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $client = Client::inRandomOrder()->first();
        $product = Product::inRandomOrder()->first();
        $productModel = null;
        $modelSeries = null;
        $dealership = Dealership::inRandomOrder()->first();

        if ($product) {
            $productModel = ProductModel::where('product_id', $product->id)->inRandomOrder()->first();
            if ($productModel) {
                $modelSeries = \App\Models\ModelSeries::where('product_model_id', $productModel->id)->inRandomOrder()->first();
            }
        }

        return [
            'client_id' => $client ? $client->id : Client::factory(),
            'product_id' => $product ? $product->id : Product::factory(),
            'product_model_id' => $productModel ? $productModel->id : null,
            'model_series_id' => $modelSeries ? $modelSeries->id : null,
            'dealership_id' => $dealership ? $dealership->id : Dealership::factory(),
            'name' => $this->faker->sentence(3), // Complaint Title
            'description' => $this->faker->paragraph,
            'requested_location' => $this->faker->city,
            'referral_id' => $this->generateUniqueReferralId(),
            'machine_status' => $this->faker->randomElement(['warranty', 'extended_warranty', 'post_warranty']),
            'type_of_service' => $this->faker->randomElement(['warranty_claimable', 'warranty_free_service', 'warranty_mandatory', 'amc', 'paid_service', 'goodwill']),
            'contact_info' => $this->faker->phoneNumber,
            'contact_person' => $this->faker->name,
            'price' => $this->faker->randomFloat(2, 100, 1000),
            'due_date_1' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'due_date_2' => $this->faker->dateTimeBetween('+1 month', '+2 months')->format('Y-m-d'),
            'doc' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'failure_date' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'failure_hmr' => $this->faker->numberBetween(100, 5000),
        ];
    }

    /**
     * Generate a unique referral ID with the format SVHE(ddmmyy)NN.
     *
     * @return string
     */
    private function generateUniqueReferralId()
    {
        static $sequence = []; // Static array to hold sequence for each day

        // Get current date in ddmmyy format
        $date = \Carbon\Carbon::now()->format('dmy');
        $prefix = 'SVHE' . $date;

        // Initialize sequence for today if not already set
        if (!isset($sequence[$date])) {
            // Find the highest existing sequence number for today's date from the database
            $latestEntry = \App\Models\Service::where('referral_id', 'like', $prefix . '%')
                                ->orderBy('referral_id', 'desc')
                                ->first();

            if ($latestEntry) {
                $lastReferralId = $latestEntry->referral_id;
                $lastSequence = (int) substr($lastReferralId, -4); // Assuming NN is always 4 digits
                $sequence[$date] = $lastSequence + 1;
            } else {
                $sequence[$date] = 1;
            }
        } else {
            // Increment sequence for subsequent calls within the same day/run
            $sequence[$date]++;
        }

        // Format the sequence number with leading zeros
        $referralId = $prefix . str_pad($sequence[$date], 4, '0', STR_PAD_LEFT);

        // While loop to ensure uniqueness in case of race conditions or existing IDs
        // This is a fallback, the static counter should handle most cases within a single run
        while (\App\Models\Service::where('referral_id', $referralId)->exists()) {
            $sequence[$date]++;
            $referralId = $prefix . str_pad($sequence[$date], 4, '0', STR_PAD_LEFT);
        }

        return $referralId;
    }
}
