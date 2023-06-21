<?php

namespace TemplateGenius\TemplateGenius\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionProperty;
use RegexIterator;

class SyncEventsCommand extends Command
{
    public $signature = 'template-genius:sync-events';

    public $description = 'My command';

    public array $skippedTypes = [];

    public function handle(): int
    {
        $this->comment('Event Sync started..');

        $success = collect(config('template-genius.events_paths'))
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
            ->pipe(function ($events) {
                $this->comment('Syncing to server..');

                $token = config('template-genius.api_token');

                $result = Http::withHeaders([
                    'Authorization' => "Bearer $token",
                ])
                    ->acceptJson()
                    ->withOptions(['verify' => false])
                    ->post(rtrim(config('template-genius.api_url'), '/').'/events/sync', ['events' => $events]);

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

    protected function parseFile($file)
    {
        $class = $this->getClass($file);
        $columns = $this->getClassFields($class);
        if (! empty($this->skippedTypes)) {
            $types = collect($this->skippedTypes)
                ->map(fn ($type, $name) => "$name: $type")
                ->implode(', ');

            $this->comment('Unknown types found. skipping: '.$types);
            $this->skippedTypes = [];
        }

        return [
            'name' => $class->getShortName(),
            'types' => $columns,
        ];
    }

    public function getClassFields(ReflectionClass $class)
    {
        return collect($class->getProperties())
            ->filter(fn (ReflectionProperty $property) => $property->isPublic())
            ->map(function (ReflectionProperty $property) use ($class) {
                $type = Str::of($property->getType()?->getName())->afterLast('\\')->toString() ?: 'string';

                if (is_null($type)) {
                    $this->skippedTypes[$class->getShortName()] = $type;

                    return null;
                }

                return [
                    'name' => $type,
                    'as' => $property->getName(),
                ];
            })
            ->filter()
            ->toArray();
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
}
