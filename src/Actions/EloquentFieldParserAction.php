<?php

namespace Markings\Actions;

use Illuminate\Support\Facades\Schema;
use ReflectionClass;

class EloquentFieldParserAction extends Action
{
    public array $skippedTypes = [];

    public function handle(ReflectionClass $class): array
    {
        $table = (new ($class->getName()))->getTable();
        $columns = Schema::getColumnListing($table);

        $fields = collect($columns)
            ->map(function ($column) use ($table, $class) {
                $hidden = (new $class->name)->hidden ?? [];
                
                if (in_array($column, $hidden)) {
                    return null;
                }

                $type = $this->mapType(Schema::getColumnType($table, $column));

                if (is_null($type)) {
                    $this->skippedTypes[$class->getShortName()] = $column;

                    return null;
                }

                return [
                    'name' => $column,
                    'nullable' => ! Schema::getConnection()->getDoctrineColumn($table, $column)->getNotnull(),
                    'type' => $type,
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        return [$fields, $this->skippedTypes];
    }

    protected function mapType(string $type)
    {
        return match ($type) {
            'string' => 'string',
            'char' => 'string',
            'varchar' => 'string',
            'binary' => 'string',
            'varbinary' => 'string',
            'tinyblob' => 'string',
            'tinytext' => 'string',
            'text' => 'string',
            'blob' => 'string',
            'mediumtext' => 'string',
            'mediumblob' => 'string',
            'longtext' => 'string',
            'longblob' => 'string',
            'enum' => 'string',
            'bit' => 'integer',
            'tinyint' => 'boolean',
            'bool' => 'boolean',
            'boolean' => 'boolean',
            'smallint' => 'integer',
            'mediumint' => 'integer',
            'int' => 'integer',
            'integer' => 'integer',
            'bigint' => 'integer',
            'float' => 'float',
            'double' => 'float',
            'double precision' => 'float',
            'decimal' => 'float',
            'dec' => 'float',
            'date' => 'datetime',
            'datetime' => 'datetime',
            'timestamp' => 'time',
            'year' => 'datetime',
            'time' => 'time',
            default => null,
        };
    }
}
