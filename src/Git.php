<?php

	namespace pvpender\GitPhp;


	class Git
	{
        protected IRunner $runner;


		public function  __construct(IRunner $runner = NULL)
		{
			$this->runner = $runner !== NULL ? $runner : new Runners\CliRunner;
		}


        /**
         * @throws GitException
         */
		public function open(string $directory): GitRepository
        {
			return new GitRepository($directory, $this->runner);
		}


        /**
         * Init repo in directory
         * @param  ?mixed[] $params
         * @throws GitException
         * @throws InvalidStateException
         */
		public function init(string $directory, array $params = NULL): GitRepository
        {
			if (is_dir("$directory/.git")) {
				throw new GitException("Repo already exists in $directory.");
			}

			if (!is_dir($directory) && !@mkdir($directory, 0777, TRUE)) { // intentionally @; not atomic; from Nette FW
				throw new GitException("Unable to create directory '$directory'.");
			}

			try {
				$this->run($directory, [
					'init',
					$params,
					'--end-of-options',
					$directory
				]);

			} catch (GitException $e) {
				throw new GitException("Git init failed (directory $directory).", $e->getCode(), $e);
			} catch (InvalidStateException $e) {
                throw new InvalidStateException("Invalid state", $e->getCode(), $e);
            }

            return $this->open($directory);
		}


        /**
         * Clones GIT repository from $url into $directory
         * @param  ?mixed[] $params
         * @throws GitException
         * @throws InvalidStateException
         */
		public function cloneRepository(string $url, ?string $directory = NULL, array $params = NULL): GitRepository
        {
			if ($directory !== NULL && is_dir("$directory/.git")) {
				throw new GitException("Repo already exists in $directory.");
			}

			$cwd = $this->runner->getCwd();

			if ($directory === NULL) {
				$directory = Helpers::extractRepositoryNameFromUrl($url);
				$directory = "$cwd/$directory";

			} elseif(!Helpers::isAbsolute($directory)) {
				$directory = "$cwd/$directory";
			}

			if ($params === NULL) {
				$params = '-q';
			}

			try {
				$this->run($cwd, [
					'clone',
					$params,
					'--end-of-options',
					$url,
					$directory
				]);

			} catch (GitException $e) {
				$stderr = '';
				$result = $e->getRunnerResult();

				if ($result !== NULL && $result->hasErrorOutput()) {
					$stderr = implode(PHP_EOL, $result->getErrorOutput());
				}

				throw new GitException("Git clone failed (directory $directory)." . ($stderr !== '' ? ("\n$stderr") : ''));
			}

			return $this->open($directory);
		}


        /**
         * @param  ?string[] $refs
         * @throws GitException
         * @throws InvalidStateException
         */
		public function isRemoteUrlReadable(string $url, array $refs = NULL): bool
        {
			$result = $this->runner->run($this->runner->getCwd(), [
				'ls-remote',
				'--heads',
				'--quiet',
				'--exit-code',
				'--end-of-options',
				$url,
				$refs,
			], [
				'GIT_TERMINAL_PROMPT' => 0,
			]);

			return $result->isOk();
		}


        /**
         * @param  mixed[] $args
         * @param  tuple(string, string) $env
         * @throws GitException
         * @throws InvalidStateException
         */
		private function run(string $cwd, array $args, array $env = NULL): RunnerResult
        {
			$result = $this->runner->run($cwd, $args, $env);

			if (!$result->isOk()) {
				throw new GitException("Command '{$result->getCommand()}' failed (exit-code 
				{$result->getExitCode()}).", $result->getExitCode(), NULL, $result);
			}

			return $result;
		}
	}
