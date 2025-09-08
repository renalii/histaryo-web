<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Services\FirebaseService;

class FirebaseMigrate extends Command
{
    protected $signature = 'firebase:migrate 
        {--fresh : Delete all Firebase data (Auth, Firestore, Storage) then re-run migrations} 
        {--rollback : Rollback the last Firebase migration batch}';

    protected $description = 'Run Firestore/Firebase migrations';

    public function handle()
    {
        $path = database_path('firebase-migrations');
        $firebase = app(FirebaseService::class);

        // 🔥 Handle --fresh: wipe ALL Firebase data
        if ($this->option('fresh')) {
            $this->warn('⚠ Deleting ALL Firebase data (Auth users + Firestore collections + Storage)...');

            // 1. Delete all users
            $auth = $firebase->auth();
            $users = $auth->listUsers();
            foreach ($users as $user) {
                $auth->deleteUser($user->uid);
                $this->line("   Deleted user: {$user->email}");
            }

            // 2. Delete all Firestore collections
            $firestore = $firebase->firestore();
            $collections = $firestore->collections();
            foreach ($collections as $collection) {
                $this->deleteCollection($collection, 50);
                $this->line("   Deleted collection: {$collection->id()}");
            }

            // 3. Delete all Firebase Storage files
            $storage = $firebase->storage();
            $bucket = $storage->getBucket();
            foreach ($bucket->objects() as $object) {
                $bucket->object($object->name())->delete();
                $this->line("   Deleted storage file: {$object->name()}");
            }

            $this->info('✅ Firebase data wiped!');
        }

        // Run migrations
        if (!File::exists($path)) {
            $this->error("Firebase migrations folder not found: $path");
            return Command::FAILURE;
        }

        $files = File::files($path);

        foreach ($files as $file) {
            $this->info("Running migration: " . $file->getFilename());
            $migration = require $file->getPathname();

            if ($this->option('rollback')) {
                if (method_exists($migration, 'down')) {
                    $migration->down();
                    $this->line(" ↳ Rolled back " . $file->getFilename());
                } else {
                    $this->warn(" ↳ No down() method for " . $file->getFilename());
                }
            } else {
                $migration->up();
                $this->line(" ↳ Migrated " . $file->getFilename());
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Recursively delete all docs in a Firestore collection
     */
    protected function deleteCollection($collectionRef, $batchSize = 50)
    {
        $documents = $collectionRef->limit($batchSize)->documents();
        $deleted = 0;

        foreach ($documents as $document) {
            $document->reference()->delete();
            $deleted++;
        }

        if ($deleted >= $batchSize) {
            $this->deleteCollection($collectionRef, $batchSize);
        }
    }
}
