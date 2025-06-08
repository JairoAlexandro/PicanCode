<?php

namespace App\Tests\Repository;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $repo;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repo = static::getContainer()->get(UserRepository::class);
    }

    public function testRepositoryIsService(): void
    {
        $this->assertInstanceOf(UserRepository::class, $this->repo);
    }

    public function testFindAllReturnsArray(): void
    {
        $users = $this->repo->findAll();
        $this->assertIsArray($users);
    }
}
