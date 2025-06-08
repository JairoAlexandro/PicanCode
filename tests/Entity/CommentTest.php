<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    public function testDefaultCreatedAt()
    {
        $c = new Comment();
        $this->assertInstanceOf(\DateTimeInterface::class, $c->getCreatedAt(), 'createdAt se inicializa en el constructor');
    }

    public function testPostAssociation()
    {
        $c = new Comment();
        $p = new Post();
        $this->assertInstanceOf(Comment::class, $c->setPost($p));
        $this->assertSame($p, $c->getPost());
    }

    public function testUserAssociation()
    {
        $c = new Comment();
        $u = new User();
        $u->setUsername('ana')->setEmail('ana@example.com')->setPassword('123');
        $this->assertInstanceOf(Comment::class, $c->setUser($u));
        $this->assertSame($u, $c->getUser());
    }

    public function testContentGetterSetter()
    {
        $c = new Comment();
        $texto = '¡Un comentario!';
        $this->assertInstanceOf(Comment::class, $c->setContent($texto));
        $this->assertSame($texto, $c->getContent());
    }

    public function testCreatedAtSetter()
    {
        $c = new Comment();
        $fecha = new \DateTime('2021-05-05');
        $this->assertInstanceOf(Comment::class, $c->setCreatedAt($fecha));
        $this->assertSame($fecha, $c->getCreatedAt());
    }
}
