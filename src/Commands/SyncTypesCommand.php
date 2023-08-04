<?php

namespace Markings\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Markings\Actions\Api;
use Markings\Actions\EloquentFieldParserAction;
use Markings\Actions\FindClassFromPathAction;
use Markings\Actions\GetFilesInGlobPatternAction;
use Markings\Actions\PopoFieldParserAction;
use Markings\Exceptions\FilesNotFoundException;
use ReflectionClass;
use Src\Traits\HandlesEnvironments;

class SyncTypesCommand extends Command
{
    use HandlesEnvironments;

    public $signature = 'markings:sync-types';

    public $description = 'Sync all your types to Markings';

    public array $skippedTypes = [];

    public function handle(): int
    {
        $this->comment('Type sync started..');

        try {
            $success = collect(config('markings.types_paths'))
                ->map(fn ($path) => GetFilesInGlobPatternAction::make()->handle($path))
                ->flatten()
                ->map(fn ($file) => FindClassFromPathAction::make()->handle($file))
                ->reject(function (ReflectionClass $class) {
                    if (in_array($class->getName(), config('markings.exclude_files'))) {
                        $this->comment('Skipping class: '.$class->getName());

                        return true;
                    }

                    $this->comment('Parsing class: '.$class->getName());

                    return false;
                })
                ->map(fn (ReflectionClass $class) => $this->parseFile($class))
                ->filter()
                ->filter(fn ($type) => ! empty($type['fields']))
                ->values()
                ->pipe(function ($types) {
                    if (! $this->handleEnvironment()) {
                        throw new Exception('Sync failed!');
                    }

                    return $types;
                })
                ->pipe(function ($types) {
                    $this->comment('Syncing to server..');

                    $result = Api::syncTypes($types);

                    if ($result->failed()) {
                        $this->error('There was an unexpected error when calling the server. Error message:');
                        $this->error($result->body());
                        $this->error('Sync failed!');

                        return false;
                    }

                    $this->comment('Sync successful!');

                    return true;
                });
        } catch (FilesNotFoundException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error('There was an unexpected error. Error message: '.$e->getMessage());

            return self::FAILURE;
        }

        if (! $success) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    public function parseFile(ReflectionClass $class)
    {
        if ($class->isAbstract()) {
            return false;
        }

        [$columns, $skippedTypes] = $class->isSubclassOf(Model::class)
            ? EloquentFieldParserAction::make()->handle($class)
            : PopoFieldParserAction::make()->handle($class);

        if (! empty($skippedTypes)) {
            $types = collect($skippedTypes)
                ->map(fn ($type, $name) => "$name: $type")
                ->implode(', ');

            $this->comment('Unknown types found. skipping: '.$types);
        }

        return [
            'name' => $class->getShortName(),
            'fields' => $columns,
        ];
    }
}
