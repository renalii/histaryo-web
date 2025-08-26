<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;

class FirebaseMigrate extends Command
{
    protected $signature = 'firebase:migrate';
    protected $description = 'Seed or reset Firestore collections';

    protected $firestore;

    public function __construct(FirebaseService $firebaseService)
    {
        parent::__construct();
        $this->firestore = $firebaseService->firestore();
    }

    public function handle()
    {
        $this->info("🔄 Migrating Firebase data...");

        // Example: delete all in 'landmarks' collection
        $documents = $this->firestore->collection('landmarks')->documents();
        foreach ($documents as $doc) {
            $doc->reference()->delete();
        }

        // Example: seed sample data
        $this->firestore->collection('landmarks')->add([
            'name' => "Magellan's Cross",
            'latitude' => 10.2936,
            'longitude' => 123.9020,
            'description' => "A significant religious artifact in Cebu.",
            'created_at' => now(),
        ]);

        $this->info("✅ Firebase migration complete.");
    }
}
