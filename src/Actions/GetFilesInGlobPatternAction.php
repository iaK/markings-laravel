<?php

namespace Markings\Actions;

use Illuminate\Support\Collection;
use Markings\Exceptions\FilesNotFoundException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class GetFilesInGlobPatternAction extends Action
{
    public function handle(string $pattern): Collection
    {
        try {
            $dir = new RecursiveDirectoryIterator($pattern);
            $ite = new RecursiveIteratorIterator($dir);
            $files = new RegexIterator($ite, '/.*.php/', RegexIterator::GET_MATCH);

            return collect($files)->flatMap(fn ($file) => $file)->values();
        } catch (\Throwable $th) {
            throw new FilesNotFoundException("There was a problem finding your events. Make sure the following glob pattern is correct: $pattern");
        }
    }
}
