<?php

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\ProfileEditType;
use Symfony\Component\Form\Test\TypeTestCase;

class ProfileEditTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'bio' => 'Esta es mi nueva biografía',
        ];

        $model = new User();
        $form  = $this->factory->create(ProfileEditType::class, $model);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertSame('Esta es mi nueva biografía', $model->getBio());

        $this->assertCount(2, $form->all());

        $this->assertFalse($form->get('avatar')->getConfig()->getMapped());
    }
}
