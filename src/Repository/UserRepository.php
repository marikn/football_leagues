<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class UserRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * UserRepository constructor.
     * @param EntityManagerInterface $entityManager
     * @param EntityRepository $entityRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EntityRepository $entityRepository
    ) {
        $this->entityManager = $entityManager;
        $this->entityRepository = $entityRepository;
    }

    /**
     * @param string $id
     * @return User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneActiveById(string $id): ?User
    {
        return $this->entityRepository
            ->createQueryBuilder('u')
            ->where('u.id = :id')
            ->andWhere('u.isActive = true')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SIMPLEOBJECT);
    }

    /**
     * @param string $username
     * @return User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneActiveByUsername(string $username): ?User
    {
        return $this->entityRepository
            ->createQueryBuilder('u')
            ->where('u.username = :username')
            ->andWhere('u.isActive = true')
            ->setParameter('username', $username)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult(Query::HYDRATE_SIMPLEOBJECT);
    }
}