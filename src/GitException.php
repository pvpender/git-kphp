<?php

namespace pvpender\GitPhp;

class GitException extends \Exception
	{
		/** @var ?RunnerResult */
		private $runnerResult;


		/**
		 * @param string $message
		 * @param int $code
		 */
		public function __construct($message, $code = 0, \Throwable $previous = NULL, RunnerResult $runnerResult = NULL)
		{
			parent::__construct($message, $code, $previous);
			$this->runnerResult = $runnerResult;
		}


		/**
		 * @return ?RunnerResult
		 */
		public function getRunnerResult()
		{
			return $this->runnerResult;
		}
	}
