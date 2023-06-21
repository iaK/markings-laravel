<?php

namespace TemplateGenius\TemplateGenius\Commands;

use Illuminate\Console\Command;

class TemplateGeniusCommand extends Command
{
    public $signature = 'template-genius-laravel';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
