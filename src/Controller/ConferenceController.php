<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ConferenceRepository;
use Twig\Environment;

class ConferenceController extends AbstractController
{
    /**
     * @var Environment $twig
     */
    private $_twig;

    /**
     * ConferenceController constructor.
     *
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->_twig = $twig;
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
        return new Response($this->_twig->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
        ]));
    }

    /**
     * @Route("/conference/{slug}", name="conference")
     *
     * @param Request           $request
     * @param Conference        $conference
     * @param CommentRepository $commentRepository
     *
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function show(Request $request, Conference $conference, CommentRepository $commentRepository): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);

        return new Response($this->_twig->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments'   => $paginator,
            'prev'       => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next'       => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
        ]));
    }
}
