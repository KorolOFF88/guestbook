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
     * @Route("/", name="homepage")
     *
     * @param Environment          $twig
     * @param ConferenceRepository $conferenceRepository
     *
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function index(Environment $twig, ConferenceRepository $conferenceRepository): Response
    {
        return new Response($twig->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
        ]));
    }

    /**
     * @Route("/conference/{id}", name="conference")
     *
     * @param Request           $request
     * @param Environment       $twig
     * @param Conference        $conference
     * @param CommentRepository $commentRepository
     *
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function show(
        Request $request,
        Environment $twig,
        Conference $conference,
        CommentRepository $commentRepository
    ): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);

        return new Response($twig->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments'   => $paginator,
            'prev'       => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next'       => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
        ]));
    }
}
