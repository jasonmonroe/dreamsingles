<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('roles', ChoiceType::class,
                ['choices' =>
                    ['User' => 'ROLE_USER', 'Admin' => 'ROLE_ADMIN'],
                    'attr' => ['class' => 'form-control']
                ])
            ->add('password', TextType::class, ['attr' => ['class' => 'form-control']] )
        ;

        $builder->get('roles')->addModelTransformer(new CallbackTransformer(
            function($rolesArray) {
                return count($rolesArray) ? $rolesArray[0] : null;
            },
            function($rolesString){
                return [$rolesString];
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
