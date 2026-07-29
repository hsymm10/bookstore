<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'nama_buku',
        'slug',
        'tanggal_terbit',
        'tahun_terbit',
        'stok',
        'harga_modal',
        'harga_jual',
        'keuntungan',
        'deskripsi',
        'gambar',
    ];

    /**
     * Attribute casting.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_terbit' => 'date',
        'stok'           => 'integer',
        'harga_modal'    => 'decimal:2',
        'harga_jual'     => 'decimal:2',
        'keuntungan'     => 'decimal:2',
    ];

    // ─── Boot ─────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        // Auto-generate slug dari nama_buku
        static::creating(function (Book $book) {
            $book->slug       = Str::slug($book->nama_buku);
            $book->keuntungan = $book->harga_jual - $book->harga_modal;
        });

        static::updating(function (Book $book) {
            $book->slug       = Str::slug($book->nama_buku);
            $book->keuntungan = $book->harga_jual - $book->harga_modal;
        });
    }

    // ─── Accessors ────────────────────────────────────────────────

    public function getGambarUrlAttribute(): ?string
    {
        if (! $this->gambar) {
            return null;
        }

        return asset('storage/' . $this->gambar);
    }

    // ─── Relationships ────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
