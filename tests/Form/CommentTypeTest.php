<?php

namespace App\Tests\Form;

use App\Entity\Comment;
use App\Form\CommentType;
use Symfony\Component\Form\Test\TypeTestCase;

class CommentTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'content' => 'Este es un comentario de prueba',
        ];

        $model = new Comment();
        $form  = $this->factory->create(CommentType::class, $model);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertSame('Este es un comentario de prueba', $model->getContent());

        $this->assertCount(1, $form->all());
    }
}
