<?php

	namespace pvpender\GitPhp;


	class GitRepository
	{
		/** @var  string */
		protected $repository;

		/** @var IRunner */
		protected $runner;


		/**
		 * @param  string $repository
		 * @throws GitException
		 */
		public function __construct($repository, IRunner $runner = NULL)
		{
			if (basename($repository) === '.git') {
				$repository = dirname($repository);
			}

			$path = realpath($repository);

			if ($path === FALSE) {
				throw new GitException("Repository '$repository' not found.");
			}

			$this->repository = $path;
			$this->runner = $runner !== NULL ? $runner : new Runners\CliRunner;
		}


		/**
		 * @return string
		 */
		public function getRepositoryPath()
		{
			return $this->repository;
		}


        /**
         * Creates a tag.
         * `git tag <name>`
         * @param  string $name
         * @param  ?mixed[] $options
         * @return static
         * @throws InvalidStateException
         * @throws GitException
         */
		public function createTag($name, $options = NULL)
		{
			$this->run('tag', $options, '--end-of-options', $name);
			return $this;
		}


        /**
         * Removes tag.
         * `git tag -d <name>`
         * @param  string $name
         * @return static
         * @throws InvalidStateException
         * @throws GitException
         */
		public function removeTag($name)
		{
			$this->run('tag', [
				'-d' => $name,
			]);
			return $this;
		}


        /**
         * Renames tag.
         * `git tag <new> <old>`
         * `git tag -d <old>`
         * @param  string $oldName
         * @param  string $newName
         * @return static
         * @throws InvalidStateException
         * @throws GitException
         */
		public function renameTag($oldName, $newName)
		{
			// http://stackoverflow.com/a/1873932
			// create new as alias to old (`git tag NEW OLD`)
			$this->run('tag', '--end-of-options', $newName, $oldName);
			// delete old (`git tag -d OLD`)
			$this->removeTag($oldName);
			return $this;
		}


        /**
         * Returns list of tags in repo.
         * @return ?string[] NULL => no tags
         * @throws GitException
         * @throws InvalidStateException
         */
		public function getTags()
		{
			return $this->extractFromCommand(['tag'], 'trim');
		}


        /**
         * Merges branches.
         * `git merge <options> <name>`
         * @param  string $branch
         * @param  ?mixed[] $options
         * @return static
         * @throws InvalidStateException
         * @throws GitException
         */
		public function merge($branch, $options = NULL)
		{
			$this->run('merge', $options, '--end-of-options', $branch);
			return $this;
		}


        /**
         * Creates new branch.
         * `git branch <name>`
         * (optionaly) `git checkout <name>`
         * @param  string $name
         * @param  bool $checkout
         * @return static
         * @throws InvalidArgumentException
         * @throws GitException
         * @throws InvalidStateException
         */
		public function createBranch($name, $checkout = FALSE)
		{
			// git branch $name
			$this->run('branch', '--end-of-options', $name);

			if ($checkout) {
				$this->checkout($name);
			}

			return $this;
		}


        /**
         * Removes branch.
         * `git branch -d <name>`
         * @param  string $name
         * @return static
         * @throws InvalidStateException
         * @throws GitException
         */
		public function removeBranch($name)
		{
			$this->run('branch', [
				'-d' => $name,
			]);
			return $this;
		}



		/**
		 * Gets name of current branch
		 * `git branch` + magic
		 * @return string
		 * @throws GitException
		 */
		public function getCurrentBranchName()
		{
			try {
				$branch = $this->extractFromCommand(['branch', '-a', '--no-color'], function($value) {
					if (isset($value[0]) && $value[0] === '*') {
						return trim(substr($value, 1));
					}

					return FALSE;
				});

				if (is_array($branch)) {
					return $branch[0];
				}

			} catch (GitException $e) {
				// nothing
			} catch (InvalidStateException $e) {
            }

            throw new GitException('Getting of current branch name failed.');
		}


        /**
         * Returns list of all (local & remote) branches in repo.
         * @return ?string[]  NULL => no branches
         * @throws GitException
         * @throws InvalidStateException
         */
		public function getBranches()
		{
			return $this->extractFromCommand(['branch', '-a', '--no-color'], function($value) {
				return trim(substr($value, 1));
			});
		}


        /**
         * Returns list of remote branches in repo.
         * @return ?string[]  NULL => no branches
         * @throws GitException
         * @throws InvalidStateException
         */
		public function getRemoteBranches()
		{
			return $this->extractFromCommand(['branch', '-r', '--no-color'], function($value) {
				return trim(substr($value, 1));
			});
		}


        /**
         * Returns list of local branches in repo.
         * @return ?string[]  NULL => no branches
         * @throws GitException
         * @throws InvalidStateException
         */
		public function getLocalBranches()
		{
			return $this->extractFromCommand(['branch', '--no-color'], function($value) {
				return trim(substr($value, 1));
			});
		}


        /**
         * Checkout branch.
         * `git checkout <branch>`
         * @param  string $name
         * @return static
         * @throws InvalidArgumentException
         * @throws GitException
         * @throws InvalidStateException
         */
		public function checkout($name)
		{
			if (!is_string($name)) {
				throw new InvalidArgumentException('Branch name must be string.');
			}

			if ($name === '') {
				throw new InvalidArgumentException('Branch name cannot be empty.');
			}

			if ($name[0] === '-') {
				throw new InvalidArgumentException('Branch name cannot be option name.');
			}

			$this->run('checkout', $name);
			return $this;
		}


        /**
         * Removes file(s).
         * `git rm <file>`
         * @param  string|string[] $file
         * @return static
         * @throws InvalidStateException
         * @throws GitException
         */
		public function removeFile($file)
		{
			if (!is_array($file)) {
				$file = func_get_args();
			}

			foreach ($file as $item) {
				$this->run('rm', '-r', '--end-of-options', $item);
			}

			return $this;
		}


        /**
         * Adds file(s).
         * `git add <file>`
         * @param  string|string[] $file
         * @return static
         * @throws InvalidStateException
         * @throws GitException
         */
		public function addFile($file)
		{
			if (!is_array($file)) {
				$file = func_get_args();
			}

			foreach ($file as $item) {
				// make sure the given item exists
				// this can be a file or an directory, git supports both
				$path = Helpers::isAbsolute($item) ? $item : ($this->getRepositoryPath() . DIRECTORY_SEPARATOR . $item);

				if (!file_exists($path)) {
					throw new GitException("The path at '$item' does not represent a valid file.");
				}

				$this->run('add', '--end-of-options', $item);
			}

			return $this;
		}


        /**
         * Adds all created, modified & removed files.
         * `git add --all`
         * @return static
         * @throws InvalidStateException
         * @throws GitException
         */
		public function addAllChanges()
		{
			$this->run('add', '--all');
			return $this;
		}


        /**
         * Renames file(s).
         * `git mv <file>`
         * @param  string|string[] $file from: array('from' => 'to', ...) || (from, to)
         * @param  ?string $to
         * @return static
         * @throws InvalidStateException
         * @throws GitException
         */
		public function renameFile($file, $to = NULL)
		{
			if (!is_array($file)) { // rename(file, to);
				$file = [
					$file => $to,
				];
			}

			foreach ($file as $from => $to) {
				$this->run('mv', '--end-of-options', $from, $to);
			}

			return $this;
		}


        /**
         * Commits changes
         * `git commit <params> -m <message>`
         * @param  string $message
         * @param  ?mixed[] $options
         * @return static
         * @throws InvalidStateException
         * @throws GitException
         */
		public function commit($message, $options = NULL)
		{
			$this->run('commit', $options, [
				'-m' => $message,
			]);
			return $this;
		}


        /**
         * Returns last commit ID on current branch
         * `git log --pretty=format:"%H" -n 1`
         * @return CommitId
         * @throws GitException
         * @throws InvalidArgumentException
         * @throws InvalidStateException
         */
		public function getLastCommitId()
		{
			$result = $this->run('log', '--pretty=format:%H', '-n', '1');
			$lastLine = $result->getOutputLastLine();
			return new CommitId((string) $lastLine);
		}


        /**
         * @return Commit
         * @throws GitException
         * @throws InvalidArgumentException
         * @throws InvalidStateException
         */
		public function getLastCommit()
		{
			return $this->getCommit($this->getLastCommitId());
		}


        /**
         * @param  string|CommitId $commitId
         * @return Commit
         * @throws InvalidArgumentException
         * @throws GitException
         * @throws InvalidStateException
         */
		public function getCommit($commitId)
		{
			if (!($commitId instanceof CommitId)) {
				$commitId = new CommitId($commitId);
			}

			// subject
			$result = $this->run('log', '-1', $commitId, '--format=%s');
			$subject = rtrim($result->getOutputAsString());

			// body
			$result = $this->run('log', '-1', $commitId, '--format=%b');
			$body = rtrim($result->getOutputAsString());

			// author email
			$result = $this->run('log', '-1', $commitId, '--format=%ae');
			$authorEmail = rtrim($result->getOutputAsString());

			// author name
			$result = $this->run('log', '-1', $commitId, '--format=%an');
			$authorName = rtrim($result->getOutputAsString());

			// author date
			$result = $this->run('log', '-1', $commitId, '--pretty=format:%ad', '--date=iso-strict');
			$authorDate = \DateTimeImmutable::createFromFormat(\DateTime::ATOM, (string) $result->getOutputLastLine());

			if (!($authorDate instanceof \DateTimeImmutable)) {
				throw new GitException('Failed fetching of commit author date.', 0, NULL, $result);
			}

			// committer email
			$result = $this->run('log', '-1', $commitId, '--format=%ce');
			$committerEmail = rtrim($result->getOutputAsString());

			// committer name
			$result = $this->run('log', '-1', $commitId, '--format=%cn');
			$committerName = rtrim($result->getOutputAsString());

			// committer date
			$result = $this->run('log', '-1', $commitId, '--pretty=format:%cd', '--date=iso-strict');
			$committerDate = \DateTimeImmutable::createFromFormat(\DateTime::ATOM, (string) $result->getOutputLastLine());

			if (!($committerDate instanceof \DateTimeImmutable)) {
				throw new GitException('Failed fetching of commit committer date.', 0, NULL, $result);
			}

			return new Commit(
				$commitId,
				$subject,
				$body !== '' ? $body : NULL,
				$authorEmail,
				$authorName !== '' ? $authorName : NULL,
				$authorDate,
				$committerEmail,
				$committerName !== '' ? $committerName : NULL,
				$committerDate
			);
		}


        /**
         * Exists changes?
         * `git status` + magic
         * @return bool
         * @throws GitException
         * @throws InvalidStateException
         */
		public function hasChanges()
		{
			// Make sure the `git status` gets a refreshed look at the working tree.
			$this->run('update-index', '-q', '--refresh');
			$result = $this->run('status', '--porcelain');
			return $result->hasOutput();
		}


        /**
         * Pull changes from a remote
         * @param  string|string[]|null $remote
         * @param  ?mixed[] $options
         * @return static
         * @throws GitException
         * @throws InvalidStateException
         */
		public function pull($remote = NULL, array $options = NULL)
		{
			$this->run('pull', $options, '--end-of-options', $remote);
			return $this;
		}


        /**
         * Push changes to a remote
         * @param  string|string[]|null $remote
         * @param  ?mixed[] $options
         * @return static
         * @throws GitException
         * @throws InvalidStateException
         */
		public function push($remote = NULL, array $options = NULL)
		{
			$this->run('push', $options, '--end-of-options', $remote);
			return $this;
		}


        /**
         * Run fetch command to get latest branches
         * @param  string|string[]|null $remote
         * @param  ?mixed[] $options
         * @return static
         * @throws GitException
         * @throws InvalidStateException
         */
		public function fetch($remote = NULL, array $options = NULL)
		{
			$this->run('fetch', $options, '--end-of-options', $remote);
			return $this;
		}


        /**
         * Adds new remote repository
         * @param  string $name
         * @param  string $url
         * @param  ?mixed[] $options
         * @return static
         * @throws GitException
         * @throws InvalidStateException
         */
		public function addRemote($name, $url, array $options = NULL)
		{
			$this->run('remote', 'add', $options, '--end-of-options', $name, $url);
			return $this;
		}


        /**
         * Renames remote repository
         * @param  string $oldName
         * @param  string $newName
         * @return static
         * @throws GitException
         * @throws InvalidStateException
         */
		public function renameRemote($oldName, $newName)
		{
			$this->run('remote', 'rename', '--end-of-options', $oldName, $newName);
			return $this;
		}


        /**
         * Removes remote repository
         * @param  string $name
         * @return static
         * @throws GitException
         * @throws InvalidStateException
         */
		public function removeRemote($name)
		{
			$this->run('remote', 'remove', '--end-of-options', $name);
			return $this;
		}


        /**
         * Changes remote repository URL
         * @param  string $name
         * @param  string $url
         * @param  ?mixed[] $options
         * @return static
         * @throws GitException
         * @throws InvalidStateException
         */
		public function setRemoteUrl($name, $url, array $options = NULL)
		{
			$this->run('remote', 'set-url', $options, '--end-of-options', $name, $url);
			return $this;
		}


        /**
         * @param  mixed ...$cmd
         * @return string[]  returns output
         * @throws GitException
         * @throws InvalidStateException
         */
		public function execute(...$cmd)
		{
			$result = $this->run(...$cmd);
			return $result->getOutput();
		}


        /**
         * @param  mixed[] $args
         * @return ?string[]
         * @throws GitException
         * @throws InvalidStateException
         */
		protected function extractFromCommand(array $args, callable $filter = NULL)
		{
			$result = $this->run(...$args);
			$output = $result->getOutput();

			if ($filter !== NULL) {
				$newArray = [];

				foreach ($output as $line) {
					$value = $filter($line);

					if ($value === FALSE) {
						continue;
					}

					$newArray[] = (string) $value;
				}

				$output = $newArray;
			}

			if (empty($output)) {
				return null;
			}

			return $output;
		}


        /**
         * Runs command.
         * @param  mixed ...$args
         * @return RunnerResult
         * @throws GitException
         * @throws InvalidStateException
         */
		protected function run(...$args)
		{
			$result = $this->runner->run($this->repository, $args);

			if (!$result->isOk()) {
				throw new GitException("Command '{$result->getCommand()}' failed (exit-code 
				{$result->getExitCode()}).", $result->getExitCode(), NULL, $result);
			}

			return $result;
		}
	}
