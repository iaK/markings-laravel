<?php

namespace Markings\Traits;

use Exception;
use Markings\Actions\Api;

trait HandlesEnvironments
{
    protected function handleEnvironment(): bool
    {
        $environments = Api::getEnvironments();

        if (! $environments->contains(fn ($environment) => $environment->name == config('markings.environment'))) {

            if ($this->confirm('The environment "'.config('markings.environment').'" does not exist. Would you like to create it?', true)) {
                $copyFrom = $this->confirm('Would you like to copy from another environment?', true)
                    ? $this->choice('Which environment would you like to copy from?', $environments->map(fn ($e) => $e->name.($e->main ? ' (main)' : ''))->toArray())
                    : '';

                $copyFrom = str($copyFrom)->replace(' (main)', '')->trim()->__toString();

                try {
                    Api::createEnvironment(config('markings.environment'), $copyFrom ?: null);
                } catch (\Exception $e) {
                    $this->error('There was an error when trying to create the envirinment. Error message:');
                    $this->error($e->getMessage());
                    $this->error('Sync failed!');

                    return false;
                }

                $this->info('Environment created successfully!');

                if ($copyFrom) {
                    $this->info('Environment copied from "'.$copyFrom.'"');
                }
            } else {
                throw new \Exception('Sync failed!');
            }
        } else {
            if ($environments->where('name', config('markings.environment'))->first()->locked) {
                throw new Exception('The environment "'.config('markings.environment').'" is locked. Please unlock it in the Markings UI.');
            }
        }

        return true;
    }
}
