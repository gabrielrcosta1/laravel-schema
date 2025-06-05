<?php

namespace LaravelSchemaPy;

use Illuminate\Support\ServiceProvider;
use LaravelSchemaPy\Console\Commands\SchemaCreate;
use LaravelSchemaPy\Console\Commands\SchemaMigrate;

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
