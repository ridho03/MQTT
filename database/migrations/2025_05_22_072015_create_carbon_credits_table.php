<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carbon_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users');
            $table->string('nomor_kartu_keluarga');
            $table->enum('pemilik_kendaraan', ['milik sendiri', 'milik keluarga satu kk']);
            $table->string('nik_e_ktp');
            $table->string('nrkb');
            $table->string('nomor_rangka_5digit', 5);
            $table->string('vehicle_type')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->decimal('price_per_unit', 15, 2);

            $table->enum('status', [
                'pending',
                'approved',
                'pending_sale',
                'available',
                'rejected',
                'sold'
            ])->default('pending');

            // Kolom penjualan
            $table->decimal('sale_price_per_unit', 15, 2)->nullable();
            $table->decimal('quantity_to_sell', 10, 2)->nullable();
            $table->text('sale_notes')->nullable();
            $table->date('preferred_sale_date')->nullable();
            $table->timestamp('sale_requested_at')->nullable();
            $table->timestamp('sale_approved_at')->nullable();
            $table->timestamp('sale_rejected_at')->nullable();
            $table->text('sale_rejection_reason')->nullable();
            $table->unsignedBigInteger('sale_rejected_by')->nullable();

            $table->foreign('sale_rejected_by')->references('id')->on('users');

            // MQTT device
            $table->string('device_id')->nullable()->index();

            // Emisi
            $table->float('current_co2e_g_km')->nullable();
            $table->float('total_emissions_kg')->default(0);
            $table->float('daily_emissions_kg')->default(0);
            $table->float('monthly_emissions_kg')->default(0);

            // Lokasi
            $table->double('last_latitude', 10, 8)->nullable();
            $table->double('last_longitude', 11, 8)->nullable();
            $table->float('last_speed_kmph')->nullable();

            // Sensor
            $table->timestamp('last_sensor_update')->nullable();
            $table->enum('sensor_status', ['active', 'inactive', 'error'])->default('inactive');

            // Auto adjustment
            $table->boolean('auto_adjustment_enabled')->default(true);
            $table->float('emission_threshold_kg')->default(100);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carbon_credits');
    }
};
