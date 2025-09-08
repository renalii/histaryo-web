<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FirebaseMakeMigration extends Command
{
    protected $signature = 'firebase:make-migration {name : The name of the migration}';
    protected $description = 'Create a new Firestore migration file';

    public function handle()
    {
        $name = $this->argument('name');
        $timestamp = now()->format('Y_m_d_His');
        $fileName = "{$timestamp}_{$name}.php";

        $path = database_path("firebase-migrations/{$fileName}");

        if (! File::exists(database_path('firebase-migrations'))) {
            File::makeDirectory(database_path('firebase-migrations'), 0755, true);
        }

        $stub = <<<PHP
<?php

use App\Firebase\FirebaseMigration;

return new class extends FirebaseMigration {
    public function up()
    {
        // TODO: Add Firestore or Auth changes
    }

    public function down()
    {
        // TODO: Rollback changes
    }
};
PHP;

        File::put($path, $stub);

        $this->info("✅ Firestore migration created: {$fileName}");
    }
}
