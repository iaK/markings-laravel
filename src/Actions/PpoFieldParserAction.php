<?php

namespace Markings\Actions;

use ReflectionClass;
use ReflectionProperty;
use Markings\Actions\Action;

class PpoFieldParserAction extends Action
{
    public array $skippedTypes = [];

    public function handle(ReflectionClass $class) : array
    {
        $fields = collect($class->getProperties())
            ->filter(fn (ReflectionProperty $property) => $property->isPublic())
            ->map(function (ReflectionProperty $property) use ($class) {
                $type = $property->getType()?->getName() ?: 'string';

                $type = $this->mapInternalType($type);

                if (is_null($type)) {
                    $this->skippedTypes[$class->getShortName()] = $property->getName();

                    return null;
                }

                return [
                    'name' => $property->getName(),
                    'nullable' => $property->getType()?->allowsNull() ?? false,
                    'type' => $type,
                ];
            })
            ->filter()
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
            \DateTime::class => 'datetime',
            \Carbon\Carbon::class => 'datetime',
            'array' => null,
            'object' => null,
            'callable' => null,
            'iterable' => null,
            default => null,
        };
    }
}
