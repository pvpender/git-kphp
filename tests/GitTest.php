<?php

use pvpender\GitPhp\CommitId;
use PHPUnit\Framework\TestCase;
use pvpender\GitPhp\GitException;
use pvpender\GitPhp\Helpers;
use pvpender\GitPhp\Runners\MemoryRunner;
use pvpender\GitPhp\Git;

class GitTest extends TestCase{
    public function testCommitID(){
        $commitId = new CommitId('734713bc047d87bf7eac9674765ae793478c50d3');
        $this->assertSame('734713bc047d87bf7eac9674765ae793478c50d3', (string) $commitId);
        $this->assertSame('734713bc047d87bf7eac9674765ae793478c50d3', $commitId->toString());
    }
    public function testBranch(){

    }
    public function testExtractRepoNameFromUrl(){
        $this->assertSame('repo', Helpers::extractRepositoryNameFromUrl('/path/to/repo.git'));
        $this->assertSame('repo', Helpers::extractRepositoryNameFromUrl('/path/to/repo/.git'));
        $this->assertSame('foo', Helpers::extractRepositoryNameFromUrl('host.xz:foo/.git'));
        $this->assertSame('repo', Helpers::extractRepositoryNameFromUrl('file:///path/to/repo.git/'));
        $this->assertSame('git-kphp', Helpers::extractRepositoryNameFromUrl('https://github.com/pvpender/git-kphp.git'));
        $this->assertSame('git-php', Helpers::extractRepositoryNameFromUrl('git@github.com:czproject/git-php.git'));
    }

    /**
     * @throws GitException
     */
    public function testGitRepoDir(){
        /*$runner = new MemoryRunner(__DIR__);
        $git = new Git($runner);
        $repoA = $git->open(__DIR__);
        $this.self::assertSame(__DIR__, $repoA->getRepositoryPath());
        $repoA = $git->open(__DIR__ . '/.git');
        $this.self::assertSame(__DIR__, $repoA->getRepositoryPath());
        $repoA = $git->open(__DIR__ . '/.git/');
        $this.self::assertSame(__DIR__, $repoA->getRepositoryPath());*/
    }
}
