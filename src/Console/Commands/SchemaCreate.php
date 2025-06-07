<?php

declare(strict_types=1);

namespace LaravelSchema\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SchemaCreate extends Command
{
    protected $signature = 'schema:create';

    protected $description = 'Create the initial schema.db file';

    public function handle()
    {
        $path = config('app.schema_dir');

        if (File::exists($path)) {
            $this->warn('schema.db already exists. No changes made.');

            return;
        }

        $content = <<<'EOT'
table posts {
    id primary
    title string
    content text
    timestamps
    softDeletes
}
EOT;

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        $this->info('schema.db created.');
    }
}
