<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-user {--email=admin@irteguhsolution.com} {--password=admin123}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');

        // Use plain password - Laravel will hash it automatically due to 'hashed' cast
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrator',
                'password' => $password, // Will be hashed automatically by Laravel
                'is_admin' => true,
            ]
        );

        $this->info("Admin user created/updated successfully!");
        $this->info("Email: {$email}");
        $this->info("Password: {$password}");
        $this->info("User ID: {$user->id}");
        
        // Verify password
        if (Hash::check($password, $user->password)) {
            $this->info("✓ Password verification: OK");
        } else {
            $this->error("✗ Password verification: FAILED");
        }

        return Command::SUCCESS;
    }
}
