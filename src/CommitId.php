<?php

namespace pvpender\GitKphp;

class CommitId
{
    private string $id;


    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $id)
    {
        if (!self::isValid($id)) {
            throw new InvalidArgumentException("Invalid commit ID" . (is_string($id) ?
                    " '$id'." : ', expected string, ' . gettype($id) . ' given.'));
        }

        $this->id = $id;
    }


    public function toString(): string
    {
        return $this->id;
    }


    public function __toString(): string
    {
        return $this->id;
    }


    public static function isValid(string $id): bool
    {
        return is_string($id) && preg_match('/^[0-9a-f]{40}$/i', $id);
    }
}
