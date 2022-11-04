<?php

use Tester\Assert;
use pvpender\GitPhp\Git;
use pvpender\GitPhp\Tests\AssertRunner;

require __DIR__ . '/bootstrap.php';

$runner = new AssertRunner(__DIR__);
$git = new Git($runner);

$runner->assert(
	['log', '--pretty=format:%H', '-n', '1'],
	[],
	['734713bc047d87bf7eac9674765ae793478c50d3']
);

$repo = $git->open(__DIR__);
Assert::same('734713bc047d87bf7eac9674765ae793478c50d3', $repo->getLastCommitId()->toString());
