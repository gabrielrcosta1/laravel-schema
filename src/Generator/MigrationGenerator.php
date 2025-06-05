<?php

namespace LaravelSchemaPy\Generator;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigrationGenerator
{
  public static function generate(string $table, array $columns): void
  {
    $hashDir = base_path('.schema-cache');
    $migrationDir = base_path('database/migrations');
    $template = file_get_contents(base_path('templates/migration.php.stub'));

    File::ensureDirectoryExists($migrationDir);
    File::ensureDirectoryExists($hashDir);

    $hash = md5(json_encode($columns));
    $hashPath = "$hashDir/{$table}.hash";

    if (File::exists($hashPath) && trim(File::get($hashPath)) === $hash) return;

    $existing = collect(File::files($migrationDir))
      ->first(fn($f) => Str::contains($f->getFilename(), "create_{$table}_table"));

    $filename = $existing
      ? $existing->getFilename()
      : now()->format('Y_m_d_His') . "_create_{$table}_table.php";

    $lines = [];
    foreach ($columns as $col) {
      $lines[] = self::convertColumn($col);
    }

    $output = str_replace(['{{table}}', '{{columns}}'], [$table, implode("\n            ", $lines)], $template);
    File::put("$migrationDir/$filename", $output);
    File::put($hashPath, $hash);
  }

  private static function convertColumn(array $field): string
  {
    $name = $field['name'];
    $type = $field['type'];
    $modifiers = $field['modifiers'];

    if ($type === 'special') {
      if ($name === 'timestamps') return '$table->timestamps();';
      if ($name === 'softDeletes') return '$table->softDeletes();';
      return "// Unknown directive: $name";
    }

    if ($type === 'primary') return '$table->id();';

    if (str_starts_with($type, 'foreign:')) {
      [$refTable, $refCol] = explode('.', substr($type, 8));
      $line = "$table->foreignId('$name')->constrained('$refTable')->references('$refCol')";
    } elseif ($type === 'rememberToken') {
      return '$table->rememberToken();';
    } else {
      $line = "$table->$type('$name')";
    }

    if (in_array('nullable', $modifiers)) $line .= '->nullable()';
    if (in_array('unique', $modifiers)) $line .= '->unique()';
    if (in_array('index', $modifiers)) $line .= '->index()';

    return $line . ';';
  }
}
