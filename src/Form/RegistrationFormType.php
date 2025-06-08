<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType; 
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label'       => 'Name:',
                'attr'        => ['placeholder'=>'Enter your name'],
            ])
            ->add('email', EmailType::class, [
                'label'       => 'Email:',
                'attr'        => ['placeholder'=>'Enter your email'],
            ])
            ->add('avatar', FileType::class, [
                'label'       => 'Avatar (PNG/JPG)',
                'mapped'      => false,    
                'required'    => false,
            ])
            ->add('bio', TextareaType::class, [
                'label'       => 'Bio:',
                'required'    => false,
                'attr'        => ['placeholder'=>'Tell us something about you'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'mapped'          => false,
                'first_options'   => [
                    'label' => 'Password:',
                    'attr'  => ['placeholder'=>'Enter your password'],
                ],
                'second_options'  => [
                    'label' => 'Repeat password:',
                    'attr'  => ['placeholder'=>'Enter your password'],
                ],
                'invalid_message' => 'Las contraseñas deben coincidir.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

