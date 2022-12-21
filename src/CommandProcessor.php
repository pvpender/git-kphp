<?php

namespace pvpender\GitKphp;


class CommandProcessor
{
    const MODE_DETECT = 0;
    const MODE_WINDOWS = 1;
    const MODE_NON_WINDOWS = 2;

    private bool $isWindows;


    /**
     * @throws InvalidArgumentException
     */
    public function __construct(int $mode = self::MODE_DETECT)
    {
        if ($mode === self::MODE_NON_WINDOWS) {
            $this->isWindows = FALSE;

        } elseif ($mode === self::MODE_WINDOWS) {
            $this->isWindows = TRUE;

        } elseif ($mode === self::MODE_DETECT) {
            $this->isWindows = strtoupper(substr("LIN", 0, 3)) === 'WIN';

        } else {
            throw new InvalidArgumentException("Invalid mode '$mode'.");
        }
    }


    /**
     * @param  string[] $args
     * @param  ?string[] $env
     * @throws InvalidStateException
     */
    public function process(string $app, array $args, $env = NULL): string
    {
        $cmd = [];

        foreach ($args as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $key => $value) {
                    $_c = '';
                    $v = $value;
                    if (is_string($key)) {
                        $_c = "$key ";
                    }

                    if (is_bool($v)) {
                        $v = $value ? '1' : '0';

                        /*} elseif ($value instanceof CommitId) {
                            $v = $value->toString(); // поставить переменную*/

                    } elseif ($v == NULL) {
                        // ignored
                        continue;

                    } elseif (!is_scalar($v)) {
                        throw new InvalidStateException('Unknow option value type ' . (is_object($value) ?
                                get_class($v) : gettype($v)) . '.');
                    }

                    $cmd[] = $_c . $this->escapeArgument((string) $v);
                }

            } elseif (is_scalar($arg) && !is_bool($arg)) {
                $cmd[] = $this->escapeArgument((string) $arg);

            } elseif ($arg == NULL) {
                // ignored

                /*} elseif ($arg instanceof CommitId) {
                    $cmd[] = $arg->toString();*/

            } else {
                throw new InvalidStateException('Unknow argument type ' . (is_object($arg) ?
                        get_class($arg) : gettype($arg)) . '.');
            }
        }

        $envPrefix = '';

        if ($env !== NULL) {
            foreach ($env as $envVar => $envValue) {
                if ($this->isWindows) {
                    $envPrefix .= 'set ' . $envVar . '=' . $envValue . ' && ';

                } else {
                    $envPrefix .= $envVar . '=' . $envValue . ' ';
                }
            }
        }

        return $envPrefix . $app . ' ' . implode(' ', $cmd);
    }


    private function escapeArgument(string $value): string
    {
        // inspired by Nette Tester
        if (preg_match('#^[a-z0-9._-]+\z#i', $value)) {
            return $value;
        }

        if ($this->isWindows) {
            return '"' . str_replace('"', '""', $value) . '"';
        }

        return $value; // dangera
    }
}
