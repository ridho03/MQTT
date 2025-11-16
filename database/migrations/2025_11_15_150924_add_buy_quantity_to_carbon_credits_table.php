<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('carbon_credits', function (Blueprint $table) {
        $table->decimal('quantity_to_buy', 10, 2)->nullable();
        $table->timestamp('buy_requested_at')->nullable();
        $table->timestamp('buy_approved_at')->nullable();
        $table->timestamp('buy_rejected_at')->nullable();
    });
}



    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('carbon_credits', function (Blueprint $table) {
        $table->dropColumn([
            'amount',
            'quantity_to_buy',
            'buy_requested_at',
            'buy_approved_at',
            'buy_rejected_at',
        ]);
    });
}

};
