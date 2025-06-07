<?php

declare(strict_types=1);

namespace LaravelSchema\Diff;

class SchemaDiff
{
    public static function compare(array $old, array $new): array
    {
        $added = collect($new)->reject(fn ($n) => self::existsIn($n, $old))->values()->all();
        $removed = collect($old)->reject(fn ($o) => self::existsIn($o, $new))->values()->all();

        return [
            'added' => $added,
            'removed' => $removed,
        ];
    }

    private static function existsIn(array $column, array $columns): bool
    {
        return collect($columns)->contains(
            fn ($col) => $col['name'] === $column['name'] &&
                $col['type'] === $column['type'] &&
                $col['modifiers'] === $column['modifiers']
        );
    }
}
