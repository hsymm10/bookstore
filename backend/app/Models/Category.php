<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'slug',
    ];

    // ─── Boot ─────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        // Auto-generate slug dari nama
        static::creating(function (Category $category) {
            $category->slug = Str::slug($category->nama);
        });

        static::updating(function (Category $category) {
            $category->slug = Str::slug($category->nama);
        });
    }

    // ─── Relationships ────────────────────────────────────────────

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
