<?php

declare(strict_types=1);

return [
    'json_cache_dir' => base_path('vendor/gabrielrcosta1/laravel-schema/src/schema-cache'),
    'hash_cache_dir' => base_path('vendor/gabrielrcosta1/laravel-schema/src/.schema-cache'),
    'template_dir' => base_path('vendor/gabrielrcosta1/laravel-schema/templates/migration.php.stub'),
    'migrations_dir' => base_path('database/migrations'),
    'schema_dir' => base_path('database/schema.db'),
];
