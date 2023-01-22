<?php

use pvpender\GitKphp\CommitId;
use PHPUnit\Framework\TestCase;
use pvpender\GitKphp\GitException;
use pvpender\GitKphp\Helpers;
use pvpender\GitKphp\Runners\MemoryRunner;
use pvpender\GitKphp\Git;
use pvpender\GitKphp\Commit;

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
        $this->assertSame($this->TESTPATH, $repo->getRepositoryPath());
        $s->system("rm -r".$this->TESTPATH);
    }

    public function testCloning(){
        \pvpender\GitKphp\Systemc::load();
        $git = new Git();
        $s = new \pvpender\GitKphp\Systemc();
        $repo = $git->cloneRepository("https://github.com/pvpender/git-kphp");
        $this->assertSame($repo->getCurrentBranchName(), "main");
        $s->system("rm -r ./git-kphp");
    }

    public function testBasic(){
        \pvpender\GitKphp\Systemc::load();
        $runner = new \pvpender\GitKphp\Runners\CliRunner(__DIR__);
        $git = new Git($runner);
        $s = new \pvpender\GitKphp\Systemc();
        $s->system("mkdir ".$this->TESTPATH);
        $repo = $git->init(__DIR__.$this->TESTPATH);
        $repo->createBranch("test", true);
        $this->assertSame($repo->getLocalBranches(), ["main", "test"]);
        $this->assertSame($repo->getCurrentBranchName(), "test");
        $repo->addFile("file.txt", "file2.txt");
        $repo->renameFile(["file.txt" => "new.txt", "file2.txt" => "new2.txt"]);
        $repo->addAllChanges();
        $repo->commit("test");
        $s->system("rm -r ".$this->TESTPATH);
    }

    public function testTag(){
        \pvpender\GitKphp\Systemc::load();
        $runner = new \pvpender\GitKphp\Runners\CliRunner(__DIR__);
        $git = new Git($runner);
        $s = new \pvpender\GitKphp\Systemc();
        $s->system("mkdir ".$this->TESTPATH);
        $repo = $git->init(__DIR__.$this->TESTPATH);
        $repo->createTag('v1.0.0');
        $repo->renameTag("v1.0.0", "v1.1.1");
        $this->assertSame($repo->getTags(), ["v1.1.1"]);
        $repo->createTag("v2.1.1");
        $repo->removeTag("v1.1.1");
        $this->assertSame($repo->getTags(), ["V2.1.1"]);
        $s->system("rm -r ".$this->TESTPATH);
    }

    public function testHistory(){
        \pvpender\GitKphp\Systemc::load();
        $git = new Git();
        $s = new \pvpender\GitKphp\Systemc();
        $repo = $git->cloneRepository("https://github.com/pvpender/git-kphp");
        $commit = $repo->getCommit('734713bc047d87bf7eac9674765ae793478c50d3');
        $commit->getId();
        $commit->getSubject();
        $commit->getBody();
        $commit->getAuthorName();
        $commit->getAuthorEmail();
        $commit->getAuthorDate();
        $commit->getCommitterName();
        $commit->getCommitterEmail();
        $commit->getCommitterDate();
        $commit->getDate();
        $commit = $repo->getLastCommit();
        $s->system("rm -r ./git-kphp");
    }
}

