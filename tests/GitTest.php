<?php

use pvpender\GitKphp\CommitId;
use PHPUnit\Framework\TestCase;
use pvpender\GitKphp\GitException;
use pvpender\GitKphp\Helpers;
use pvpender\GitKphp\Runners\MemoryRunner;
use pvpender\GitKphp\Git;

class GitTest extends TestCase{

    private string $TESTPATH = "./git-kphp-pvpender-test-repo/";

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
        $this->assertSame('git-php', Helpers::extractRepositoryNameFromUrl('git@github.com:pvpender/git-kphp.git'));
    }

    /**
     * @throws GitException
     */
    public function testGitRepoDir(){
        \pvpender\GitKphp\Systemc::load();
        $runner = new MemoryRunner(__DIR__);
        $git = new Git($runner);
        $repoA = $git->open(__DIR__);
        $this->assertSame(__DIR__, $repoA->getRepositoryPath());
        $repoA = $git->open(__DIR__ . '/.git');
        $this->assertSame(__DIR__, $repoA->getRepositoryPath());
        $repoA = $git->open(__DIR__ . '/.git/');
        $this->assertSame(__DIR__, $repoA->getRepositoryPath());
    }

    public function testRepoInit(){
        \pvpender\GitKphp\Systemc::load();
        $runner = new \pvpender\GitKphp\Runners\CliRunner(__DIR__);
        $git = new Git($runner);
        $s = new \pvpender\GitKphp\Systemc();
        $s->system("mkdir ".$this->TESTPATH);
        $repo = $git->init(__DIR__.$this->TESTPATH);
    }

}

