<?php

namespace App\Tests\Entity;

use App\Entity\Post;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\Collection;

class PostTest extends TestCase
{
    public function testTitleAndContent()
    {
        $post = new Post();
        $this->assertInstanceOf(Post::class, $post->setTitle('Mi título'));
        $this->assertSame('Mi título', $post->getTitle());

        $this->assertInstanceOf(Post::class, $post->setContent('Contenido del post'));
        $this->assertSame('Contenido del post', $post->getContent());
    }

    public function testMedia()
    {
        $post = new Post();
        $this->assertNull($post->getMedia());

        $this->assertInstanceOf(Post::class, $post->setMedia('imagen.jpg'));
        $this->assertSame('imagen.jpg', $post->getMedia());

        $this->assertInstanceOf(Post::class, $post->setMedia(null));
        $this->assertNull($post->getMedia());
    }

    public function testUserAssociation()
    {
        $post = new Post();
        $user = new User();
        $user->setUsername('bob')->setEmail('bob@example.com')->setPassword('x');
        $this->assertInstanceOf(Post::class, $post->setUser($user));
        $this->assertSame($user, $post->getUser());
    }

    public function testTimestamps()
    {
        $post = new Post();
        $created = $post->getCreatedAt();
        $updated = $post->getUpdatedAt();
        $this->assertInstanceOf(\DateTimeInterface::class, $created);
        $this->assertInstanceOf(\DateTimeInterface::class, $updated);

        $fecha = new \DateTime('2020-01-01');
        $post->setCreatedAt($fecha);
        $post->setUpdatedAt($fecha);
        $this->assertSame($fecha, $post->getCreatedAt());
        $this->assertSame($fecha, $post->getUpdatedAt());
    }

    public function testPublishedFlag()
    {
        $post = new Post();
        $this->assertFalse($post->getIsPublished());
        $this->assertInstanceOf(Post::class, $post->setIsPublished(true));
        $this->assertTrue($post->getIsPublished());
    }

    public function testCollectionsEmptyByDefault()
    {
        $post = new Post();
        $this->assertInstanceOf(Collection::class, $post->getComments());
        $this->assertInstanceOf(Collection::class, $post->getLikes());

        $this->assertCount(0, $post->getComments());
        $this->assertCount(0, $post->getLikes());
    }
}
