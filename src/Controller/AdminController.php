<?php declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Comment;
use App\Message\CommentMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;
use Twig\Environment;

class AdminController extends AbstractController
{
    /**
     * @var MessageBusInterface
     */
    private $bus;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * AdminController constructor.
     *
     * @param Environment            $twig
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface    $bus
     */
    public function __construct(Environment $twig, EntityManagerInterface $entityManager, MessageBusInterface $bus) {
        $this->bus           = $bus;
        $this->twig          = $twig;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/admin/comment/review/{id}", name="review_comment")
     *
     * @param Request  $request
     * @param Comment  $comment
     * @param Registry $registry
     *
     * @return Response
     */
    public function reviewComment(Request $request, Comment $comment, Registry $registry): Response {
        $accepted = ! $request->query->get('reject');

        $machine = $registry->get($comment);
        if ($machine->can($comment, 'publish')) {
            $transition = $accepted ? 'publish' : 'reject';
        } elseif ($machine->can($comment, 'publish_ham')) {
            $transition = $accepted ? 'publish_ham' : 'reject_ham';
        } else {
            return new Response('Comment already reviewed or not in the right state.');
        }

        $machine->apply($comment, $transition);
        $this->entityManager->flush();

        if ($accepted) {
            $this->bus->dispatch(new CommentMessage($comment->getId()));
        }

        return $this->render('admin/review.html.twig', [
            'transition' => $transition,
            'comment' => $comment,
        ]);
    }
}
