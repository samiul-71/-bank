<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Account holders whose statement can be generated.
        Schema::create('statement_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // Account title
            $table->string('joint_name')->nullable();       // Joint holder
            $table->string('fhp')->nullable();              // Father/Husband/Parent
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();

            $table->string('customer_id')->nullable();
            $table->string('account_no')->unique();
            $table->string('prev_account_no')->nullable();
            $table->string('account_type')->default('Current'); // Current / Savings ...
            $table->string('currency', 8)->default('BDT');
            $table->string('status')->default('Active');

            // Opening balance the running balance is calculated from.
            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->decimal('uncleared_balance', 18, 2)->default(0);

            $table->timestamps();
        });

        // Individual ledger movements. Running balance is derived, not stored,
        // so the statement is always internally consistent.
        Schema::create('statement_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('statement_account_id')
                ->constrained('statement_accounts')
                ->cascadeOnDelete();

            $table->date('trans_date');
            $table->string('cheque_no')->nullable();
            $table->string('reference')->nullable();     // Ref.
            $table->text('narration')->nullable();       // Long narration block
            $table->string('trans_details')->nullable(); // Short trans details

            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);

            // Ordering key so same-date rows keep a stable sequence.
            $table->unsignedBigInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(
                ['statement_account_id', 'trans_date', 'sort_order'],
                'stmt_txn_acct_date_sort_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statement_transactions');
        Schema::dropIfExists('statement_accounts');
    }
};
