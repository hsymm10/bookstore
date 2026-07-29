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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->string('nama_buku');
            $table->string('slug')->unique();
            $table->date('tanggal_terbit')->nullable();
            $table->year('tahun_terbit')->nullable();
            $table->unsignedInteger('stok')->default(0);
            $table->decimal('harga_modal', 12, 2)->default(0);
            $table->decimal('harga_jual', 12, 2)->default(0);
            $table->decimal('keuntungan', 12, 2)->default(0);
            $table->text('deskripsi')->nullable();
            $table->string('gambar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
