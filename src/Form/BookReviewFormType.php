<?php

namespace App\Form;

use App\Entity\BookReview;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookReviewFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $stars = [];
        for ($i = 1; $i <= 5; ++$i) {
            $stars[(string) $i.' étoile'.($i > 1 ? 's' : '')] = $i;
        }

        $builder
            ->add('rating', ChoiceType::class, [
                'label' => 'Note',
                'choices' => $stars,
                'expanded' => false,
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Commentaire',
                'attr' => ['rows' => 4],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BookReview::class,
        ]);
    }
}
