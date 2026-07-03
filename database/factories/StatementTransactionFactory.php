<?php

namespace Database\Factories;

use App\Models\StatementTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StatementTransaction>
 */
class StatementTransactionFactory extends Factory
{
    protected $model = StatementTransaction::class;

    public function definition(): array
    {
        // A pool of realistic narration / detail pairs so generated statements
        // read like a real ledger without reusing the reference document's data.
        $catalog = [
            ['Fund Transfer - Debit',        'Ibanking Fund Transfer - Debit'],
            ['Eftn Transfer Debit',          'I Banking Eftn Transfer Debit'],
            ['Npsb Transfer Debit',          'I Banking Npsb Transfer Debit'],
            ['Rtgs Inward',                  'Rtgs Inward'],
            ['Beftn Inward Credit',          'Beftn Inward Credit'],
            ['Cheque Withdrawal',            'Cheque Withdrawal'],
            ['Npsb Charge',                  'Npsb Charge'],
            ['Value Added Tax',              'Value Added Tax'],
            ['Salary Disbursement',          'Corporate Salary Payment'],
            ['Utility Bill Payment',         'Auto Debit - Utility'],
        ];

        [$narration, $details] = $this->faker->randomElement($catalog);

        $isCredit = $this->faker->boolean(45);
        $amount   = $this->faker->numberBetween(500, 300000);

        $prefix = $this->faker->randomElement(['EB', 'BICB', 'RGI', 'TXN']);

        return [
            'trans_date'    => $this->faker->dateTimeBetween('-90 days', 'now'),
            'cheque_no'     => $this->faker->optional(0.1)->numerify('#######'),
            'reference'     => $prefix . $this->faker->numerify('##############'),
            'narration'     => 'Trn. Br: ' . $this->faker->numerify('###') . ' ' . $narration,
            'trans_details' => $details,
            'debit'         => $isCredit ? 0 : $amount,
            'credit'        => $isCredit ? $amount : 0,
            'sort_order'    => $this->faker->numberBetween(0, 1000),
        ];
    }
}
