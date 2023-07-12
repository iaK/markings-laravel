<?php

namespace Markings\Markings\Commands;

use Illuminate\Console\Command;

class SyncAllCommand extends Command
{
    public $signature = 'markings:sync';

    public $description = 'Sync both Types and Events';

    public function handle(): int
    {
        $this->call('markings:sync-types');
        $this->call('markings:sync-events');

        return self::SUCCESS;
    }
}
