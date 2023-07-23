<?php

namespace Markings\Actions;

use DateTime;
use ReflectionClass;
use ReflectionProperty;

class PopoFieldFinderAction extends Action
{
    public array $skippedTypes = [];

    public function handle(ReflectionClass $class): array
    {
        $fields = collect($class->getProperties())
            ->filter(fn (ReflectionProperty $property) => $property->isPublic())
            // Fix this..
            ->reject(fn (ReflectionProperty $property) => $property->getName() == 'socket')
            ->map(function (ReflectionProperty $property) use ($class) {
                $information = GetPropertyInformationAction::make()->handle($property);

                if (! $information) {
                    $this->skippedTypes[$class->getShortName()] = $property->getName();

                    return null;
                }

                return $information;
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
            'array' => 'array',
            'object' => null,
            'callable' => null,
            'iterable' => null,
            default => 'custom',
        };
    }
}
