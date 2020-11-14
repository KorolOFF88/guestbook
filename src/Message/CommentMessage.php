<?php declare(strict_types = 1);

namespace App\Message;

class CommentMessage
{
    private $id;
    private $context;

    /**
     * CommentMessage constructor.
     *
     * @param $id
     * @param $context
     */
    public function __construct(int $id, array $context = [])
    {
        $this->id = $id;
        $this->context = $context;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
