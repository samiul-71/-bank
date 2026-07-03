<?php

namespace App\Console\Commands;

use App\Models\StatementAccount;
use App\Services\TransactionImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Usage:
 *   php artisan statement:import {account_no} {path/to/file.xlsx}
 *   php artisan statement:import 80404609801229 storage/app/txns.xlsx --replace
 */
#[Signature('statement:import {account : Account number (account_no)} {file : Path to the .xlsx/.xls/.csv file} {--replace : Delete existing transactions first}')]
#[Description('Import statement transactions from a local Excel/CSV file into an account')]
class ImportStatementTransactions extends Command
{
    public function handle(TransactionImporter $importer): int
    {
        $accountNo = (string) $this->argument('account');
        $file      = (string) $this->argument('file');

        $account = StatementAccount::where('account_no', $accountNo)->first();
        if (! $account) {
            $this->error("No account found with account_no = {$accountNo}");

            return self::FAILURE;
        }

        if (! is_file($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $summary = $importer->importInto($account, $file, (bool) $this->option('replace'));

        $this->info("Imported {$summary['imported']} transaction(s), skipped {$summary['skipped']}.");
        if ($summary['opening_balance'] !== null) {
            $this->info('Opening balance set to ' . number_format($summary['opening_balance'], 2));
        }

        return self::SUCCESS;
    }
}
