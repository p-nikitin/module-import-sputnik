<?php

namespace Izifir\Sputnik;

class Agent
{
    public static function run()
    {
        Import::run();

        return '\Izifir\Sputnik\Agent::run();';
    }
}