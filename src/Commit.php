<?php

	namespace pvpender\GitPhp;


	class Commit
	{
		/** @var CommitId */
		private $id;

		/** @var string */
		private $subject;

		/** @var ?string */
		private $body;

		/** @var string */
		private $authorEmail;

		/** @var ?string */
		private $authorName;

		/** @var \DateTimeImmutable */
		private $authorDate;

		/** @var string */
		private $committerEmail;

		/** @var ?string */
		private $committerName;

		/** @var \DateTimeImmutable */
		private $committerDate;


		/**
		 * @param string $subject
		 * @param ?string $body
		 * @param string $authorEmail
		 * @param ?string $authorName
		 * @param string $committerEmail
		 * @param ?string $committerName
		 */
		public function __construct(
			CommitId $id,
			$subject,
			$body,
			$authorEmail,
			$authorName,
			\DateTimeImmutable $authorDate,
			$committerEmail,
			$committerName,
			\DateTimeImmutable $committerDate
		)
		{
			$this->id = $id;
			$this->subject = $subject;
			$this->body = $body;
			$this->authorEmail = $authorEmail;
			$this->authorName = $authorName;
			$this->authorDate = $authorDate;
			$this->committerEmail = $committerEmail;
			$this->committerName = $committerName;
			$this->committerDate = $committerDate;
		}


		/**
		 * @return CommitId
		 */
		public function getId()
		{
			return $this->id;
		}


		/**
		 * @return string
		 */
		public function getSubject()
		{
			return $this->subject;
		}


		/**
		 * @return ?string
		 */
		public function getBody()
		{
			return $this->body;
		}


		/**
		 * @return ?string
		 */
		public function getAuthorName()
		{
			return $this->authorName;
		}


		/**
		 * @return string
		 */
		public function getAuthorEmail()
		{
			return $this->authorEmail;
		}


		/**
		 * @return \DateTimeImmutable
		 */
		public function getAuthorDate()
		{
			return $this->authorDate;
		}


		/**
		 * @return ?string
		 */
		public function getCommitterName()
		{
			return $this->committerName;
		}


		/**
		 * @return string
		 */
		public function getCommitterEmail()
		{
			return $this->committerEmail;
		}


		/**
		 * @return \DateTimeImmutable
		 */
		public function getCommitterDate()
		{
			return $this->committerDate;
		}


		/**
		 * Alias for getAuthorDate()
		 * @return \DateTimeImmutable
		 */
		public function getDate()
		{
			return $this->authorDate;
		}
	}
