<?php

namespace App\Http\Controllers;

use App\Models\StatementAccount;
use App\Services\StatementBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountStatementController extends Controller
{
    /**
     * Stream the account statement PDF (same layout as the reference document,
     * fully dynamic data). Add ?download=1 to force a file download.
     */
    public function download(Request $request, StatementAccount $account): Response
    {
        $data = (new StatementBuilder())->build($account);

        $data['institution'] = config('statement.institution');
        $data['branch']      = config('statement.branch');
        $data['title']       = config('statement.title');
        $data['notes']       = config('statement.notes', []);

        $pdf = Pdf::loadView('statements.account-statement', $data)
            ->setPaper('a4', 'portrait');

        // Render once, then stamp "Page X of Y" on every page. Doing this after
        // render() means the total page count is known and correct, and the
        // barryvdh wrapper won't re-render (which would drop the stamps).
        $pdf->render();
        $this->stampPageNumbers($pdf);

        $fileName = 'Statement_' . $account->account_no . '.pdf';

        return $request->boolean('download')
            ? $pdf->download($fileName)
            : $pdf->stream($fileName);
    }

    /**
     * Draw a bottom-right "Page X of Y" footer on each page of a rendered PDF.
     */
    private function stampPageNumbers(\Barryvdh\DomPDF\PDF $pdf): void
    {
        $dompdf      = $pdf->getDomPDF();
        $canvas      = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();

        $text = 'Page {PAGE_NUM} of {PAGE_COUNT}';
        $size = 8.5;
        $font = $fontMetrics->getFont('Helvetica');
        $width = $fontMetrics->getTextWidth($text, $font, $size);

        $x = $canvas->get_width() - $width - 26;
        $y = $canvas->get_height() - 32;

        // page_text applies to every page; the placeholders are substituted
        // with the real page number / total at output time.
        $canvas->page_text($x, $y, $text, $font, $size, [0, 0, 0]);
    }
}
