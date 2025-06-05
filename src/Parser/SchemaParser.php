<?php

namespace LaravelSchemaPy\Parser;

class SchemaParser
{
  public static function parseFile(string $path): array
  {
    $content = file_get_contents($path);

    $matches = [];
    preg_match_all('/table (\w+)\s*{([^}]+)}/', $content, $matches, PREG_SET_ORDER);

    return collect($matches)
      ->mapWithKeys(function ($match) {
        $table = trim($match[1]);
        $columns = self::parseColumns(trim($match[2]));

        return [$table => $columns];
      })
      ->all();
  }

  private static function parseColumns(string $body): array
  {
    return collect(preg_split("/\r?\n/", $body))
      ->map(fn($line) => trim($line))
      ->filter()
      ->map(fn($line) => self::parseLine($line))
      ->all();
  }

  private static function parseLine(string $line): array
  {
    $parts = preg_split('/\s+/', $line);

    return count($parts) === 1
      ? self::specialColumn($parts[0])
      : self::normalColumn($parts);
  }

  private static function specialColumn(string $name): array
  {
    return [
      'name' => $name,
      'type' => 'special',
      'modifiers' => []
    ];
  }

  private static function normalColumn(array $parts): array
  {
    return [
      'name' => $parts[0],
      'type' => $parts[1],
      'modifiers' => array_slice($parts, 2)
    ];
  }
}
