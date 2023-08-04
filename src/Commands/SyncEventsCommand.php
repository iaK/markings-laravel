<?php

namespace Markings\Commands;

use Exception;
use Illuminate\Console\Command;
use Markings\Traits\HandlesEnvironments;
use Illuminate\Support\Facades\Storage;
use Markings\Actions\Api;
use Markings\Actions\FindClassFromPathAction;
use Markings\Actions\GetFilesInGlobPatternAction;
use Markings\Actions\PopoFieldFinderAction;
use Markings\Exceptions\FilesNotFoundException;
use ReflectionClass;

class SyncEventsCommand extends Command
{
    use HandlesEnvironments;

    public $signature = 'markings:sync-events';

    public $description = 'Sync all your events to Markings';

    public array $events = [];

    public function handle(): int
    {
        $this->comment('Event Sync started..');

        try {
            $success = collect(config('markings.events_paths'))
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
                ->values()
                ->pipe(function ($types) {
                    if (! $this->handleEnvironment()) {
                        throw new Exception('Sync failed!');
                    }

                    return $types;
                })
                ->pipe(function ($events) {
                    $this->comment('Syncing to server..');

                    $result = Api::syncEvents($events);

                    if ($result->failed()) {
                        $this->error('There was an unexpected error when calling the server. Error message:');
                        $this->error($result->body());
                        $this->error('Sync failed!');

                        return false;
                    }

                    $this->comment('Sync successful!');

                    Storage::put('markings-events.json', json_encode($this->events));

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

    protected function parseFile(ReflectionClass $class)
    {
        [$columns, $skippedTypes] = PopoFieldFinderAction::make()->handle($class);

        if ($class->isAbstract()) {
            return false;
        }

        if (! empty($skippedTypes)) {
            $types = collect($skippedTypes)
                ->map(fn ($type, $name) => "$name: $type")
                ->implode(', ');

            $this->comment('Unknown types found. skipping: '.$types);
        }

        $this->events[] = $class->getName();

        return [
            'name' => $class->getShortName(),
            'types' => $columns,
        ];
    }
}
