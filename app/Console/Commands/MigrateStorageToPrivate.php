<?php

namespace App\Console\Commands;

use App\Models\Exercise;
use App\Models\School;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('app:migrate-storage-to-private')]
#[Description('Migrate exercise images and school logos from public to private storage.')]
class MigrateStorageToPrivate extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->migrateExerciseImages();
        $this->migrateSchoolLogos();

        $this->info('Storage migration complete.');

        return self::SUCCESS;
    }

    private function migrateExerciseImages(): void
    {
        $exercises = Exercise::whereNotNull('image_path')->get();
        $migrated = 0;
        $skipped = 0;

        foreach ($exercises as $exercise) {
            if (! Storage::disk('public')->exists($exercise->image_path)) {
                $this->warn("Skipping exercise #{$exercise->id}: file not found at public/{$exercise->image_path}");
                $skipped++;

                continue;
            }

            $contents = Storage::disk('public')->get($exercise->image_path);
            Storage::disk('local')->put($exercise->image_path, $contents);
            Storage::disk('public')->delete($exercise->image_path);
            $migrated++;
        }

        $this->info("Exercises: {$migrated} migrated, {$skipped} skipped.");
    }

    private function migrateSchoolLogos(): void
    {
        $schools = School::whereNotNull('logo_path')->get();
        $migrated = 0;
        $skipped = 0;

        foreach ($schools as $school) {
            if (! Storage::disk('public')->exists($school->logo_path)) {
                $this->warn("Skipping school #{$school->id}: file not found at public/{$school->logo_path}");
                $skipped++;

                continue;
            }

            $contents = Storage::disk('public')->get($school->logo_path);
            Storage::disk('local')->put($school->logo_path, $contents);
            Storage::disk('public')->delete($school->logo_path);
            $migrated++;
        }

        $this->info("Schools: {$migrated} migrated, {$skipped} skipped.");
    }
}
