<?php

namespace Markings\Actions;

use DateTime;
use ReflectionProperty;
use Illuminate\Support\Str;
use ReflectionClass;
use Markings\Actions\Action;

class PpoFieldFinderAction extends Action
{
    public array $skippedTypes = [];

    public function handle(ReflectionClass $class) : array
    {
        $fields = collect($class->getProperties())
            ->filter(fn (ReflectionProperty $property) => $property->isPublic())
            ->map(function (ReflectionProperty $property) use ($class) {
                $name = Str::of($property->getType()?->getName())->afterLast('\\')->toString();

                if ($name) {
                    $type = $this->mapInternalType($property->getType()?->getName());
                } else {
                    $name = 'string';
                    $type = 'string';
                }

                if (! $type) {
                    $this->skippedTypes[$class->getShortName()] = $property->getName();

                    return null;
                }

                return [
                    'name' => $name,
                    'as' => $property->getName(),
                    'type' => $type,
                    'nullable' => $property->getType()?->allowsNull() ?? false,
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        return [$fields, $this->skippedTypes];
    }

    protected function mapInternalType(string $type)
    {
        return match ($type) {
            'int' => 'integer',
            'float' => 'float',
            'string' => 'string',
            'bool' => 'boolean',
            DateTime::class => 'datetime',
            \Carbon\Carbon::class => 'datetime',
            \Closure::class => null,
            'array' => null,
            'object' => null,
            'callable' => null,
            'iterable' => null,
            default => 'custom',
        };
    }
}
