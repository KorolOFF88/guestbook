<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use App\Repository\ConferenceRepository;
use Twig\Environment;

class TwigEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Environment
     */
    private $_twig;

    /**
     * @var ConferenceRepository
     */
    private $_conferenceRepository;

    /**
     * TwigEventSubscriber constructor.
     *
     * @param Environment          $twig
     * @param ConferenceRepository $conferenceRepository
     */
    public function __construct(Environment $twig, ConferenceRepository $conferenceRepository)
    {
        $this->_conferenceRepository = $conferenceRepository;
        $this->_twig                 = $twig;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $this->_twig->addGlobal('conferences', $this->_conferenceRepository->findAll());
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}
