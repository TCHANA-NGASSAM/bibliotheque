<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRolesEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $user = $event->getData();
            if (!$user instanceof User) {
                return;
            }
            $event->getForm()->add('assignableRoles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Bibliothécaire' => 'ROLE_LIBRARIAN',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'data' => $user->getStoredRoles(),
                'required' => false,
            ]);
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            $user = $event->getData();
            if (!$user instanceof User) {
                return;
            }
            $form = $event->getForm();
            $roles = $form->has('assignableRoles') ? $form->get('assignableRoles')->getData() : [];
            $user->setRoles(\is_array($roles) ? array_values($roles) : []);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
