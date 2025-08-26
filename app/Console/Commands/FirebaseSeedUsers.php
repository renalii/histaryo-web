<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\FirebaseException;

class FirebaseSeedUsers extends Command
{
    protected $signature = 'firebase:seed-users';
    protected $description = 'Seed predefined users into Firebase Auth';

    protected $auth;

    public function __construct(FirebaseService $firebaseService)
    {
        parent::__construct();
        $this->auth = $firebaseService->auth();
    }

    public function handle()
    {
        $this->info("🔄 Seeding Firebase users...");

        $users = [
            [
                'email' => 'jessabelgera@gmail.com',
                'password' => 'password123',
                'role' => 'admin',
            ],
            [
                'email' => 'luigicanete@gmail.com',
                'password' => 'password123',
                'role' => 'curator',
            ],
            [
                'email' => 'renaolivoxyz@gmail.com',
                'password' => 'password123',
                'role' => 'curator',
            ],
            [
                'email' => 'evamae@gmail.com',
                'password' => 'password123',
                'role' => null, // No role
            ],
        ];

        foreach ($users as $user) {
            try {
                $createdUser = $this->auth->createUser([
                    'email' => $user['email'],
                    'password' => $user['password'],
                ]);

                if ($user['role']) {
                    $this->auth->setCustomUserClaims($createdUser->uid, [
                        'role' => $user['role'],
                    ]);
                }

                $this->info("✅ Created: {$user['email']} (Role: {$user['role']})");
            } catch (EmailExists $e) {
                $this->warn("⚠️ Skipped (already exists): {$user['email']}");
            } catch (FirebaseException $e) {
                $this->error("❌ Failed for {$user['email']}: " . $e->getMessage());
            }
        }

        $this->info("🎉 User seeding complete.");
    }
}
