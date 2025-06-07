<?php

declare(strict_types=1);

namespace LaravelSchema\Generator;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigrationGenerator
{
    public static function generate(string $table, array $columns): void
    {
        $paths = self::resolvePaths($table);
        $hash = self::calculateHash($columns);

        if (self::isUnchanged($paths['hash'], $hash)) {
            return;
        }

        $filename = self::resolveFilename($table, $paths['migrations']);
        $content = self::buildMigration($table, $columns);

        File::put("{$paths['migrations']}/{$filename}", $content);
        File::put($paths['hash'], $hash);
    }

    private static function resolvePaths(string $table): array
    {

        $migrations = config('app.migrations_dir');
        $hashDir = config('app.hash_cache_dir');

        File::ensureDirectoryExists($migrations);
        File::ensureDirectoryExists($hashDir);

        return [
            'migrations' => $migrations,
            'hash' => config('app.hash_cache_dir')."/{$table}.hash",
            'template' => config('app.template_dir'),
        ];
    }

    private static function calculateHash(array $columns): string
    {
        return md5(json_encode($columns));
    }

    private static function isUnchanged(string $hashFile, string $currentHash): bool
    {
        return File::exists($hashFile) && mb_trim(File::get($hashFile)) === $currentHash;
    }

    private static function resolveFilename(string $table, string $dir): string
    {
        $existing = collect(File::files($dir))
            ->first(fn ($f) => Str::contains($f->getFilename(), "create_{$table}_table"));

        return $existing
            ? $existing->getFilename()
            : now()->format('Y_m_d_His')."_create_{$table}_table.php";
    }

    private static function buildMigration(string $table, array $columns): string
    {
        $template = file_get_contents(config('app.template_dir'));
        $lines = collect($columns)
            ->map(fn ($col) => self::renderColumn($col))
            ->implode("\n            ");

        return str_replace(
            ['{{table}}', '{{columns}}'],
            [$table, $lines],
            $template
        );
    }

    private static function renderColumn(array $field): string
    {
        return match ($field['type']) {
            'special' => self::renderSpecial($field['name']),
            'primary' => '$table->id();',
            'rememberToken' => '$table->rememberToken();',
            default => self::renderStandard($field),
        };
    }

    private static function renderSpecial(string $name): string
    {
        return match ($name) {
            'timestamps' => '$table->timestamps();',
            'softDeletes' => '$table->softDeletes();',
            default => "// Unknown directive: {$name}",
        };
    }

    private static function renderStandard(array $field): string
    {
        $name = $field['name'];
        $type = $field['type'];
        $modifiers = collect($field['modifiers']);

        $line = str_starts_with($type, 'foreign:')
            ? self::renderForeign($name, $type)
            : "\$table->{$type}('{$name}')";

        return $modifiers
            ->reduce(fn ($acc, $mod) => "{$acc}->".self::modifierCall($mod), $line).';';
    }

    private static function renderForeign(string $name, string $type): string
    {
        [$refTable, $refColumn] = explode('.', mb_substr($type, 8));

        return "\$table->foreignId('{$name}')->constrained('{$refTable}')->references('{$refColumn}')";
    }

    private static function modifierCall(string $modifier): string
    {
        return match ($modifier) {
            'nullable' => 'nullable()',
            'unique' => 'unique()',
            'index' => 'index()',
            default => '// unknown modifier: '.$modifier,
        };
    }
}
