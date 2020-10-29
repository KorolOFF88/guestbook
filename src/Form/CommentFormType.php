<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface,
    Extension\Core\Type\EmailType,
    Extension\Core\Type\SubmitType,
    Extension\Core\Type\FileType
};

class CommentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('author', null, ['label' => 'Your name'])
            ->add('text')
            ->add('email', EmailType::class)
            ->add('photo', FileType::class, [
                'required' => false,
                'mapped'   => false,
                'constraints' => [
                    new Image(['maxSize' => '2048k']),
                ],
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
