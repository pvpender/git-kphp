<?php

namespace pvpender\GitPhp;


interface IRunner
{
    /**
     * @param  string[] $args
     * @param ?string[] $env
     */
    function run(string $cwd, array $args, $env = NULL): RunnerResult;


    function getCwd(): string;
}
