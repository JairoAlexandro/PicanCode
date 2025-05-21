<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{TextType,EmailType,ChoiceType,SubmitType};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('username', TextType::class, ['label'=>'Usuario'])
          ->add('email',    EmailType::class, ['label'=>'Email'])
          ->add('roles', ChoiceType::class, [
              'label'    => 'Roles',
              'choices'  => ['Usuario'=>'ROLE_USER','Admin'=>'ROLE_ADMIN'],
              'multiple' => true,
              'expanded' => true,
          ])
          ->add('save', SubmitType::class, ['label'=>'Guardar cambios']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class'=>User::class]);
    }
}
