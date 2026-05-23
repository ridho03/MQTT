<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->unsignedBigInteger('user_id');

            $table->string('payout_id');
            $table->string('midtrans_payout_id')->nullable();
            $table->text('midtrans_response')->nullable();

            $table->decimal('amount', 15, 2);
            $table->decimal('net_amount', 10, 2);

            $table->enum('status', [
                'pending',
                'created',
                'processing',
                'completed',
                'failed'
            ])->default('pending');

            // ✅ FIX (hapus after)
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // FOREIGN KEY
            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payouts');
    }
};
