<?php

namespace App\Service;

use App\Exception\ApiException;
use App\Repository\LeagueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class League
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var LeagueRepository
     */
    private $leagueRepository;

    /**
     * League constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em               = $entityManager;
        $this->leagueRepository = $entityManager->getRepository('App:League');
    }

    /**
     * @return \App\Entity\League[]
     */
    public function getAllLeagues()
    {
        return $this->leagueRepository->findAll();
    }

    /**
     * @param int $id
     * @return \App\Entity\League|null
     * @throws \Exception
     */
    public function getLeagueById(int $id): \App\Entity\League
    {
        $league = $this->leagueRepository->findOneBy(['id' => $id]);

        if (!$league instanceof \App\Entity\League) {
            throw new ApiException("League with id: $id not found", Response::HTTP_NOT_FOUND);
        }

        return $league;
    }

    /**
     * @param int $id
     * @throws ApiException
     */
    public function deleteLeague(int $id)
    {
        $league = $this->leagueRepository->findOneBy(['id' => $id]);

        if (!$league instanceof \App\Entity\League) {
            throw new ApiException("League with id: $id not found", Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($league);
        $this->em->flush();
    }
}
