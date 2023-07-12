<?php

use Markings\Markings\Actions\GetFilesInGlobPatternAction;

it('can get all files in a folder', function () {
    $files = GetFilesInGlobPatternAction::make()->handle(__DIR__.'/../TestClasses/GlobFiles');

    expect($files)->toHaveCount(2);
    expect($files->first())->toBe(__DIR__.'/../TestClasses/GlobFiles/sub/other.php');
    expect($files->last())->toBe(__DIR__.'/../TestClasses/GlobFiles/random.php');
});
