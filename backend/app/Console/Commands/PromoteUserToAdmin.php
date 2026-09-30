<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteUserToAdmin extends Command
{
    protected $signature = 'studyflow:make-admin {email : Email of the existing account to promote}';

    protected $description = 'Grant StudyFlow administrator access to an existing user';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user exists with that email address.');

            return self::FAILURE;
        }

        $user->forceFill(['is_admin' => true])->save();
        $this->info("Administrator access granted to {$user->email}.");

        return self::SUCCESS;
    }
}