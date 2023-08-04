<?php

namespace Src\Traits;

use Markings\Actions\Api;

trait HandlesEnvironments
{
    protected function handleEnvironment(): bool
    {
        $environments = Api::getEnvironments();

        if (! $environments->contains(fn ($environment) => $environment->name == config('markings.environment'))) {

            if ($this->ask('The environment "'.config('markings.environment').'" does not exist. Would you like to create it?')) {
                $copyFrom = $this->ask('Would you like to copy from another environment?')
                    ? $this->choose('Which environment would you like to copy from?', $environments->map(fn ($e) => $e->name)->toArray())
                    : null;

                try {
                    Api::createEnvironment(config('markings.environment'), $copyFrom);
                } catch (\Exception $e) {
                    $this->error('There was an error when trying to create the envirinment. Error message:');
                    $this->error($e->getMessage());
                    $this->error('Sync failed!');

                    return false;
                }

                $this->comment('Environment created successfully!');

                if ($copyFrom) {
                    $this->comment('Environment copied from "'.$copyFrom.'"');
                }
            } else {
                $this->comment('Sync failed!');

                return false;
            }
        }

        return true;
    }
}
