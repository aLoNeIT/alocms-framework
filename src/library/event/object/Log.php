<?php

declare(strict_types=1);

namespace alocms\event\object;

class Log
{
    public function __construct($args)
    {
        dump([
            'args' => $args
        ]);
    }

    public function handle($args)
    {
        dump([
            'handle_args' => $args
        ]);
    }
}
