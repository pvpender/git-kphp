<?php

namespace pvpender\GitKphp;


class Commit
{
    private CommitId $id;

    private string $subject;

    private ?string $body;

    private string $authorEmail;

    private ?string $authorName;

    private \DateTimeImmutable $authorDate;

    private string $committerEmail;

    private ?string $committerName;

    private \DateTimeImmutable $committerDate;


    public function __construct(
        CommitId           $id,
        string             $subject,
        ?string            $body,
        string             $authorEmail,
        ?string            $authorName,
        \DateTimeImmutable $authorDate,
        string             $committerEmail,
        ?string            $committerName,
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


    public function getId(): CommitId
    {
        return $this->id;
    }


    public function getSubject(): string
    {
        return $this->subject;
    }


    public function getBody(): ?string
    {
        return $this->body;
    }


    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }


    public function getAuthorEmail(): string
    {
        return $this->authorEmail;
    }


    public function getAuthorDate(): \DateTimeImmutable
    {
        return $this->authorDate;
    }


    public function getCommitterName(): ?string
    {
        return $this->committerName;
    }


    public function getCommitterEmail(): string
    {
        return $this->committerEmail;
    }


    public function getCommitterDate(): \DateTimeImmutable
    {
        return $this->committerDate;
    }


    /**
     * Alias for getAuthorDate()
     */
    public function getDate(): \DateTimeImmutable
    {
        return $this->authorDate;
    }
}
