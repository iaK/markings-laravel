<?php

namespace Markings\Markings\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Markings\Markings\Actions\Api;
use Markings\Markings\Actions\PpoFieldFinderAction;
use Markings\Markings\Actions\FindClassFromPathAction;
use Markings\Markings\Exceptions\FilesNotFoundException;
use Markings\Markings\Actions\GetFilesInGlobPatternAction;

class SyncEventsCommand extends Command
{
    public $signature = 'markings:sync-events';

    public $description = 'Sync all your events to Template Genius';

    public array $events = [];

    public function handle(): int
    {
        $this->comment('Event Sync started..');

        try {
            $success = collect(config('markings.events_paths'))
                ->map(fn ($path) => GetFilesInGlobPatternAction::make()->handle($path))
                ->flatten()
                ->map(function ($file) {
                    $this->comment("Parsing file: $file");
    
                    return $this->parseFile($file);
                })
                ->filter()
                ->values()
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

    protected function parseFile($file)
    {
        $class = FindClassFromPathAction::make()->handle($file);
        [$columns, $skippedTypes] = PpoFieldFinderAction::make()->handle($class);

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
