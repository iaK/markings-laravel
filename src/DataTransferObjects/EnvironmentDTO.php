<?php

namespace Markings\DataTransferObjects;

class EnvironmentDTO
{
    public function __construct(
        public string $name,
        public bool $main,
        public bool $locked
    ) {

    }
}
