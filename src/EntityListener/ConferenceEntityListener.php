<?php declare(strict_types = 1);

namespace App\EntityListener;

use App\Entity\Conference;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Class ConferenceEntityListener
 * @package App\EntityListener
 */
class ConferenceEntityListener
{
    /**
     * @var SluggerInterface
     */
    private $_slugger;

    /**
     * ConferenceEntityListener constructor.
     *
     * @param SluggerInterface $slugger
     */
    public function __construct(SluggerInterface $slugger)
    {
        $this->_slugger = $slugger;
    }

    /**
     * @param Conference         $conference
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Conference $conference, LifecycleEventArgs $event)
    {
        $conference->computeSlug($this->_slugger);
    }

    /**
     * @param Conference         $conference
     * @param LifecycleEventArgs $event
     */
    public function preUpdate(Conference $conference, LifecycleEventArgs $event)
    {
        $conference->computeSlug($this->_slugger);
    }
}
