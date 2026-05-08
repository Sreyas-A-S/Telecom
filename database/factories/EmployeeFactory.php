<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'user_type' => 'employee',
        ]);

        return [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'password' => $user->password, // Use the same password as the user
            'department_id' => function () {
                return Department::inRandomOrder()->first()->id ?? Department::factory()->create()->id;
            },
            'is_broker' => $this->faker->boolean(20), // 20% chance of being a broker
            'employee_id' => $this->faker->unique()->randomNumber(5), // Generate a unique employee ID
            'marital_status' => $this->faker->randomElement(['Single', 'Married', 'Divorced', 'Widowed']),
            'emergency_contact' => $this->faker->phoneNumber,
            'father_name' => $this->faker->name('male'),
            'mother_name' => $this->faker->name('female'),
            'spouse_name' => $this->faker->name,
            'shirt_size' => $this->faker->randomElement(['S', 'M', 'L', 'XL', 'XXL']),
            'tshirt_size' => $this->faker->randomElement(['S', 'M', 'L', 'XL', 'XXL']),
            'blood_group' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'bank_name' => $this->faker->company . ' Bank',
            'account_number' => $this->faker->bankAccountNumber,
            'ifsc_code' => $this->faker->bothify('????0######'),
            'pf_no' => $this->faker->bothify('??/???/#######'),
            'esi_no' => $this->faker->bothify('##########'),
            'lwf_no' => $this->faker->bothify('##########'),
            'aadhar_no' => $this->faker->numerify('############'),
            'pan_no' => $this->faker->bothify('?????####?'),
            'branch' => $this->faker->city,
        ];
    }
}