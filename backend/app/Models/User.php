<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role',
        'nama',
        'username',
        'email',
        'no_hp',
        'password',
        'is_online',
        'last_seen',
        'alamat',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'last_seen'         => 'datetime',
            'is_online'         => 'boolean',
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isOnline(): bool
    {
        if (!$this->is_online) {
            return false;
        }
        if (!$this->last_seen) {
            return false;
        }
        // Online if last active within 1 minute
        return $this->last_seen->diffInMinutes(now()) <= 1;
    }

    public function getLastSeenLabel(): string
    {
        if ($this->isOnline()) {
            return 'Online';
        }

        if (!$this->last_seen) {
            return 'Offline';
        }

        $dt = $this->last_seen->setTimezone('Asia/Jakarta');
        $timeStr = $dt->format('H:i');

        if ($dt->isToday()) {
            return "Terakhir aktif pukul {$timeStr}";
        } elseif ($dt->isYesterday()) {
            return "Terakhir aktif kemarin pukul {$timeStr}";
        } else {
            $months = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            return "Terakhir aktif {$dt->day} {$months[$dt->month]} {$dt->year} pukul {$timeStr}";
        }
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    // ─── Relationships ────────────────────────────────────────────

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }
}
