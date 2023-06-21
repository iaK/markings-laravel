<?php

namespace TemplateGenius\TemplateGenius\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \TemplateGenius\TemplateGenius\TemplateGenius
 */
class TemplateGenius extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \TemplateGenius\TemplateGenius\TemplateGenius::class;
    }
}
