<?php

	namespace pvpender\GitPhp;


	use DateTimeImmutable;
    use DateTimeInterface;

    class GitRepository
	{
        protected string $repository;

        protected IRunner $runner;


		/**
         * @throws GitException
		 */
		public function __construct(string $repository, IRunner $runner = NULL)
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


        public function getRepositoryPath(): string
        {
			return $this->repository;
		}


        /**
         * Creates a tag.
         * `git tag <name>`
         * @param  ?mixed[] $options
         * @throws InvalidStateException
         * @throws GitException
         */
		public function createTag(string $name, $options = NULL): GitRepository
        {
			$this->run('tag', $options, '--end-of-options', $name);
			return $this;
		}


        /**
         * Removes tag.
         * `git tag -d <name>`
         * @throws InvalidStateException
         * @throws GitException
         */
		public function removeTag(string $name): GitRepository
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
         * @throws InvalidStateException
         * @throws GitException
         */
		public function renameTag(string $oldName, string $newName): GitRepository
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
         * @param  ?mixed[] $options
         * @return static
         * @throws InvalidStateException
         * @throws GitException
         */
		public function merge(string $branch, $options = NULL)
		{
			$this->run('merge', $options, '--end-of-options', $branch);
			return $this;
		}


        /**
         * Creates new branch.
         * `git branch <name>`
         * (optionaly) `git checkout <name>`
         * @throws InvalidArgumentException
         * @throws GitException
         * @throws InvalidStateException
         */
		public function createBranch(string $name, bool $checkout = FALSE): GitRepository
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
         * @throws InvalidStateException
         * @throws GitException
         */
		public function removeBranch(string $name): GitRepository
        {
			$this->run('branch', [
				'-d' => $name,
			]);
			return $this;
		}



		/**
		 * Gets name of current branch
		 * `git branch` + magic
         * @throws GitException
		 */
		public function getCurrentBranchName(): string
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
         * @throws InvalidArgumentException
         * @throws GitException
         * @throws InvalidStateException
         */
		public function checkout(string $name): GitRepository
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
         * @throws InvalidStateException
         * @throws GitException
         */
		public function removeFile($file): GitRepository
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
         * @throws InvalidStateException
         * @throws GitException
         */
		public function addFile($file): GitRepository
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
         * @throws InvalidStateException
         * @throws GitException
         */
		public function addAllChanges(): GitRepository
        {
			$this->run('add', '--all');
			return $this;
		}


        /**
         * Renames file(s).
         * `git mv <file>`
         * @param  string|string[] $file from: array('from' => 'to', ...) || (from, to)
         * @throws InvalidStateException
         * @throws GitException
         */
		public function renameFile($file, ?string $to = NULL): GitRepository
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
         * @param  ?mixed[] $options
         * @throws InvalidStateException
         * @throws GitException
         */
		public function commit(string $message, $options = NULL): GitRepository
        {
			$this->run('commit', $options, [
				'-m' => $message,
			]);
			return $this;
		}


        /**
         * Returns last commit ID on current branch
         * `git log --pretty=format:"%H" -n 1`
         * @throws GitException
         * @throws InvalidArgumentException
         * @throws InvalidStateException
         */
		public function getLastCommitId(): CommitId
        {
			$result = $this->run('log', '--pretty=format:%H', '-n', '1');
			$lastLine = $result->getOutputLastLine();
			return new CommitId((string) $lastLine);
		}


        /**
         * @throws GitException
         * @throws InvalidArgumentException
         * @throws InvalidStateException
         */
		public function getLastCommit(): Commit
        {
			return $this->getCommit($this->getLastCommitId());
		}


        /**
         * @param  string|CommitId $commitId
         * @throws InvalidArgumentException
         * @throws GitException
         * @throws InvalidStateException
         */
		public function getCommit($commitId): Commit
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
			$authorDate = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, (string) $result->getOutputLastLine());

			if (!($authorDate instanceof DateTimeImmutable)) {
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
			$committerDate = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, (string) $result->getOutputLastLine());

			if (!($committerDate instanceof DateTimeImmutable)) {
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
         * @throws GitException
         * @throws InvalidStateException
         */
		public function hasChanges(): bool
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
         * @throws GitException
         * @throws InvalidStateException
         */
		public function pull($remote = NULL, array $options = NULL): GitRepository
        {
			$this->run('pull', $options, '--end-of-options', $remote);
			return $this;
		}


        /**
         * Push changes to a remote
         * @param  string|string[]|null $remote
         * @param  ?mixed[] $options
         * @throws GitException
         * @throws InvalidStateException
         */
		public function push($remote = NULL, array $options = NULL): GitRepository
        {
			$this->run('push', $options, '--end-of-options', $remote);
			return $this;
		}


        /**
         * Run fetch command to get latest branches
         * @param  string|string[]|null $remote
         * @param  ?mixed[] $options
         * @throws GitException
         * @throws InvalidStateException
         */
		public function fetch($remote = NULL, array $options = NULL): GitRepository
        {
			$this->run('fetch', $options, '--end-of-options', $remote);
			return $this;
		}


        /**
         * Adds new remote repository
         * @param  ?mixed[] $options
         * @throws GitException
         * @throws InvalidStateException
         */
		public function addRemote(string $name, string $url, array $options = NULL): GitRepository
        {
			$this->run('remote', 'add', $options, '--end-of-options', $name, $url);
			return $this;
		}


        /**
         * Renames remote repository
         * @throws GitException
         * @throws InvalidStateException
         */
		public function renameRemote(string $oldName, string $newName): GitRepository
        {
			$this->run('remote', 'rename', '--end-of-options', $oldName, $newName);
			return $this;
		}


        /**
         * Removes remote repository
         * @throws GitException
         * @throws InvalidStateException
         */
		public function removeRemote(string $name): GitRepository
        {
			$this->run('remote', 'remove', '--end-of-options', $name);
			return $this;
		}


        /**
         * Changes remote repository URL
         * @param  ?mixed[] $options
         * @throws GitException
         * @throws InvalidStateException
         */
		public function setRemoteUrl(string $name, string $url, array $options = NULL): GitRepository
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
         * @throws GitException
         * @throws InvalidStateException
         */
		protected function run(...$args): RunnerResult
        {
			$result = $this->runner->run($this->repository, $args);

			if (!$result->isOk()) {
				throw new GitException("Command '{$result->getCommand()}' failed (exit-code 
				{$result->getExitCode()}).", $result->getExitCode(), NULL, $result);
			}

			return $result;
		}
	}
