<?php

declare(strict_types=1);

namespace LaravelSchema\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use LaravelSchema\Diff\SchemaDiff;
use LaravelSchema\Generator\MigrationChangeGenerator;
use LaravelSchema\Generator\MigrationGenerator;
use LaravelSchema\Parser\SchemaParser;

class SchemaMigrate extends Command
{
    protected $signature = 'schema:migrate';

    protected $description = 'Generate Laravel migrations from schema.db';

    public function handle()
    {
        $schemaFile = config('app.schema_dir');
        $cachePath = config('app.json_cache_dir');

        File::ensureDirectoryExists($cachePath);

        $parsed = SchemaParser::parseFile($schemaFile);

        foreach ($parsed as $table => $newSchema) {
            $cachedFile = "{$cachePath}/{$table}.json";

            if (! File::exists($cachedFile)) {
                $this->info("Creating initial migration for table: {$table}");
                MigrationGenerator::generate($table, $newSchema);
                File::put($cachedFile, json_encode($newSchema, JSON_PRETTY_PRINT));

                continue;
            }

            $oldSchema = json_decode(File::get($cachedFile), true);
            $changes = SchemaDiff::compare($oldSchema, $newSchema);

            if (empty($changes['added']) && empty($changes['removed'])) {
                $this->line("No changes detected for table: {$table}");

                continue;
            }

            $this->warn("Changes detected in '{$table}' → generating incremental migration...");
            MigrationChangeGenerator::generate($table, $changes);
            File::put($cachedFile, json_encode($newSchema, JSON_PRETTY_PRINT));
        }

        $this->info('Done. Run: php artisan migrate');
    }
}
