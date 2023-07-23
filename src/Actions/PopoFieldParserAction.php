<?php

namespace Markings\Actions;

use ReflectionClass;
use ReflectionProperty;

class PopoFieldParserAction extends Action
{
    public array $skippedTypes = [];

    public function handle(ReflectionClass $class): array
    {
        $fields = collect($class->getProperties())
            ->filter(fn (ReflectionProperty $property) => $property->isPublic())
            ->map(function (ReflectionProperty $property) use ($class) {
                $information = GetPropertyInformationAction::make()->handle($property);

                if (!$information) {
                    $this->skippedTypes[$class->getShortName()] = $property->getName();

                    return null;
                }

                return [
                    'name' => $information['as'],
                    'type' => $information['type'],
                    'nullable' => $information['nullable'],
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        return [$fields, $this->skippedTypes];
    }
}
