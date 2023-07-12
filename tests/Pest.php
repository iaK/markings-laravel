<?php

use Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function getField($field, $fields)
{
    return collect($fields)->first(fn ($f) => $f['name'] === $field);
}
