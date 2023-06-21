<?php

namespace TemplateGenius\TemplateGenius\Commands;

use DateTime;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionProperty;
use RegexIterator;

class SyncTypesCommand extends Command
{
    public $signature = 'template-genius:sync-types';

    public $description = 'My command';

    public array $skippedTypes = [];

    public function handle(): int
    {
        $this->comment('Sync started..');

        $success = collect(config('template-genius.types_paths'))
            ->map(function ($path) {
                $dir = new RecursiveDirectoryIterator($path);
                $ite = new RecursiveIteratorIterator($dir);
                $files = new RegexIterator($ite, '/.*.php/', RegexIterator::GET_MATCH);

                return collect($files)->map(fn ($file) => $file);
            })
            ->flatten()
            ->values()
            ->map(function ($file) {
                $this->comment("Parsing file: $file");

                return $this->parseFile($file);
            })
            ->pipe(function ($types) {
                $this->comment('Syncing to server..');

                $token = config('template-genius.api_token');

                $result = Http::withHeaders([
                    'Authorization' => "Bearer $token",
                ])
                    ->acceptJson()
                    ->withOptions(['verify' => false])
                    ->post(rtrim(config('template-genius.api_url'), '/').'/types', ['types' => $types]);

                if ($result->failed()) {
                    $this->error('Sync failed!');
                    $this->error($result->body());

                    return false;
                }

                $this->comment('Sync successful!');

                return true;
            });

        if (! $success) {
            return self::FAILURE;
        }

        return self::SUCCESS;
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

    protected function mapInternalType(string $type)
    {
        return match ($type) {
            'int' => 'integer',
            'float' => 'float',
            'string' => 'string',
            'bool' => 'boolean',
            DateTime::class => 'datetime',
            Carbon::class => 'datetime',
            \Carbon\Carbon::class => 'datetime',
            'array' => null,
            'object' => null,
            'callable' => null,
            'iterable' => null,
            default => null,
        };
    }

    public function getClass($path): ReflectionClass
    {
        $fileContents = file_get_contents($path);

        // Match namespace and class name using regular expressions
        preg_match('/namespace\s+(.*?);/s', $fileContents, $namespaceMatches);
        preg_match('/class\s+(\w+)/', $fileContents, $classMatches);

        $namespace = isset($namespaceMatches[1])
            ? $namespaceMatches[1]
            : null;

        $className = isset($classMatches[1])
            ? $classMatches[1]
            : null;

        $fullClassName = $namespace
            ? $namespace.'\\'.$className
            : $className;

        return new \ReflectionClass($fullClassName);
    }

    public function getEloquentFields(ReflectionClass $class): array
    {
        $table = (new ($class->getName()))->getTable();
        $columns = Schema::getColumnListing($table);

        return collect($columns)
            ->map(function ($column) use ($table, $class) {
                $type = $this->mapType(Schema::getColumnType($table, $column));

                if (is_null($type)) {
                    $this->skippedTypes[$class->getShortName()] = $column;

                    return null;
                }

                return [
                    'name' => $column,
                    'type' => $type,
                ];
            })
            ->filter()
            ->toArray();
    }

    public function parseFile(string $file)
    {
        $class = $this->getClass($file);

        $columns = $class->isSubclassOf(Model::class)
            ? $this->getEloquentFields($class)
            : $this->getClassFields($class);

        if (! empty($this->skippedTypes)) {
            $types = collect($this->skippedTypes)
                ->map(fn ($type, $name) => "$name: $type")
                ->implode(', ');

            $this->comment('Unknown types found. skipping: '.$types);
            $this->skippedTypes = [];
        }

        return [
            'name' => $class->getShortName(),
            'fields' => $columns,
        ];
    }

    public function getClassFields(ReflectionClass $class)
    {
        return collect($class->getProperties())
            ->filter(fn (ReflectionProperty $property) => $property->isPublic())
            ->map(function (ReflectionProperty $property) use ($class) {
                $type = $property->getType()?->getName() ?: 'string';

                $type = $this->mapInternalType($type);

                if (is_null($type)) {
                    $this->skippedTypes[$class->getShortName()] = $type;

                    return null;
                }

                return [
                    'name' => $property->getName(),
                    'type' => $type,
                ];
            })
            ->filter()
            ->toArray();
    }
}
