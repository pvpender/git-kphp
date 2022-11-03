<?php

use Tester\Assert;
use pvpender\GitPhp\Git;
use pvpender\GitPhp\GitException;
use pvpender\GitPhp\Tests\AssertRunner;

require __DIR__ . '/bootstrap.php';

$runner = new AssertRunner(__DIR__);
$git = new Git($runner);

$runner->assert(['commit', '-m', 'Commit message']);

$repo = $git->open(__DIR__);
$repo->commit('Commit message');
