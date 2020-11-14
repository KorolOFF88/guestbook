<?php declare(strict_types = 1);

namespace App\MessageHandler;

use App\SpamChecker;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class CommentMessageHandler
 * @package App\MessageHandler
 */
class CommentMessageHandler implements MessageHandlerInterface
{
    /**
     * @var SpamChecker
     */
    private $spamChecker;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CommentRepository
     */
    private $commentRepository;

    /**
     * CommentMessageHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SpamChecker            $spamChecker
     * @param CommentRepository      $commentRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SpamChecker $spamChecker,
        CommentRepository $commentRepository
    ) {
        $this->spamChecker       = $spamChecker;
        $this->entityManager     = $entityManager;
        $this->commentRepository = $commentRepository;
    }

    /**
     * @param CommentMessage $message
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function __invoke(CommentMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());
        if (! $comment) {
            return;
        }

        $comment->setText($comment->getText() . ' [after message handling]');

        if (2 === $this->spamChecker->getSpamScore($comment, $message->getContext())) {
            $comment->setState('spam');
        } else {
            $comment->setState('published');
        }

        $this->entityManager->flush();
    }
}
