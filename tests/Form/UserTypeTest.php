<?php

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\Form\Test\TypeTestCase;

class UserTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'username' => 'juanperez',
            'email'    => 'juan@example.com',
        ];

        $model = new User();

        $form = $this->factory->create(UserType::class, $model);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertSame('juanperez', $model->getUsername());
        $this->assertSame('juan@example.com', $model->getEmail());

        $this->assertCount(2, $form->all());
    }
}
