<?php

namespace pvpender\GitKphp;


class RunnerResult
{
    private string $command;

    private int $exitCode;

    /** @var string[] */
    private $output;

    /** @var string[] */
    private $errorOutput;


    /**
     * @param  string[] $output
     * @param  string[] $errorOutput
     */
    public function __construct(string $command, int $exitCode, array $output, array $errorOutput)
    {
        $this->command = $command;
        $this->exitCode = $exitCode;
        $this->output = $output;
        $this->errorOutput = $errorOutput;
    }


    public function isOk(): bool
    {
        return $this->exitCode === 0;
    }


    public function getCommand(): string
    {
        return $this->command;
    }


    public function getExitCode(): int
    {
        return $this->exitCode;
    }


    /**
     * @return string[]
     */
    public function getOutput()
    {
        return $this->output;
    }


    /**
     * @return string
     */
    public function getOutputAsString()
    {
        return implode("\n", $this->output);
    }


    public function getOutputLastLine(): ?string
    {
        $lastLine = array_last_value($this->output);
        return is_string($lastLine) ? $lastLine : NULL;
    }


    public function hasOutput(): bool
    {
        return !empty($this->output);
    }


    /**
     * @return string[]
     */
    public function getErrorOutput()
    {
        return $this->errorOutput;
    }


    public function hasErrorOutput(): bool
    {
        return !empty($this->errorOutput);
    }


    public function toText(): string
    {
        return '$ ' . $this->getCommand() . "\n\n"
            . "---- STDOUT: \n\n"
            . implode("\n", $this->getOutput()) . "\n\n"
            . "---- STDERR: \n\n"
            . implode("\n", $this->getErrorOutput()) . "\n\n"
            . '=> ' . $this->getExitCode() . "\n\n";
    }
}
