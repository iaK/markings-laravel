<?php 

namespace Markings\Actions;

use ReflectionProperty;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Object_;

class GetPropertyInformationAction extends Action
{
    public function handle(ReflectionProperty $property)
    {
        $name = Str::of($property->getType()?->getName())->afterLast('\\')->toString();

        $isCollection = false;
        
        if ($name) {
            $type = $this->mapInternalType($property->getType()?->getName());
        } else {
            $name = 'string';
            $type = 'string';
        }

        if ($type == 'array') {
            $factory  = \phpDocumentor\Reflection\DocBlockFactory::createInstance();

            if (! $property->getDocComment()) {
                $type = 'string';
                $name = 'string';
            } else {
                $docblock = $factory->create($property->getDocComment());
                $collectionType = $docblock->getTagsWithTypeByName('var')[0]->getType()->getValueType();

                if ($collectionType instanceof Array_) {
                    return false;
                }

                $name = $collectionType instanceof Object_ 
                    ? $collectionType->getFqsen()->getName()
                    : $collectionType->__toString();
                $type = $this->mapInternalType($name);
            }

            $isCollection = true;
        }

        if (! $type) {
            return false;
        }

        return [
            'name' => $name,
            'as' => $property->getName(),
            'type' => $type,
            'nullable' => $property->getType()?->allowsNull() ?? false,
            'multiple' => $isCollection,
        ];
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
            \Closure::class => null,
            'array' => 'array',
            'object' => null,
            'callable' => null,
            'iterable' => null,
            default => 'custom',
        };
    }
}
