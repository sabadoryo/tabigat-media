<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:user {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create user by given email, password';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::create([
            'name' => 'admin',
            'email' => $email,
            'password' => $password
        ]);

        $user->createToken('auth-token',['ultra-admin-actions', 'admin-actions']);

        $this->info('User created');

        return Command::SUCCESS;
    }
}
