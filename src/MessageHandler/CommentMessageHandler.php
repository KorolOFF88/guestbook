<?php declare(strict_types = 1);

namespace App\MessageHandler;

use App\SpamChecker;
use Psr\Log\LoggerInterface;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Messenger\MessageBusInterface;
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
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var WorkflowInterface
     */
    private $workflow;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var string
     */
    private $adminEmail;

    /**
     * CommentMessageHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SpamChecker            $spamChecker
     * @param CommentRepository      $commentRepository
     * @param MessageBusInterface    $bus
     * @param WorkflowInterface      $commentStateMachine
     * @param MailerInterface        $mailer
     * @param string                 $adminEmail
     * @param LoggerInterface|null   $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SpamChecker $spamChecker,
        CommentRepository $commentRepository,
        MessageBusInterface $bus,
        WorkflowInterface $commentStateMachine,
        MailerInterface $mailer,
        string $adminEmail,
        LoggerInterface $logger = null
    ) {
        $this->spamChecker       = $spamChecker;
        $this->entityManager     = $entityManager;
        $this->commentRepository = $commentRepository;

        $this->bus      = $bus;
        $this->workflow = $commentStateMachine;
        $this->logger   = $logger;

        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
    }

    /**
     * @param CommentMessage $message
     *
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
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

        if ($this->workflow->can($comment, 'accept')) {
            $score = $this->spamChecker->getSpamScore($comment, $message->getContext());

            $transition = 'accept';
            if (2 === $score) {
                $transition = 'reject_spam';
            } elseif (1 === $score) {
                $transition = 'might_be_spam';
            }

            $this->workflow->apply($comment, $transition);
            $this->entityManager->flush();

            $this->bus->dispatch($message);
        }
        elseif ($this->workflow->can($comment, 'publish') || $this->workflow->can($comment, 'publish_ham')) {
            $this->mailer->send(
                (new NotificationEmail())->subject('New comment posted')
                                         ->htmlTemplate('emails/comment_notification.html.twig')
                                         ->from($this->adminEmail)
                                         ->to($this->adminEmail)
                                         ->context(['comment' => $comment])
            );
        }
        elseif ($this->logger) {
            $this->logger->debug('Dropping comment message', [
                'comment' => $comment->getId(),
                'state'   => $comment->getState(),
            ]);
        }
    }
}
