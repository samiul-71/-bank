<?php

namespace App\Http\Controllers;

use App\Models\StatementAccount;
use App\Services\TransactionImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionImportController extends Controller
{
    /** Show the Excel upload form for an account. */
    public function create(StatementAccount $account): View
    {
        return view('statements.import', compact('account'));
    }

    /**
     * Download a ready-to-fill sample .xlsx with the exact expected headers
     * and a couple of example rows (including a leading opening-balance row).
     */
    public function sample(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Transactions');

        // No Balance column: the running balance is calculated automatically,
        // and the opening balance is set on the account itself.
        $headers = [
            'Trans. Date', 'Cheque#.', 'Ref.', 'Narration',
            'Trans. Details', 'Debit', 'Credit',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $examples = [
            ['01-04-2026', '',        'RGI8260401791882', 'Trn. Br: 146 Rtgs Inward',       'Rtgs Inward',                    '0.00',      '202,777.11'],
            ['01-04-2026', '',        'EB26040196964896', 'Trn. Br: 146 Salary for Mar26',  'Ibanking Fund Transfer - Debit', '12,000.00', '0.00'],
            ['02-04-2026', '1325318', '0562609117543',    'Trn. Br: 056 Cheque Withdrawal', 'Cheque Withdrawal',              '30,000.00', '0.00'],
        ];
        $sheet->fromArray($examples, null, 'A2');

        // Header styling: bold + grey fill + centered, matching the statement.
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9D9D9');
        $sheet->getStyle('A1:G1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'statement_import_template.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** Handle the uploaded Excel/CSV file and import its rows. */
    public function store(Request $request, StatementAccount $account, TransactionImporter $importer): RedirectResponse
    {
        $request->validate([
            'file'    => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:20480'],
            'replace' => ['nullable', 'boolean'],
        ]);

        $summary = $importer->importInto(
            $account,
            $request->file('file')->getRealPath(),
            $request->boolean('replace'),
        );

        $message = "Imported {$summary['imported']} transaction(s)"
            . ($summary['skipped'] ? ", skipped {$summary['skipped']} empty row(s)" : '')
            . ($summary['opening_balance'] !== null
                ? '. Opening balance set to ' . number_format($summary['opening_balance'], 2)
                : '') . '.';

        return redirect()
            ->route('dashboard')
            ->with('status', $message);
    }
}
