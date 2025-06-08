<?php

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Component\Form\Test\TypeTestCase;

class RegistrationFormTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'username'      => 'juanperez',
            'email'         => 'juan@example.com',
            'bio'           => 'Una pequeña biografía',
            'plainPassword' => [
                'first'  => 'secreto123',
                'second' => 'secreto123',
            ],
          
        ];

        $model = new User();
        $form  = $this->factory->create(RegistrationFormType::class, $model);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertSame('juanperez',            $model->getUsername());
        $this->assertSame('juan@example.com',      $model->getEmail());
        $this->assertSame('Una pequeña biografía', $model->getBio());

        $this->assertCount(5, $form->all());

        $this->assertFalse($form->get('avatar')->getConfig()->getMapped());
        $this->assertFalse($form->get('plainPassword')->getConfig()->getMapped());
    }
}
