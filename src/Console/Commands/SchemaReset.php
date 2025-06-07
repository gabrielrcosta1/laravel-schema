<?php

declare(strict_types=1);

namespace LaravelSchema\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SchemaReset extends Command
{
    protected $signature = 'schema:reset {--all}';

    protected $description = 'Delete generated migration files and schema cache for one or all tables.';

    public function handle(): void
    {
        $schemaPath = config('app.schema_dir');
        $jsonCacheDir = config('app.json_cache_dir');
        $hashCacheDir = config('app.hash_cache_dir');
        $migrationDir = config('app.migrations_dir');

        if (! File::exists($schemaPath)) {
            $this->error('schema.db not found.');

            return;
        }

        $content = File::get($schemaPath);
        preg_match_all('/table (\w+)\s*{/', $content, $matches);
        $tables = $matches[1] ?? [];

        if (! $this->option('all')) {
            $table = $this->ask('Enter the table name to reset');

            if (! in_array($table, $tables)) {
                $this->error("Table '{$table}' not found in schema.db");

                return;
            }

            $this->deleteMigration($table, $migrationDir);
            $this->deleteCache($table, $jsonCacheDir, $hashCacheDir);

            $this->info("✔ Reset complete for '{$table}'");

            return;
        }

        foreach ($tables as $table) {
            $this->deleteMigration($table, $migrationDir);
            $this->deleteCache($table, $jsonCacheDir, $hashCacheDir);
        }

        $this->info('✔ Full reset complete.');
    }

    private function deleteMigration(string $table, string $dir): void
    {
        foreach (File::files($dir) as $file) {
            $name = $file->getFilename();

            if (
                str_contains($name, "create_{$table}_table") ||
                str_contains($name, "add_columns_to_{$table}_table") ||
                str_contains($name, "remove_columns_from_{$table}_table")
            ) {
                File::delete($file->getPathname());
                $this->line("Deleted migration: {$name}");
            }
        }
    }

    private function deleteCache(string $table, string $jsonDir, string $hashDir): void
    {
        $json = "{$jsonDir}/{$table}.json";
        $hash = "{$hashDir}/{$table}.hash";

        if (File::exists($json)) {
            File::delete($json);
        }

        if (File::exists($hash)) {
            File::delete($hash);
        }
    }
}
