<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\{Conference, Comment, Admin};
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AppFixtures extends Fixture
{
    /**
     * @var EncoderFactoryInterface
     */
    private $_encoderFactory;

    /**
     * AppFixtures constructor.
     *
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->_encoderFactory = $encoderFactory;
    }

    public function load(ObjectManager $manager)
    {
        // Add conferences

        $spb = new Conference();
        $spb->setCity('Saint-Petersburg');
        $spb->setYear('2020');
        $spb->setIsInternational(true);
        $manager->persist($spb);

        $paris = new Conference();
        $paris->setCity('Paris');
        $paris->setYear('2020');
        $paris->setIsInternational(false);
        $manager->persist($paris);

        $msk = new Conference();
        $msk->setCity('Moscow');
        $msk->setYear('2020');
        $msk->setIsInternational(true);
        $manager->persist($msk);

        // Add comments

        $comment1 = new Comment();
        $comment1->setConference($msk);
        $comment1->setAuthor('Korolev Vladimir');
        $comment1->setEmail('koroloff@list.ru');
        $comment1->setText('Test comment message');
        $comment1->setState('published');
        $manager->persist($comment1);

        $comment2 = new Comment();
        $comment2->setConference($spb);
        $comment2->setAuthor('Korolev2 Vladimir2');
        $comment2->setEmail('koroloff@list.ru');
        $comment2->setText('Test comment message 2');
        $comment2->setState('published');
        $manager->persist($comment2);

        // Add users

        $admin = new Admin();
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setUsername('admin');
        $admin->setPassword($this->_encoderFactory->getEncoder(Admin::class)->encodePassword('admin', null));
        $manager->persist($admin);

        $manager->flush();
    }
}
