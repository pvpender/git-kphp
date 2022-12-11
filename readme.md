Git-KPHP
=======

[![Total Downloads](http://poser.pugx.org/pvpender/git-kphp/downloads)](https://packagist.org/packages/pvpender/git-kphp)
[![PHP Version Require](http://poser.pugx.org/pvpender/git-kphp/require/php)](https://packagist.org/packages/pvpender/git-kphp)

FFI Library for work with Git repository in KPHP.

[Download a latest package](https://github.com/pvpender/git-kphp/releases) or use [Composer](http://getcomposer.org/):

```
composer require pvpender/git-kphp
```

Library requires PHP 7.4, latest version of [KPHP](https://github.com/VKCOM/kphp) and `git` client (path to Git must be
in system variable `PATH`). Also, if you want use commands, which requires to log in git, you should tune authentication 
without password using private/public keys or setting `-global` parameter.

**Warning!** This is FFI lib. That's mean that some C cod using to normal working.
Before proc_open will not support, this library use FFI, that can be very unsafe. Don't use this library if safety is very important to you.


Before starting
---
This is FFI lib and it means that you should preload `.c` files before starting working the library.
Don't worry everything already in `Systemc` class, you only should write:

```php
use pvpender\GitPhp\Systemc;
Systemc::load();
```
At the top of your `main.php` file. 

Using
---
```php
use pvpender\GitPhp\Git;
use pvpender\GitPhp\Systemc;

Systemc::load();
$git = new pvpender\GitPhp\Git;
// create repo object
$repo = $git->open(__DIR__);

// create a new file in repo
$filename = $repo->getRepositoryPath() . '/newfile.txt';
file_put_contents($filename, "Hello world!");

// commit
$repo->addFile($filename);
$repo->commit('init commit');
```
Initialization of empty repository
----------------------------------

``` php
$repo = $git->init('/path/to/repo-directory');
```

With parameters:

``` php
$repo = $git->init('/path/to/repo-directory', [
	'--bare', // creates bare repo
]);
```

Cloning of repository
---------------------
``` php
// Cloning of repository into subdirectory 'git-php' in current working directory
$repo = $git->cloneRepository('https://github.com/czproject/git-php.git');
// Cloning of repository into own directory
$repo = $git->cloneRepository('https://github.com/czproject/git-php.git', '/path/to/my/subdir');
```


Basic operations
----------------

``` php
$repo->commit('commit message');
$repo->merge('branch-name');
$repo->checkout('master');
$repo->getRepositoryPath();
// adds files into commit
$repo->addFile('file.txt');
$repo->addFile('file1.txt', 'file2.txt');
$repo->addFile(['file3.txt', 'file4.txt']);
// renames files in repository
$repo->renameFile('old.txt', 'new.txt');
$repo->renameFile([
    'old1.txt' => 'new1.txt',
    'old2.txt' => 'new2.txt',
]);
// removes files from repository
$repo->removeFile('file.txt');
$repo->removeFile('file1.txt', 'file2.txt');
$repo->removeFile(['file3.txt', 'file4.txt']);
// adds all changes in repository
$repo->addAllChanges();
```
Branches
--------

``` php
// gets list of all repository branches (remotes & locals)
$repo->getBranches();
// gets list of all local branches
$repo->getLocalBranches();
// gets name of current branch
$repo->getCurrentBranchName();
// creates new branch
$repo->createBranch('new-branch');
// creates new branch and checkout
$repo->createBranch('patch-1', TRUE);
// removes branch
$repo->removeBranch('branch-name');
```


Tags
----

``` php
// gets list of all tags in repository
$repo->getTags();
// creates new tag
$repo->createTag('v1.0.0');
$repo->createTag('v1.0.0', $options);
$repo->createTag('v1.0.0', [
	'-m' => 'message',
]);
// renames tag
$repo->renameTag('old-tag-name', 'new-tag-name');
// removes tag
$repo->removeTag('tag-name');
```


History
-------

This functions working by reading files in `.git/`

``` php
// returns last commit ID on current branch
$commitId = $repo->getLastCommitId();
$commitId->getId(); // or (string) $commitId
// returns commit data
$commit = $repo->getCommit('734713bc047d87bf7eac9674765ae793478c50d3');
$commit->getId(); // instance of CommitId
$commit->getSubject();
$commit->getBody();
$commit->getAuthorName();
$commit->getAuthorEmail();
$commit->getAuthorDate();
$commit->getCommitterName();
$commit->getCommitterEmail();
$commit->getCommitterDate();
$commit->getDate();
// returns commit data of last commit on current branch
$commit = $repo->getLastCommit();
```


Remotes
-------

``` php
// pulls changes from remote
$repo->pull('remote-name', ['--options']);
$repo->pull('origin');
// pushs changes to remote
$repo->push('remote-name', ['--options']);
$repo->push('origin');
$repo->push(['origin', 'master'], ['-u']);
// fetchs changes from remote
$repo->fetch('remote-name', ['--options']);
$repo->fetch('origin');
$repo->fetch(['origin', 'master']);
// adds remote repository
$repo->addRemote('remote-name', 'repository-url', ['--options']);
$repo->addRemote('origin', 'git@github.com:pvpender/git-kphp.git');
// renames remote
$repo->renameRemote('old-remote-name', 'new-remote-name');
$repo->renameRemote('origin', 'upstream');
// removes remote
$repo->removeRemote('remote-name');
$repo->removeRemote('origin');
// changes remote URL
$repo->setRemoteUrl('remote-name', 'new-repository-url');
$repo->setRemoteUrl('upstream', 'https://github.com/pvpender/git-kphp.git');
```

Other commands
--------------

For running other commands you can use `execute` method:
**Warning!** In current version you **CAN'T** get some outputs from console.

```php
$output = $repo->execute('command');
$output = $repo->execute('command', 'with', 'parameters');
// example:
$repo->execute('remote', 'set-branches', $originName, $branches);
```

Based on https://github.com/czproject/git-php
