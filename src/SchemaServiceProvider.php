<?php

declare(strict_types=1);

namespace LaravelSchema;

use Illuminate\Support\ServiceProvider;
use LaravelSchema\Console\Commands\SchemaCreate;
use LaravelSchema\Console\Commands\SchemaMigrate;
use LaravelSchema\Console\Commands\SchemaReset;

class SchemaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            SchemaCreate::class,
            SchemaMigrate::class,
            SchemaReset::class,
        ]);
        $this->mergeConfigFrom(__DIR__.'/config/app.php', 'app');
    }
}
