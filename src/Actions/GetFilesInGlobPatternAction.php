<?php

namespace Markings\Markings\Actions;

use Illuminate\Support\Collection;
use RegexIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Markings\Markings\Actions\Action;
use Markings\Markings\Exceptions\FilesNotFoundException;

class GetFilesInGlobPatternAction extends Action
{
    public function handle(string $pattern) : Collection
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
