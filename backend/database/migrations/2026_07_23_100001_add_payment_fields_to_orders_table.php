<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Invoice number unik per transaksi, format: INV-YYYYMMDD-XXXXX
            $table->string('invoice_number', 30)->unique()->nullable()->after('kode_pesanan');

            // Metode pembayaran
            $table->enum('payment_method', ['cash', 'transfer'])->default('cash')->after('total');

            // Status pembayaran (terpisah dari status pesanan)
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid')->after('payment_method');

            // Nominal yang dibayarkan kasir/customer
            $table->decimal('amount_paid', 12, 2)->nullable()->after('payment_status');

            // Kembalian
            $table->decimal('change_amount', 12, 2)->nullable()->after('amount_paid');

            // Waktu pembayaran berhasil
            $table->timestamp('paid_at')->nullable()->after('change_amount');

            // URL QR code image (external service)
            $table->string('qr_code_url')->nullable()->after('paid_at');

            // URL barcode image (external service)
            $table->string('barcode_url')->nullable()->after('qr_code_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_number',
                'payment_method',
                'payment_status',
                'amount_paid',
                'change_amount',
                'paid_at',
                'qr_code_url',
                'barcode_url',
            ]);
        });
    }
};
