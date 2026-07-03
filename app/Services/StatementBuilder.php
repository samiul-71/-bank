<?php

namespace App\Services;

use App\Models\StatementAccount;
use Illuminate\Support\Collection;

/**
 * Turns a StatementAccount + its transactions into a print-ready view model:
 * running balances are computed here so the Blade template only formats data.
 * The table flows naturally across pages (dompdf repeats the <thead>), so no
 * manual page chunking is required and page numbers can never desync.
 */
class StatementBuilder
{
    /**
     * @return array{
     *   account: StatementAccount,
     *   opening_balance: float,
     *   period_from: ?string,
     *   period_to: ?string,
     *   rows: Collection<int, array<string,mixed>>,
     *   total_debit: float,
     *   total_credit: float
     * }
     */
    public function build(StatementAccount $account): array
    {
        $account->loadMissing('transactions');

        $transactions = $account->transactions
            ->sortBy([
                ['trans_date', 'asc'],
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        $balance = (float) $account->opening_balance;
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        // First printed row is the opening balance (balance column only),
        // mirroring the reference statement.
        $rows = collect([[
            'opening' => true,
            'balance' => (float) $account->opening_balance,
        ]]);

        foreach ($transactions as $txn) {
            $totalDebit  += (float) $txn->debit;
            $totalCredit += (float) $txn->credit;
            $balance += (float) $txn->credit - (float) $txn->debit;

            $rows->push([
                'opening'       => false,
                'trans_date'    => $txn->trans_date?->format('d-m-Y'),
                'cheque_no'     => $txn->cheque_no,
                'reference'     => $txn->reference,
                'narration'     => $txn->narration,
                'trans_details' => $txn->trans_details,
                'debit'         => (float) $txn->debit,
                'credit'        => (float) $txn->credit,
                'balance'       => $balance,
            ]);
        }

        return [
            'account'         => $account,
            'opening_balance' => (float) $account->opening_balance,
            'period_from'     => $transactions->first()?->trans_date?->format('d-M-Y'),
            'period_to'       => $transactions->last()?->trans_date?->format('d-M-Y'),
            'rows'            => $rows,
            'total_debit'     => $totalDebit,
            'total_credit'    => $totalCredit,
        ];
    }
}
