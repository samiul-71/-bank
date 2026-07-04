@php
    /** @var \App\Models\StatementAccount $account */
    /** @var \Illuminate\Support\Collection $rows */
    $fmt = fn ($v) => number_format((float) $v, 2, '.', ',');
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* Page + margins measured from the reference: 700x842pt, table 20->680. */
        @page { margin: 22pt 20pt 30pt 20pt; }

        * { box-sizing: border-box; }

        body {
            /* Reference statement: Helvetica 10pt throughout (dompdf core font). */
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            margin: 0;
        }

        /* ---- Top branding header (prints once, above the table) ---- */
        .brand { text-align: center; }
        .brand h1 { font-size: 14pt; font-weight: bold; margin: 0 0 7pt; }
        .brand h2 { font-size: 14pt; font-weight: bold; margin: 0 0 7pt; }
        .brand h3 { font-size: 14pt; font-weight: bold; margin: 0 0 11pt; }

        /* ---- Account info block ---- */
        .info { width: 100%; border-collapse: collapse; margin-bottom: 8pt; }
        .info td { vertical-align: top; padding: 1.5pt 4pt; }
        .info .label  { width: 82pt; }
        .info .value  { width: 205pt; }
        .info .gap    { width: 28pt; }
        .info .rlabel { width: 165pt; }

        /* ---- Transaction table (thead auto-repeats on every page) ---- */
        table.txn {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.txn thead { display: table-header-group; }
        table.txn th, table.txn td {
            border: 0.7px solid #000;
            padding: 3px 4px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.txn thead th {
            background: #d9d9d9;
            /* Reference column headers are regular weight (only titles are bold). */
            font-weight: normal;
            text-align: center;
        }
        /* Column widths measured from the reference grid (% of the 660pt table). */
        .c-date    { width: 15.2%; text-align: center; }
        .c-cheque  { width: 12.1%; text-align: center; }
        .c-ref     { width: 11.5%; text-align: center; }
        .c-narr    { width: 12.1%; text-align: center; }
        .c-det     { width: 14.2%; }
        .c-debit   { width: 11.5%; text-align: right; }
        .c-credit  { width: 9.4%;  text-align: right; }
        .c-balance { width: 13.9%; text-align: right; }

        td.num { text-align: right; }
        td.ctr { text-align: center; }

        /* ---- Totals row: a detached box below the table (gap above),
                only the label + Debit + Credit cells are boxed, the
                surrounding cells are left blank (no border) ---- */
        table.totals { margin-top: 16pt; }
        table.txn td.blank { border: 0; padding: 0; }
        /* Reference: "Total :" label is 12pt regular; the numbers are 10pt regular. */
        table.txn td.total-label {
            text-align: right;
            font-size: 12pt;
            font-weight: normal;
        }
        table.txn td.total-val { font-weight: normal; }

        /* ---- Footer notes: body 10pt, "Notes:" heading 10pt bold ---- */
        .notes { margin-top: 14pt; font-size: 10pt; }
        .notes .title { font-weight: bold; margin-bottom: 3pt; }
        .notes ol { margin: 0; padding-left: 18pt; }
        .notes li { margin: 1pt 0; }
    </style>
</head>
<body>

    {{-- "Page X of Y" is stamped on every page by the controller after render. --}}

    {{-- Branding: driven by config/statement.php, not hardcoded --}}
    <div class="brand">
        <h1>{{ $institution }}</h1>
        <h2>{{ $branch }}</h2>
        <h3>{{ $title }}</h3>
    </div>

    <table class="info">
        <tr>
            <td class="label">Name :</td>
            <td class="value">{{ $account->name }}</td>
            <td class="gap"></td>
            <td class="rlabel">Customer ID :</td>
            <td>{{ $account->customer_id }}</td>
        </tr>
        <tr>
            <td class="label">Joint Name :</td>
            <td class="value">{{ $account->joint_name }}</td>
            <td class="gap"></td>
            <td class="rlabel">A/C No. :</td>
            <td>{{ $account->account_no }}</td>
        </tr>
        <tr>
            <td class="label">F/H/P :</td>
            <td class="value">{{ $account->fhp }}</td>
            <td class="gap"></td>
            <td class="rlabel">Prev. A/C No. :</td>
            <td>{{ $account->prev_account_no }}</td>
        </tr>
        <tr>
            <td class="label">Address :</td>
            <td class="value">{{ $account->address }}</td>
            <td class="gap"></td>
            <td class="rlabel">A/C Type :</td>
            <td>{{ $account->account_type }}</td>
        </tr>
        <tr>
            <td class="label"></td>
            <td class="value"></td>
            <td class="gap"></td>
            <td class="rlabel">Currency :</td>
            <td>{{ $account->currency }}</td>
        </tr>
        <tr>
            <td class="label">City :</td>
            <td class="value">{{ $account->city }}</td>
            <td class="gap"></td>
            <td class="rlabel">A/C Status :</td>
            <td>{{ $account->status }}</td>
        </tr>
        <tr>
            <td class="label">Phone :</td>
            <td class="value">{{ $account->phone }}</td>
            <td class="gap"></td>
            <td class="rlabel">Period :</td>
            <td>{{ $period_from }} to {{ $period_to }}</td>
        </tr>
        <tr>
            <td class="label"></td>
            <td class="value"></td>
            <td class="gap"></td>
            <td class="rlabel">Uncleared/ Floating Balance :</td>
            <td>{{ $fmt($account->uncleared_balance) }}</td>
        </tr>
    </table>

    <table class="txn">
        <thead>
            <tr>
                <th class="c-date">Trans. Date</th>
                <th class="c-cheque">Cheque#.</th>
                <th class="c-ref">Ref.</th>
                <th class="c-narr">Narration</th>
                <th class="c-det">Trans. Details</th>
                <th class="c-debit">Debit</th>
                <th class="c-credit">Credit</th>
                <th class="c-balance">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                @if ($row['opening'])
                    {{-- Opening balance row: balance column only --}}
                    <tr>
                        <td></td><td></td><td></td><td></td><td></td>
                        <td></td><td></td>
                        <td class="num">{{ $fmt($row['balance']) }}</td>
                    </tr>
                @else
                    <tr>
                        <td class="ctr">{{ $row['trans_date'] }}</td>
                        <td class="ctr">{{ $row['cheque_no'] }}</td>
                        <td class="ctr">{{ $row['reference'] }}</td>
                        <td class="ctr">{{ $row['narration'] }}</td>
                        <td>{{ $row['trans_details'] }}</td>
                        <td class="num">{{ $fmt($row['debit']) }}</td>
                        <td class="num">{{ $fmt($row['credit']) }}</td>
                        <td class="num">{{ $fmt($row['balance']) }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    {{-- Totals: a detached, boxed row (Debit & Credit sums) sitting a little
         below the table, matching the reference layout. Same fixed column
         widths keep it aligned under the Debit / Credit columns. --}}
    <table class="txn totals">
        <tr>
            <td class="c-date blank"></td>
            <td class="c-cheque blank"></td>
            <td class="c-ref blank"></td>
            <td class="c-narr blank"></td>
            <td class="c-det total-label">Total :</td>
            <td class="c-debit num total-val">{{ $fmt($total_debit) }}</td>
            <td class="c-credit num total-val">{{ $fmt($total_credit) }}</td>
            <td class="c-balance blank"></td>
        </tr>
    </table>

    @if (! empty($notes))
        <div class="notes">
            <div class="title">Notes:</div>
            <ol>
                @foreach ($notes as $note)
                    <li>{{ $note }}</li>
                @endforeach
            </ol>
        </div>
    @endif

</body>
</html>
