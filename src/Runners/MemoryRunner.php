<?php

	namespace pvpender\GitPhp\Runners;

	use pvpender\GitPhp\CommandProcessor;
	use pvpender\GitPhp\GitException;
    use pvpender\GitPhp\InvalidStateException;
    use pvpender\GitPhp\IRunner;
	use pvpender\GitPhp\RunnerResult;


	class MemoryRunner implements IRunner
	{
		/** @var string */
		private $cwd;

		/** @var CommandProcessor */
		private $commandProcessor;

		/** @var tuple(string, RunnerResult)  [command => RunnerResult] */
		private $results = [];


		/**
		 * @param  string $cwd
		 */
		public function __construct($cwd)
		{
			$this->cwd = $cwd;
			$this->commandProcessor = new CommandProcessor;
		}


        /**
         * @param  mixed[] $args
         * @param  tuple(string, string) $env
         * @param  string[] $output
         * @param  string[] $errorOutput
         * @param  int $exitCode
         * @return self
         * @throws InvalidStateException
         */
		public function setResult(array $args, array $env, array $output, array $errorOutput = [], $exitCode = 0)
		{
			$cmd = $this->commandProcessor->process('git', $args, $env);
			$this->results[$cmd] = new RunnerResult($cmd, $exitCode, $output, $errorOutput);
			return $this;
		}


        /**
         * @param  mixed[] $args
         * @param  ?tuple(string, string) $env
         * @return RunnerResult
         * @throws InvalidStateException
         */
		public function run($cwd, array $args, array $env = NULL)
		{
			$cmd = $this->commandProcessor->process('git', $args, $env);

			if (!isset($this->results[$cmd])) {
				throw new \pvpender\GitPhp\InvalidStateException("Missing result for command '$cmd'.");
			}

			return $this->results[$cmd];
		}


		/**
		 * @return string
		 */
		public function getCwd()
		{
			return $this->cwd;
		}
	}
