<?php

namespace LaravelSchemaPy\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SchemaCreate extends Command
{
  protected $signature = 'schema:create';
  protected $description = 'Create the initial schema.db file';

  public function handle()
  {
    $path = base_path('database/schema.db');

    if (File::exists($path)) {
      $this->warn('schema.db already exists. No changes made.');
      return;
    }

    $content = <<<EOT
table users {
    id primary
    name string
    email string unique
    email_verified_at timestamp nullable
    password string
    remember_token rememberToken
    timestamps
}

table password_reset_tokens {
    email string primary
    token string
    created_at timestamp nullable
}

table sessions {
    id string primary
    user_id foreign:users.id nullable index
    ip_address string nullable length:45
    user_agent text nullable
    payload longText
    last_activity integer index
}
EOT;

    File::ensureDirectoryExists(dirname($path));
    File::put($path, $content);
    $this->info('schema.db created.');
  }
}
