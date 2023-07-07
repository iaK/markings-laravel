<?php

namespace TemplateGenius\TemplateGenius\Commands;

use Illuminate\Console\Command;

class SyncAllCommand extends Command
{
    public $signature = 'template-genius:sync';

    public $description = 'Sync both Types and Events';

    public function handle(): int
    {
        $this->call('template-genius:sync-types');
        $this->call('template-genius:sync-events');

        return self::SUCCESS;
    }
}
