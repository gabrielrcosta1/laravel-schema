<?php

declare(strict_types=1);

namespace LaravelSchema\Generator;

use Illuminate\Support\Facades\File;

class MigrationChangeGenerator
{
    public static function generate(string $table, array $changes): void
    {
        $dir = config('app.migrations_dir');
        File::ensureDirectoryExists($dir);

        if (! empty($changes['added'])) {
            self::createMigration($table, $changes['added'], 'add', $dir);
        }

        if (! empty($changes['removed'])) {
            self::createMigration($table, $changes['removed'], 'remove', $dir);
        }
    }

    private static function createMigration(string $table, array $columns, string $action, string $dir): void
    {
        $prefix = now()->format('Y_m_d_His');
        $name = match ($action) {
            'add' => "{$prefix}_add_columns_to_{$table}_table.php",
            'remove' => "{$prefix}_remove_columns_from_{$table}_table.php",
        };

        $method = $action === 'add' ? 'addColumnLine' : 'dropColumnLine';

        $lines = collect($columns)->map(fn ($col) => self::{$method}($col))->implode("\n            ");

        $content = str_replace(
            ['{{table}}', '{{up}}', '{{down}}'],
            [$table, $lines, '// Manual revert required'],
            self::template()
        );

        File::put("{$dir}/{$name}", $content);
    }

    private static function addColumnLine(array $col): string
    {
        $line = match (true) {
            $col['type'] === 'primary' => '\$table->id();',
            str_starts_with($col['type'], 'foreign:') => self::foreignLine($col),
            $col['type'] === 'rememberToken' => '\$table->rememberToken();',
            default => "\$table->{$col['type']}('{$col['name']}')"
        };

        return collect($col['modifiers'])
            ->reduce(fn ($acc, $m) => "$acc->".self::modifierCall($m), $line).';';
    }

    private static function dropColumnLine(array $col): string
    {
        return "\$table->dropColumn('{$col['name']}');";
    }

    private static function foreignLine(array $col): string
    {
        [$refTable, $refCol] = explode('.', mb_substr($col['type'], 8));

        return "\$table->foreignId('{$col['name']}')->constrained('{$refTable}')->references('{$refCol}')";
    }

    private static function modifierCall(string $mod): string
    {
        return match ($mod) {
            'nullable' => 'nullable()',
            'unique' => 'unique()',
            'index' => 'index()',
            default => '// unknown modifier'
        };
    }

    private static function template(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('{{table}}', function (Blueprint $table) {
            {{up}}
        });
    }

    public function down(): void
    {
        {{down}}
    }
};
PHP;
    }
}
