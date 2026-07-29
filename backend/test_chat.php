<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('role', 'user')->first();
$admin = \App\Models\User::where('role', 'admin')->first();

if (!$user) { echo "No user found\n"; exit; }
if (!$admin) { echo "No admin found\n"; exit; }

$chat = \App\Models\Chat::create([
    'sender_id' => $user->id,
    'receiver_id' => $admin->id,
    'message' => 'Hello Admin, testing 123'
]);
echo "Chat created: " . $chat->id . "\n";

$adminChats = \App\Models\Chat::select('sender_id', 'receiver_id')
    ->where(fn ($q) => $q->where('receiver_id', $admin->id)->orWhere('sender_id', $admin->id))
    ->latest()
    ->get()
    ->flatMap(fn ($chat) => [$chat->sender_id, $chat->receiver_id])
    ->unique()
    ->filter(fn ($id) => $id !== $admin->id)
    ->values();

print_r($adminChats->toArray());
