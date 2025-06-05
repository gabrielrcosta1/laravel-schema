<?php

declare(strict_types=1);

namespace LaravelSchema;

use Illuminate\Support\ServiceProvider;
use LaravelSchema\Console\Commands\SchemaCreate;
use LaravelSchema\Console\Commands\SchemaMigrate;

class SchemaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            SchemaCreate::class,
            SchemaMigrate::class,
        ]);
    }
}
