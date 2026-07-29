<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset-password {username=admin} {password=admin123}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the admin password';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->argument('password');

        $admin = User::where('username', $username)->where('role', 'admin')->first();

        if (!$admin) {
            $this->error("Admin user with username '{$username}' not found.");
            return Command::FAILURE;
        }

        $admin->password = Hash::make($password);
        $admin->save();

        $this->info("Password for admin '{$username}' has been reset to '{$password}'.");
        return Command::SUCCESS;
    }
}
