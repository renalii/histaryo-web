<?php

namespace App\Firebase;

use App\Services\FirebaseService;

abstract class FirebaseMigration
{
    protected FirebaseService $firebase;

    public function __construct()
    {
        $this->firebase = app(FirebaseService::class);
    }

    /**
     * Run the migration
     */
    abstract public function up();

    /**
     * Rollback the migration (optional)
     */
    public function down()
    {
        // Default: do nothing
    }

    protected function auth()
    {
        return $this->firebase->auth();
    }

    protected function firestore()
    {
        return $this->firebase->firestore();
    }
}
