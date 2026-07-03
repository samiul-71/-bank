<?php

namespace Database\Seeders;

use App\Models\StatementAccount;
use App\Models\StatementTransaction;
use Illuminate\Database\Seeder;

class StatementSeeder extends Seeder
{
    public function run(): void
    {
        // One demo account with ~120 transactions so the PDF spans many pages.
        StatementAccount::factory()
            ->has(
                StatementTransaction::factory()->count(120),
                'transactions'
            )
            ->create([
                'name' => 'CHHAYA TECHNOLOGIES LIMITED',
            ]);
    }
}
