<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Auto-expire pesanan yang sudah > 2 menit dan belum dibayar.
 * Jalankan cron: * * * * * php artisan schedule:run >> /dev/null 2>&1
 */
Schedule::call(function () {
    try {
        $baseUrl = config('app.url', 'http://localhost:8000');
        // Panggil endpoint auto-expire dengan internal service token
        Http::withHeaders([
            'X-Internal-Token' => config('app.key'),
            'Accept'           => 'application/json',
        ])->post("{$baseUrl}/api/orders/auto-expire");
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Auto-expire failed: ' . $e->getMessage());
    }
})->everyMinute()->name('auto-expire-orders')->withoutOverlapping();
