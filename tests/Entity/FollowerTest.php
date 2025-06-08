<?php

namespace App\Tests\Entity;

use App\Entity\Follower;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class FollowerTest extends TestCase
{
    public function testDefaults()
    {
        $f = new Follower();
        $this->assertInstanceOf(\DateTimeInterface::class, $f->getCreatedAt(), 'createdAt se inicializa en el constructor');
    }

    public function testFollowerAssociation()
    {
        $f = new Follower();
        $u = new User();
        $u->setUsername('juan')->setEmail('juan@example.com')->setPassword('x');
        $this->assertInstanceOf(Follower::class, $f->setFollower($u));
        $this->assertSame($u, $f->getFollower());
    }

    public function testFollowedAssociation()
    {
        $f = new Follower();
        $v = new User();
        $v->setUsername('maria')->setEmail('maria@example.com')->setPassword('y');
        $this->assertInstanceOf(Follower::class, $f->setFollowed($v));
        $this->assertSame($v, $f->getFollowed());
    }

    public function testCreatedAtSetter()
    {
        $f = new Follower();
        $fecha = new \DateTime('2020-01-01');
        $this->assertInstanceOf(Follower::class, $f->setCreatedAt($fecha));
        $this->assertSame($fecha, $f->getCreatedAt());
    }
}
