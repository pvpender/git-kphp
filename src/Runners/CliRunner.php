<?php

	namespace pvpender\GitPhp\Runners;

	use pvpender\GitPhp\CommandProcessor;
	use pvpender\GitPhp\GitException;
    use pvpender\GitPhp\InvalidStateException;
    use pvpender\GitPhp\IRunner;
	use pvpender\GitPhp\RunnerResult;


	class CliRunner implements IRunner
	{
		/** @var string */
		private $gitBinary;

		/** @var CommandProcessor */
		private $commandProcessor;


		/**
		 * @param  string $gitBinary
		 */
		public function __construct($gitBinary = 'git')
		{
			$this->gitBinary = $gitBinary;
			$this->commandProcessor = new CommandProcessor;
		}


        /**
         * @return RunnerResult
         * @throws GitException
         * @throws InvalidStateException
         */
		public function run($cwd, array $args, array $env = NULL)
		{
			if (!is_dir($cwd)) {
				throw new GitException("Directory '$cwd' not found");
			}

			$descriptorspec = [
				0 => ['pipe', 'r'], // stdin
				1 => ['pipe', 'w'], // stdout
				2 => ['pipe', 'w'], // stderr
			];

			$pipes = [];
			$command = $this->commandProcessor->process($this->gitBinary, $args);
			$process = proc_open($command, $descriptorspec, $pipes, $cwd, $env, [
				'bypass_shell' => TRUE,
			]);

			if (!$process) {
				throw new GitException("Executing of command '$command' failed (directory $cwd).");
			}

			// Reset output and error
			stream_set_blocking($pipes[1], FALSE);
			stream_set_blocking($pipes[2], FALSE);
			$stdout = '';
			$stderr = '';

			while (TRUE) {
				// Read standard output
				$stdoutOutput = stream_get_contents($pipes[1]);

				if (is_string($stdoutOutput)) {
					$stdout .= $stdoutOutput;
				}

				// Read error output
				$stderrOutput = stream_get_contents($pipes[2]);

				if (is_string($stderrOutput)) {
					$stderr .= $stderrOutput;
				}

				// We are done
				if ((feof($pipes[1]) || $stdoutOutput === FALSE) && (feof($pipes[2]) || $stderrOutput === FALSE)) {
					break;
				}
			}

			$returnCode = proc_close($process);
			return new RunnerResult($command, $returnCode, $this->convertOutput($stdout), $this->convertOutput($stderr));
		}


        /**
         * @return string
         * @throws InvalidStateException
         */
		public function getCwd()
		{
			$cwd = getcwd();

			if (!is_string($cwd)) {
				throw new \pvpender\GitPhp\InvalidStateException('Getting of CWD failed.');
			}

			return $cwd;
		}


		/**
		 * @param  string $output
		 * @return string[]
		 */
		protected function convertOutput($output)
		{
			$output = str_replace(["\r\n", "\r"], "\n", $output);
			$output = rtrim($output, "\n");

			if ($output === '') {
				return [];
			}

			return explode("\n", $output);
		}
	}
