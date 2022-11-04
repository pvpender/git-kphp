<?php

use pvpender\GitPhp\GitException;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../libs/AssertRunner.php';

Tester\Environment::setup();


/**
 * @throws GitException
 */
function test(string $cb)
{
	try {
		$cb();

	} catch (pvpender\GitPhp\GitException $e) {
		$result = $e->getRunnerResult();

		if ($result !== NULL) {
			echo $result->getCommand(), "\n";
			echo 'EXIT CODE: ', $result->getExitCode(), "\n";
			echo "--------------\n",
				$result->getOutputAsString(), "\n";

			if ($result->hasErrorOutput()) {
				echo "--------------\n",
					implode("\n", $result->getErrorOutput()), "\n";
			}
		}

		throw $e;
	}
}
