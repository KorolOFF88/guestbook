<?php

namespace App\Controller;

use App\Entity\{Conference, Comment};
use App\Repository\{CommentRepository, ConferenceRepository};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Message\CommentMessage;
use App\Form\CommentFormType;
use Twig\Environment;

class ConferenceController extends AbstractController
{
    /**
     * @var Environment $twig
     */
    private $_twig;

    /**
     * @var EntityManagerInterface
     */
    private $_entityManager;

    private $_bus;

    /**
     * ConferenceController constructor.
     *
     * @param Environment            $twig
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(Environment $twig, EntityManagerInterface $entityManager, MessageBusInterface $bus)
    {
        $this->_bus           = $bus;
        $this->_twig          = $twig;
        $this->_entityManager = $entityManager;
    }

    /**
     * @Route("/", name="homepage")
     *
     * @param ConferenceRepository $conferenceRepository
     *
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        $response = new Response($this->_twig->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
        ]));

        $response->setSharedMaxAge(3600);

        return $response;
    }

    /**
     * @Route("/conference/{slug}", name="conference")
     */

    public function show(
        Request $request,
        Conference $conference,
        CommentRepository $commentRepository,
        string $photoDir
    ): Response
    {
        $comment = new Comment();
        $form    = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $comment->setConference($conference);
            // Upload image
            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)) . '.' . $photo->guessExtension();
                try {
                    $photo->move($photoDir, $filename);
                } catch (FileException $exception) {
                    // unable to upload the photo, give up
                }
                $comment->setPhotoFilename($filename);
            }

            $this->_entityManager->persist($comment);
            $this->_entityManager->flush();

            // Check comment
            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user-agent'),
                'referrer' => $request->headers->get('referer'),
                'permalink' => $request->getUri(),
            ];

            // Send message for Spam check
            $this->_bus->dispatch(new CommentMessage($comment->getId(), $context));

            return $this->redirectToRoute('conference', [ 'slug' => $conference->getSlug() ]);
        }

        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);

        return new Response($this->_twig->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments'   => $paginator,
            'prev'       => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next'       => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'comment_form' => $form->createView(),
        ]));
    }
}
