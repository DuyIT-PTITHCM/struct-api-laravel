<?php

namespace Database\Factories;

use App\Models\Advertiser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Advertiser>
 */
class AdvertiserFactory extends Factory
{
    protected $model = Advertiser::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $email = $this->faker->unique()->email;
        return [
            'hash' => $this->faker->unique()->word,
            'email' => $email,
            'password' => bcrypt('12345678'),
            'name' => $this->faker->name,
            'profile' => $this->faker->sentence,
            'created_by' => 1,
            'balance' => $this->faker->randomFloat(2, 0, 1000),
            'parent_id' => 0,
            'report_email' =>  $email,
            'registration_no' => $this->faker->unique()->word,
            'phone' => $this->faker->phoneNumber,
            'address1' => $this->faker->address,
            'address2' => $this->faker->address,
            'postcode' => $this->faker->postcode,
            'connected_at' => $this->faker->dateTime(),
            'partner_id' => $this->faker->word,
            'representative' => $this->faker->name,
            'contact_name' => $this->faker->name,
            'contact_email' =>  $email,
            'contact_phone' => $this->faker->phoneNumber,
            'state' => $this->faker->numberBetween(1, 2),
        ];
    }
}
