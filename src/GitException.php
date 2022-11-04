<?php

namespace pvpender\GitPhp;

class GitException extends \Exception
	{
    private ?RunnerResult $runnerResult;


    public function __construct(string $message, int $code = 0, \Throwable $previous = NULL,
                                RunnerResult $runnerResult = NULL)
		{
			parent::__construct($message, $code, $previous);
			$this->runnerResult = $runnerResult;
		}


    public function getRunnerResult(): ?RunnerResult
    {
			return $this->runnerResult;
		}
	}
