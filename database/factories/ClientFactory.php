<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Agent;
use App\Models\Employee;
use App\Models\LeadSource;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    protected static $agents;
    protected static $employees;
    protected static $leadSources;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        if (!self::$agents) {
            self::$agents = Agent::pluck('id')->toArray();
        }
        if (!self::$employees) {
            self::$employees = Employee::where('is_broker', 1)->pluck('id')->toArray();
        }
        if (!self::$leadSources) {
            self::$leadSources = LeadSource::pluck('id')->toArray();
        }

        // Fetch all available agent IDs (from Agent and Employee models)
        $agentIds = self::$agents;
        $employeeBrokerIds = self::$employees;

        $allAgentOptions = [];
        foreach ($agentIds as $id) {
            $allAgentOptions[] = ['id' => $id, 'type' => 'App\\Models\\Agent'];
        }
        foreach ($employeeBrokerIds as $id) {
            $allAgentOptions[] = ['id' => $id, 'type' => 'App\\Models\\Employee'];
        }

        // Ensure there are agents/employees to pick from
        if (empty($allAgentOptions)) {
            // Fallback or throw an error if no agents/employees are available
            // For seeding, we might just return null or a default if allowed
            $selectedAgent = ['id' => null, 'type' => null];
        } else {
            $selectedAgent = $this->faker->randomElement($allAgentOptions);
        }


        // Fetch all available LeadSource IDs
        $leadSourceIds = self::$leadSources;

        // Ensure there are lead sources to pick from
        if (empty($leadSourceIds)) {
            $selectedLeadSourceId = null;
        } else {
            $selectedLeadSourceId = $this->faker->randomElement($leadSourceIds);
        }


        return [
            'salutation' => $this->faker->randomElement(['Mr.', 'Mrs.', 'Ms.']),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'gps_location' => $this->faker->latitude() . ',' . $this->faker->longitude(),
            'lead_id' => null, // Default to null for seeded clients
            'dealership_id' => null, // Default to null
            'employee_id' => null, // Default to null
            'agent_type' => $selectedAgent['type'], // Assign selected agent type
            'agent_id' => $selectedAgent['id'], // Assign selected agent ID
            'lead_source_id' => $selectedLeadSourceId, // Assign random lead source
            'lead_category_id' => null, // Default to null
            'notes' => $this->faker->paragraph(),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Client $client) {
            $lead = Lead::whereNull('client_id')->first();
            if ($lead) {
                $client->update([
                    'salutation' => $lead->salutation,
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'phone_number' => $lead->phone_number,
                    'address' => $lead->address,
                    'dealership_id' => $lead->dealership_id,
                    'employee_id' => $lead->employee_id,
                    'agent_type' => $lead->agent_type,
                    'agent_id' => $lead->agent_id,
                    'lead_source_id' => $lead->lead_source_id,
                    'lead_category_id' => $lead->lead_category_id,
                    'lead_id' => $lead->id,
                ]);

                $lead->update([
                    'client_id' => $client->id,
                    'last_status_before_conversion' => $lead->status,
                    'status' => 'converted_to_client',
                ]);
            }
        });
    }
}