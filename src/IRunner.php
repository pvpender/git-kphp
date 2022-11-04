<?php

	namespace pvpender\GitPhp;


	interface IRunner
	{
		/**
         * @param  mixed[] $args
         * @param ?tuple(string, string) $env
         */
		function run(string $cwd, array $args, array $env = NULL): RunnerResult;


        function getCwd(): string;
	}
