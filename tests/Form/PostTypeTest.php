<?php

namespace App\Tests\Form;

use App\Entity\Post;
use App\Form\PostType;
use Symfony\Component\Form\Test\TypeTestCase;

class PostTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'title'   => 'Título de prueba',
            'content' => 'Contenido de prueba',
        ];

        $model = new Post();
        $form  = $this->factory->create(PostType::class, $model);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertSame('Título de prueba', $model->getTitle());
        $this->assertSame('Contenido de prueba', $model->getContent());

        $this->assertCount(3, $form->all());

        $this->assertFalse($form->get('media')->getConfig()->getMapped());
    }
}
