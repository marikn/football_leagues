<?php

namespace App\Service;

use App\Entity\DTO\Team as TeamDTO;
use App\Exception\ApiException;
use App\Repository\LeagueRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Team
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var LeagueRepository
     */
    private $leagueRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Team constructor.
     * @param EntityManagerInterface $em
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->teamRepository = $em->getRepository(\App\Entity\Team::class);
        $this->leagueRepository = $em->getRepository(\App\Entity\League::class);
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * @return \App\Entity\Team[]
     */
    public function getAllTeams(): array
    {
        return $this->teamRepository->findAll();
    }

    /**
     * @param int $teamId
     * @return \App\Entity\Team
     * @throws ApiException
     */
    public function getTeamById(int $teamId): \App\Entity\Team
    {
        $teamData = $this->teamRepository->findOneBy(['id' => $teamId]);

        if (!$teamData instanceof \App\Entity\Team) {
            throw new ApiException("Team with id: $teamId not found", Response::HTTP_NOT_FOUND);
        }

        return $teamData;
    }

    /**
     * @param int $leagueId
     * @return array
     * @throws ApiException
     */
    public function getTeamsByLeagueId(int $leagueId): array
    {
        $league = $this->leagueRepository->findOneBy(['id' => $leagueId]);

        if (empty($league)) {
            throw new ApiException("League with id: $leagueId not found", Response::HTTP_NOT_FOUND);
        }

        $teams = $this->teamRepository->findBy(['league' => $league]);

        if (empty($teams)) {
            throw new ApiException("League with id: $leagueId does not have any team", Response::HTTP_NOT_FOUND);
        }

        return $teams;
    }

    /**
     * @param $content
     * @param int $leagueId
     * @return \App\Entity\Team
     * @throws ApiException
     */
    public function addTeam($content, int $leagueId): \App\Entity\Team
    {
        /**
         * @var TeamDTO $teamDTO
         */
        $teamDTO = $this->serializer->deserialize($content, TeamDTO::class, 'json');

        $violations = $this->validator->validate($teamDTO, null, 'team_add');
        if ($violations->count() > 0) {
            throw new ApiException($violations[0]->getMessage(), Response::HTTP_NOT_ACCEPTABLE);
        }

        $league = $this->leagueRepository->findOneBy(['id' => $leagueId]);

        if (!$league instanceof \App\Entity\League) {
            throw new ApiException("League with id: $leagueId not found", Response::HTTP_NOT_FOUND);
        }

        $team = new \App\Entity\Team();
        $team->setName($teamDTO->getName())
            ->setStrip($teamDTO->getStrip())
            ->setLeague($league);

        $this->em->persist($team);
        $this->em->flush();

        return $team;
    }

    /**
     * @param $content
     * @param int $teamId
     * @return \App\Entity\Team
     * @throws ApiException
     */
    public function updateTeam($content, int $teamId): \App\Entity\Team
    {
        /**
         * @var TeamDTO $teamDTO
         */
        $teamDTO = $this->serializer->deserialize($content, TeamDTO::class, 'json');

        $violations = $this->validator->validate($teamDTO, null, 'team_update');
        if ($violations->count() > 0) {
            throw new ApiException($violations[0]->getMessage(), Response::HTTP_NOT_ACCEPTABLE);
        }

        $team = $this->teamRepository->findOneBy(['id' => $teamId]);
        if (!$team instanceof \App\Entity\Team) {
            throw new ApiException("Team with id: $teamId not found", Response::HTTP_NOT_FOUND);
        }

        if (!empty($teamDTO->getName())) {
            $team->setName($teamDTO->getName());
        }

        if (!empty($teamDTO->getStrip())) {
            $team->setStrip($teamDTO->getStrip());
        }

        $this->em->persist($team);
        $this->em->flush();

        return $team;
    }
}