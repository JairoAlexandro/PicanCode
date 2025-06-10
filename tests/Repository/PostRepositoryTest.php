<?php

namespace App\Tests\Repository;

use App\Repository\Post\PostRepository;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class PostRepositoryTest extends TestCase
{
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $em = $this->createMock(EntityManager::class);
        $this->registry
            ->method('getManagerForClass')
            ->willReturn($em);
    }

    public function testFindLatestReturnsResultsFromQueryBuilder(): void
    {
        
        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['orderBy', 'setMaxResults', 'getQuery'])
            ->getMock();

        $qb->expects(self::once())
            ->method('orderBy')
            ->with('p.createdAt', 'DESC')
            ->willReturnSelf();

        $qb->expects(self::once())
            ->method('setMaxResults')
            ->with(5)
            ->willReturnSelf();

        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResult'])
            ->getMock();

        $query->expects(self::once())
            ->method('getResult')
            ->willReturn(['postA', 'postB']);

        $qb->method('getQuery')
            ->willReturn($query);

        $repo = $this->getMockBuilder(PostRepository::class)
            ->setConstructorArgs([$this->registry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $repo->method('createQueryBuilder')
            ->with('p')
            ->willReturn($qb);

        $result = $repo->findLatest(5);
        self::assertSame(['postA', 'postB'], $result);
    }

    public function testFindRecentReturnsResultsFromQueryBuilder(): void
    {
        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['orderBy', 'setMaxResults', 'getQuery'])
            ->getMock();

        $qb->expects(self::once())
            ->method('orderBy')
            ->with('p.createdAt', 'DESC')
            ->willReturnSelf();

        $qb->expects(self::once())
            ->method('setMaxResults')
            ->with(50)
            ->willReturnSelf();

        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResult'])
            ->getMock();

        $query->expects(self::once())
            ->method('getResult')
            ->willReturn(['recent1', 'recent2']);

        $qb->method('getQuery')
            ->willReturn($query);

        $repo = $this->getMockBuilder(PostRepository::class)
            ->setConstructorArgs([$this->registry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $repo->method('createQueryBuilder')
            ->willReturn($qb);

        $result = $repo->findRecent();
        self::assertSame(['recent1', 'recent2'], $result);
    }

    public function testFindByFollowingWithNoFollowingsReturnsEmptyArray(): void
    {
        $repo = new PostRepository($this->registry);
        $user = new User(); 
        self::assertSame([], $repo->findByFollowing($user));
    }
}
