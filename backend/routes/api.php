<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AdminChatController;
use App\Http\Controllers\Api\UserChatController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — BookStore
|--------------------------------------------------------------------------
|
| Public  : tanpa autentikasi
| Auth    : wajib login (auth:sanctum)
| Admin   : wajib login + role admin
|
| Chat routes dipisah secara total:
|   - User chat  → /api/user/chats   (auth:sanctum saja)
|   - Admin chat → /api/admin/chats  (auth:sanctum + role:admin)
|
*/

// ═══════════════════════════════════════════════════════
// PUBLIC ROUTES
// ═══════════════════════════════════════════════════════

Route::prefix('auth')->group(function () {
    Route::post('/login',                       [AuthController::class, 'login']);
    Route::post('/register',                    [AuthController::class, 'register']);
    Route::post('/forgot-password',             [AuthController::class, 'forgotPassword']);
    Route::post('/forgot-password/check-email', [AuthController::class, 'checkEmail']);
    Route::post('/forgot-password/send-otp',    [AuthController::class, 'sendOtp']);
    Route::post('/forgot-password/verify-otp',  [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password',              [AuthController::class, 'resetPassword']);
});

// Buku & kategori public (landing page, toko)
Route::get('/books',                 [BookController::class, 'index']);
Route::get('/books/{book}',          [BookController::class, 'show']);
Route::get('/categories',            [CategoryController::class, 'index']);
Route::get('/categories/all',        [CategoryController::class, 'all']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/public-stats',          [DashboardController::class, 'publicStats']);

// ═══════════════════════════════════════════════════════
// AUTHENTICATED ROUTES (semua user yang sudah login)
// ═══════════════════════════════════════════════════════

Route::middleware(['auth:sanctum', \App\Http\Middleware\UpdateLastSeen::class])->group(function () {

    // ─── Auth ─────────────────────────────────────────
    Route::post('/auth/logout',  [AuthController::class, 'logout']);
    Route::get('/auth/profile',  [AuthController::class, 'profile']);
    Route::post('/auth/heartbeat', [AuthController::class, 'heartbeat']);

    // ─── Orders (User: pesanan milik sendiri) ─────────
    // PENTING: route statis (invoice/{invoiceNumber}) harus didaftarkan
    // SEBELUM route dinamis ({order}) agar tidak tertangkap sebagai ID.
    Route::get('/orders',                               [OrderController::class, 'index']);
    Route::post('/orders',                              [OrderController::class, 'store']);
    // PENTING: Semua route statis WAJIB didaftarkan SEBELUM route dinamis {order}
    Route::get('/orders/invoice/{invoiceNumber}',       [OrderController::class, 'getByInvoice']);
    // Auto-expire HARUS sebelum {order} agar 'auto-expire' tidak dianggap ID
    Route::post('/orders/auto-expire',                  [OrderController::class, 'autoExpire']);
    // Route dinamis {order} — harus paling bawah dari kelompok orders
    Route::get('/orders/{order}',                       [OrderController::class, 'show']);
    Route::get('/orders/{order}/invoice',               [OrderController::class, 'invoice']);
    Route::get('/orders/{order}/receipt',               [OrderController::class, 'receipt']);
    // User membatalkan pesanan miliknya (pending & unpaid only)
    Route::delete('/orders/{order}/cancel',             [OrderController::class, 'cancelByUser']);

    // ─── Chat USER (pelanggan ↔ admin) ────────────────
    // Hanya untuk role 'user'. unread-count didaftarkan SEBELUM wildcard.
    Route::prefix('user/chats')->group(function () {
        Route::get('/unread-count', [UserChatController::class, 'unreadCount']);
        Route::get('/',             [UserChatController::class, 'index']);
        Route::post('/',            [UserChatController::class, 'store']);
    });

    // ═══════════════════════════════════════════════════
    // ADMIN ONLY ROUTES
    // ═══════════════════════════════════════════════════

    Route::middleware('role:admin')->group(function () {

        // ─── Dashboard ────────────────────────────────
        Route::get('/dashboard',                [DashboardController::class, 'index']);

        // ─── Category CRUD ────────────────────────────
        Route::post('/categories',              [CategoryController::class, 'store']);
        Route::put('/categories/{category}',    [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        // ─── Book CRUD ────────────────────────────────
        Route::post('/books',          [BookController::class, 'store']);
        Route::post('/books/{book}',   [BookController::class, 'update']); // POST for file upload
        Route::delete('/books/{book}', [BookController::class, 'destroy']);

        // ─── Order Management (Kasir) ─────────────────
        Route::patch('/orders/{order}/status',  [OrderController::class, 'updateStatus']);
        Route::post('/orders/{order}/confirm',  [OrderController::class, 'confirm']);

        // ─── User Management ──────────────────────────
        Route::get('/users',                     [UserController::class, 'index']);
        Route::get('/users/{user}',              [UserController::class, 'show']);
        Route::put('/users/{user}',              [UserController::class, 'update']);
        Route::delete('/users/{user}',           [UserController::class, 'destroy']);

        // ─── Reports ──────────────────────────────────
        Route::get('/reports',              [ReportController::class, 'index']);
        Route::get('/reports/export/pdf',   [ReportController::class, 'exportPdf']);
        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel']);

        // ─── Chat ADMIN (admin ↔ semua pelanggan) ─────
        // unread-count didaftarkan SEBELUM wildcard {userId}.
        Route::prefix('admin/chats')->group(function () {
            Route::get('/unread-count', [AdminChatController::class, 'unreadCount']);
            Route::get('/',             [AdminChatController::class, 'index']);
            Route::post('/',            [AdminChatController::class, 'store']);
            Route::get('/{userId}',     [AdminChatController::class, 'show']);
        });

    });
});
