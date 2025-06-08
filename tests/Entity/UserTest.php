<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\Collection;

class UserTest extends TestCase
{
    public function testUsernameAndIdentifier()
    {
        $user = new User();
        $this->assertInstanceOf(User::class, $user->setUsername('alice'));
        $this->assertSame('alice', $user->getUsername());
        $this->assertSame('alice', $user->getUserIdentifier());
    }

    public function testEmail()
    {
        $user = new User();
        $this->assertInstanceOf(User::class, $user->setEmail('alice@example.com'));
        $this->assertSame('alice@example.com', $user->getEmail());
    }

    public function testPassword()
    {
        $user = new User();
        $this->assertInstanceOf(User::class, $user->setPassword('secret'));
        $this->assertSame('secret', $user->getPassword());
    }

    public function testAvatarAndBio()
    {
        $user = new User();
        $this->assertInstanceOf(User::class, $user->setAvatar('avatar.png'));
        $this->assertSame('avatar.png', $user->getAvatar());
        $this->assertInstanceOf(User::class, $user->setBio('Hello world'));
        $this->assertSame('Hello world', $user->getBio());
    }

    public function testTimestamps()
    {
        $user = new User();
        $created = $user->getCreatedAt();
        $updated = $user->getUpdatedAt();
        $this->assertInstanceOf(\DateTimeInterface::class, $created);
        $this->assertInstanceOf(\DateTimeInterface::class, $updated);

        $newDate = new \DateTime('2000-01-01');
        $user->setCreatedAt($newDate);
        $user->setUpdatedAt($newDate);
        $this->assertSame($newDate, $user->getCreatedAt());
        $this->assertSame($newDate, $user->getUpdatedAt());
    }

    public function testVerificationAndRoles()
    {
        $user = new User();
        $this->assertFalse($user->isVerified());
        $user->setIsVerified(true);
        $this->assertTrue($user->isVerified());

        $roles = $user->getRoles();
        $this->assertIsArray($roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testEraseCredentials()
    {
        $user = new User();
        $user->eraseCredentials();
        $this->assertTrue(true);
    }

    public function testCollectionsAreEmptyByDefault()
    {
        $user = new User();
        $this->assertInstanceOf(Collection::class, $user->getPosts());
        $this->assertInstanceOf(Collection::class, $user->getComments());
        $this->assertInstanceOf(Collection::class, $user->getLikes());
        $this->assertInstanceOf(Collection::class, $user->getFollowers());
        $this->assertInstanceOf(Collection::class, $user->getFollowing());

        $this->assertCount(0, $user->getPosts());
        $this->assertCount(0, $user->getComments());
        $this->assertCount(0, $user->getLikes());
        $this->assertCount(0, $user->getFollowers());
        $this->assertCount(0, $user->getFollowing());
    }
}
