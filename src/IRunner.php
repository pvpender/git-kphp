<?php

	namespace pvpender\GitPhp;


	interface IRunner
	{
		/**
		 * @param  string $cwd
		 * @param  mixed[] $args
         * @param ?tuple(string, string) $env
		 * @return RunnerResult
		 */
		function run($cwd, array $args, array $env = NULL);


		/**
		 * @return string
		 */
		function getCwd();
	}
