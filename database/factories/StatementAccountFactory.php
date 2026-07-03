<?php

namespace Database\Factories;

use App\Models\StatementAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StatementAccount>
 */
class StatementAccountFactory extends Factory
{
    protected $model = StatementAccount::class;

    public function definition(): array
    {
        return [
            'name'              => strtoupper($this->faker->company()) . ' LIMITED',
            'joint_name'        => null,
            'fhp'               => null,
            'address'           => $this->faker->buildingNumber() . ', '
                                    . strtoupper($this->faker->streetName()) . ', '
                                    . strtoupper($this->faker->city()) . '-'
                                    . $this->faker->numberBetween(1000, 9999),
            'city'              => $this->faker->city(),
            'phone'             => 'M:0' . $this->faker->numerify('1#########'),
            'customer_id'       => (string) $this->faker->numberBetween(100000000, 999999999),
            'account_no'        => (string) $this->faker->numerify('##############'),
            'prev_account_no'   => fn (array $a) => $a['account_no'],
            'account_type'      => $this->faker->randomElement(['Current', 'Savings']),
            'currency'          => 'BDT',
            'status'            => 'Active',
            'opening_balance'   => $this->faker->numberBetween(500000, 2000000),
            'uncleared_balance' => 0,
        ];
    }
}
