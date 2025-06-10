<?php

namespace App\Repository\Post;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Devuelve los últimos $limit posts publicados, ordenados por fecha descendente.
     *
     * @param int $limit
     * @return Post[]
     */
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve los posts más recientes (por defecto hasta 50).
     *
     * @param int $limit
     * @return Post[]
     */
    public function findRecent(int $limit = 50): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve los posts de los usuarios a los que sigue $user.
     *
     * @param User $user
     * @param int  $limit
     * @return Post[]
     */
    public function findByFollowing(User $user, int $limit = 50): array
    {
        $followingIds = $user->getFollowing()
            ->map(fn($f) => $f->getFollowed()->getId())
            ->toArray();

        if (empty($followingIds)) {
            return [];
        }

        return $this->createQueryBuilder('p')
            ->andWhere('p.user IN (:ids)')
            ->setParameter('ids', $followingIds)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
