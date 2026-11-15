<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    protected $signature = 'club:make-admin {email : Email address of an existing member account}';

    protected $description = 'Grant admin access to an existing GB ClLUB account';

    public function handle(): int
    {
        $user = User::whereRaw('LOWER(email) = ?', [strtolower($this->argument('email'))])->first();

        if (! $user) {
            $this->error('No account exists with that email. Register the user first.');

            return self::FAILURE;
        }

        $user->update(['role' => 'admin']);
        $this->info("{$user->email} can now access the admin dashboard.");

        return self::SUCCESS;
    }
}
