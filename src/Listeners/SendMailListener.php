<?php

namespace TemplateGenius\TemplateGenius\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionProperty;

class SendMailListener
{
    public function handle($event, $data)
    {
        $events = Storage::get('template-genius-events.json');

        if (! $events) {
            return;
        }

        $events = json_decode($events, true);

        if (in_array($event, $events)) {
            $this->sendMail($event, $data[0]);
        }
    }

    public function sendMail($event, $data)
    {
        $token = config('template-genius.api_token');

        $types = $this->getClassFields($data);

        $result = Http::withHeaders([
            'Authorization' => "Bearer $token",
        ])
            ->acceptJson()
            ->withOptions(['verify' => false])
            ->post(rtrim(config('template-genius.api_url'), '/').'/events', [
                'event' => str($event)->afterLast('\\'),
                'types' => $types,
            ]);

        if ($result->failed()) {
            throw new \Exception('Sync failed!');
        }
    }

    protected function getClassFields($class, $nested = false)
    {
        $reflectionClass = new ReflectionClass($class);

        return collect($reflectionClass->getProperties())
            ->filter(fn (ReflectionProperty $property) => $property->isPublic())
            ->map(function (ReflectionProperty $property) use ($class, $nested) {
                $name = Str::of($property->getType()?->getName())->afterLast('\\')->toString();

                if ($property->getType()?->isBuiltin()) {
                    return [
                        'name' => $name,
                        'as' => $property->getName(),
                        'value' => $class->{$property->getName()},
                    ];
                }

                if ($nested || is_null($class->{$property->getName()})) {
                    return;
                }

                return [
                    'name' => $name,
                    'as' => $property->getName(),
                    'types' => $this->getNestedTypes($class->{$property->getName()}),
                ];
            })
            ->filter()
            ->toArray();
    }

    protected function getNestedTypes($instance)
    {
        return $instance instanceof Model
            ? $this->getEloquentFields($instance)
            : $this->getClassFields($instance, true);
    }

    public function getEloquentFields($class): array
    {
        $reflectionClass = new ReflectionClass($class);
        $table = (new ($reflectionClass->getName()))->getTable();
        $columns = Schema::getColumnListing($table);

        return collect($columns)
            ->map(function ($column) use ($class) {
                return [
                    'name' => $column,
                    'value' => $class->{$column},
                ];
            })
            ->filter()
            ->toArray();
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
