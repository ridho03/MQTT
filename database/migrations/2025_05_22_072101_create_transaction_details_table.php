<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();

            // transaction_id (HARUS sama dengan transactions.id)
            $table->unsignedBigInteger('transaction_id');

            // carbon_credit_id
            $table->unsignedBigInteger('carbon_credit_id');

            // vehicle_id (nullable)
            $table->unsignedBigInteger('vehicle_id')->nullable();

            $table->decimal('amount', 15, 2);
            $table->decimal('price', 15, 2);

            $table->timestamps();

            // FOREIGN KEY (ditaruh di bawah biar aman)
            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
                ->onDelete('cascade');

            $table->foreign('carbon_credit_id')
                ->references('id')
                ->on('carbon_credits')
                ->onDelete('cascade');

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('carbon_credits')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
