<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatementTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'statement_account_id', 'trans_date', 'cheque_no', 'reference',
        'narration', 'trans_details', 'debit', 'credit', 'sort_order',
    ];

    protected $casts = [
        'trans_date' => 'date',
        'debit'      => 'decimal:2',
        'credit'     => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(StatementAccount::class, 'statement_account_id');
    }
}
