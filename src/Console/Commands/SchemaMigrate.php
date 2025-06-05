<?php

namespace LaravelSchemaPy\Console\Commands;

use Illuminate\Console\Command;
use LaravelSchemaPy\Parser\SchemaParser;
use LaravelSchemaPy\Generator\MigrationGenerator;

class SchemaMigrate extends Command
{
  protected $signature = 'schema:migrate';
  protected $description = 'Generate Laravel migrations from schema.db';

  public function handle()
  {
    $schema = SchemaParser::parseFile(base_path('database/schema.db'));
    foreach ($schema as $table => $columns) {
      $this->info("Generating migration for table: {$table}");
      MigrationGenerator::generate($table, $columns);
    }
    $this->info('Migrations generated. Run: php artisan migrate');
  }
}
