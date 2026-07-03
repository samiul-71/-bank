<?php

namespace App\Services;

use App\Models\StatementAccount;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Imports transaction rows from an Excel/CSV file into a StatementAccount.
 *
 * Expected columns (header row, order-independent, matched fuzzily):
 *   Trans. Date | Cheque#. | Ref. | Narration | Trans. Details | Debit | Credit | Balance
 *
 * Balance is IGNORED — the running balance is computed by StatementBuilder.
 * A leading "balance only" row (no date/debit/credit) is treated as the
 * account's opening balance.
 */
class TransactionImporter
{
    /**
     * Map of canonical field => list of accepted header aliases (normalized:
     * lowercased, non-alphanumerics stripped).
     */
    private const HEADER_ALIASES = [
        'trans_date'    => ['transdate', 'date', 'trndate', 'transactiondate'],
        'cheque_no'     => ['cheque', 'chequeno', 'chequenumber', 'chq'],
        'reference'     => ['ref', 'reference', 'refno'],
        'narration'     => ['narration', 'particulars'],
        'trans_details' => ['transdetails', 'details', 'transactiondetails', 'remarks'],
        'debit'         => ['debit', 'out', 'withdrawal', 'dr'],
        'credit'        => ['credit', 'in', 'deposit', 'cr'],
        'balance'       => ['balance', 'total', 'runningbalance'],
    ];

    /** @var array<string,int> resolved column index (0-based) per field */
    private array $columns = [];

    /**
     * Parse a file and persist the transactions into the account.
     *
     * @return array{imported:int, skipped:int, opening_balance:?float}
     */
    public function importInto(StatementAccount $account, string $path, bool $replace = false): array
    {
        $result = $this->parse($path);

        if ($replace) {
            $account->transactions()->delete();
        }

        $sortBase = $replace ? 0 : (int) $account->transactions()->max('sort_order');

        foreach ($result['rows'] as $i => $row) {
            $account->transactions()->create([
                'trans_date'    => $row['trans_date'],
                'cheque_no'     => $row['cheque_no'],
                'reference'     => $row['reference'],
                'narration'     => $row['narration'],
                'trans_details' => $row['trans_details'],
                'debit'         => $row['debit'],
                'credit'        => $row['credit'],
                'sort_order'    => $sortBase + $i + 1,
            ]);
        }

        if ($result['opening_balance'] !== null) {
            $account->update(['opening_balance' => $result['opening_balance']]);
        }

        return [
            'imported'        => count($result['rows']),
            'skipped'         => $result['skipped'],
            'opening_balance' => $result['opening_balance'],
        ];
    }

    /**
     * Read + normalize the file without persisting (useful for previews/tests).
     *
     * @return array{rows:array<int,array<string,mixed>>, skipped:int, opening_balance:?float}
     */
    public function parse(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($path)->getActiveSheet();

        $matrix = $sheet->toArray(null, true, false, false);
        if (empty($matrix)) {
            return ['rows' => [], 'skipped' => 0, 'opening_balance' => null];
        }

        $header = array_shift($matrix);
        $this->resolveColumns($header);

        $rows = [];
        $skipped = 0;
        $openingBalance = null;

        foreach ($matrix as $raw) {
            $date    = $this->parseDate($this->cell($raw, 'trans_date'));
            $debit   = $this->parseAmount($this->cell($raw, 'debit'));
            $credit  = $this->parseAmount($this->cell($raw, 'credit'));
            $balance = $this->parseAmount($this->cell($raw, 'balance'));

            $cheque    = $this->str($this->cell($raw, 'cheque_no'));
            $reference = $this->str($this->cell($raw, 'reference'));
            $narration = $this->str($this->cell($raw, 'narration'));
            $details   = $this->str($this->cell($raw, 'trans_details'));

            $hasMovement = $debit != 0.0 || $credit != 0.0;
            $hasText     = $cheque || $reference || $narration || $details;

            // Empty row: capture a leading balance-only row as the opening
            // balance, otherwise skip. A usable transaction must have a date,
            // some movement, or some text.
            if (! $date && ! $hasMovement && ! $hasText) {
                if ($openingBalance === null && $balance !== null && empty($rows)) {
                    $openingBalance = $balance;
                } else {
                    $skipped++;
                }
                continue;
            }

            $rows[] = [
                'trans_date'    => $date,
                'cheque_no'     => $cheque,
                'reference'     => $reference,
                'narration'     => $narration,
                'trans_details' => $details,
                'debit'         => $debit ?? 0.0,
                'credit'        => $credit ?? 0.0,
            ];
        }

        return ['rows' => $rows, 'skipped' => $skipped, 'opening_balance' => $openingBalance];
    }

    /** Resolve each canonical field to a 0-based column index using the header row. */
    private function resolveColumns(array $header): void
    {
        $normalized = [];
        foreach ($header as $idx => $label) {
            $normalized[$idx] = $this->normalize((string) $label);
        }

        $this->columns = [];
        foreach (self::HEADER_ALIASES as $field => $aliases) {
            foreach ($normalized as $idx => $norm) {
                if ($norm !== '' && in_array($norm, $aliases, true)) {
                    $this->columns[$field] = $idx;
                    break;
                }
            }
        }
    }

    private function cell(array $row, string $field): mixed
    {
        $idx = $this->columns[$field] ?? null;

        return $idx !== null ? ($row[$idx] ?? null) : null;
    }

    private function normalize(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim($value))) ?? '';
    }

    private function str(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    /** Parse "01-04-2026", "01/04/2026" (day-first), or an Excel serial date. */
    private function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Excel serial number (data-only reads leave dates as floats/ints).
        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
            } catch (\Throwable) {
                // fall through to string parsing
            }
        }

        $value = trim((string) $value);

        foreach (['d-m-Y', 'd/m/Y', 'd.m.Y', 'j-n-Y', 'j/n/Y', 'Y-m-d', 'd-M-Y'] as $format) {
            $dt = Carbon::createFromFormat($format, $value);
            if ($dt !== false && $dt->format($format) === $value) {
                return $dt->startOfDay();
            }
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /** Parse an amount, stripping thousands separators; blanks/dashes => null. */
    private function parseAmount(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $clean = trim((string) $value);
        if ($clean === '' || $clean === '-') {
            return null;
        }

        $clean = str_replace([',', ' ', "\u{00A0}"], '', $clean);

        return is_numeric($clean) ? (float) $clean : null;
    }
}
