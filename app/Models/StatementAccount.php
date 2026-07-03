<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatementAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'joint_name', 'fhp', 'address', 'city', 'phone',
        'customer_id', 'account_no', 'prev_account_no', 'account_type',
        'currency', 'status', 'opening_balance', 'uncleared_balance',
    ];

    protected $casts = [
        'opening_balance'   => 'decimal:2',
        'uncleared_balance' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(StatementTransaction::class);
    }
}
