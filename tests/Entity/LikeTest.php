<?php

namespace App\Tests\Entity;

use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class LikeTest extends TestCase
{
    public function testDefaults()
    {
        $like = new Like();
        $this->assertInstanceOf(\DateTimeInterface::class, $like->getCreatedAt(), 'createdAt se inicializa automáticamente');
    }

    public function testPostAssociation()
    {
        $like = new Like();
        $post = new Post();
        $post->setTitle('Título')->setContent('...');
        $this->assertInstanceOf(Like::class, $like->setPost($post));
        $this->assertSame($post, $like->getPost());
    }

    public function testUserAssociation()
    {
        $like = new Like();
        $user = new User();
        $user->setUsername('juan')->setEmail('juan@example.com')->setPassword('x');
        $this->assertInstanceOf(Like::class, $like->setUser($user));
        $this->assertSame($user, $like->getUser());
    }

    public function testCreatedAtSetter()
    {
        $like = new Like();
        $fecha = new \DateTime('2021-05-05');
        $this->assertInstanceOf(Like::class, $like->setCreatedAt($fecha));
        $this->assertSame($fecha, $like->getCreatedAt());
    }
}
