<?php

namespace pvpender\GitKphp\Runners;

use pvpender\GitKphp\CommandProcessor;
use pvpender\GitKphp\GitException;
use pvpender\GitKphp\InvalidStateException;
use pvpender\GitKphp\IRunner;
use pvpender\GitKphp\RunnerResult;
use pvpender\GitKphp\Systemc;


class CliRunner implements IRunner
{
    private string $gitBinary;

    private Systemc $ffi;

    private CommandProcessor $commandProcessor;


    public function __construct(string $gitBinary = 'git')
    {
        $this->gitBinary = $gitBinary;
        $this->commandProcessor = new CommandProcessor;
        $this->ffi = new Systemc();
    }


    /**
     * @param ?string[] $env
     * @throws GitException
     * @throws InvalidStateException
     */
    public function run(string $cwd, array $args, $env = NULL): RunnerResult
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
        /*$process = proc_open($command, $descriptorspec, $pipes, $cwd, $env, [
            'bypass_shell' => TRUE,
        ]);*/
        $this->ffi->system("cd $cwd");
        $this->ffi->system("$command");

        /*if (!$process) {
            throw new GitException("Executing of command '$command' failed (directory $cwd).");
        }

        // Reset output and error
        stream_set_blocking($pipes[1], FALSE);
        stream_set_blocking($pipes[2], FALSE);*/
        $stdout = '';
        $stderr = '';

        /*while (TRUE) {
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

        $returnCode = \proc_close($process);*/
        return new RunnerResult($command, 0, $this->convertOutput($stdout), $this->convertOutput($stderr));
    }


    /**
     * @throws InvalidStateException
     */
    public function getCwd(): string
    {
        $cwd = "";
        $cwd = __DIR__;

        if (!is_string($cwd)) {
            throw new \pvpender\GitPhp\InvalidStateException('Getting of CWD failed.');
        }

        return $cwd;
    }


    /**
     * @return string[]
     */
    protected function convertOutput(string $output)
    {
        $output = str_replace(["\r\n", "\r"], "\n", $output);
        $output = rtrim($output, "\n");

        if ($output === '') {
            return [];
        }
        return explode("\n", $output);
    }
}
