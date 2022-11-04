<?php

	namespace pvpender\GitPhp\Runners;

	use pvpender\GitPhp\CommandProcessor;
	use pvpender\GitPhp\GitException;
    use pvpender\GitPhp\InvalidStateException;
    use pvpender\GitPhp\IRunner;
	use pvpender\GitPhp\RunnerResult;


	class MemoryRunner implements IRunner
	{
        private string $cwd;

        private CommandProcessor $commandProcessor;

		/** @var tuple(string, RunnerResult)  [command => RunnerResult] */
		private $results = [];


        public function __construct(string $cwd)
		{
			$this->cwd = $cwd;
			$this->commandProcessor = new CommandProcessor;
		}


        /**
         * @param  mixed[] $args
         * @param  tuple(string, string) $env
         * @param  string[] $output
         * @param  string[] $errorOutput
         * @throws InvalidStateException
         */
		public function setResult(array $args, array $env, array $output, array $errorOutput = [], int $exitCode = 0): MemoryRunner
        {
			$cmd = $this->commandProcessor->process('git', $args, $env);
			$this->results[$cmd] = new RunnerResult($cmd, $exitCode, $output, $errorOutput);
			return $this;
		}


        /**
         * @param  mixed[] $args
         * @param  ?tuple(string, string) $env
         * @throws InvalidStateException
         */
		public function run($cwd, array $args, array $env = NULL): RunnerResult
        {
			$cmd = $this->commandProcessor->process('git', $args, $env);

			if (!isset($this->results[$cmd])) {
				throw new \pvpender\GitPhp\InvalidStateException("Missing result for command '$cmd'.");
			}

			return $this->results[$cmd];
		}


        public function getCwd(): string
        {
			return $this->cwd;
		}
	}
