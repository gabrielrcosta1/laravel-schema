<?php

namespace LaravelSchemaPy\Parser;

class SchemaParser
{
  public static function parseFile(string $path): array
  {
    $content = file_get_contents($path);
    preg_match_all('/table (\w+)\s*{([^}]+)}/', $content, $matches, PREG_SET_ORDER);

    $tables = [];

    foreach ($matches as $match) {
      $tableName = trim($match[1]);
      $body = trim($match[2]);
      $lines = preg_split("/\r?\n/", $body);

      $columns = [];
      foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $parts = preg_split('/\s+/', $line);

        if (count($parts) === 1) {
          $columns[] = [
            'name' => $parts[0],
            'type' => 'special',
            'modifiers' => []
          ];
        } else {
          $columns[] = [
            'name' => $parts[0],
            'type' => $parts[1],
            'modifiers' => array_slice($parts, 2)
          ];
        }
      }

      $tables[$tableName] = $columns;
    }

    return $tables;
  }
}
