<?php

namespace Markings\Actions;

use ReflectionClass;

class FindClassFromPathAction extends Action
{
    public function handle(string $path): ReflectionClass
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

        return new ReflectionClass($fullClassName);
    }
}
