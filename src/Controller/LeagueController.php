<?php

namespace App\Controller;

use App\Service\League;
use App\Service\Team;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/leagues")
 */
class LeagueController extends AbstractController
{
    /**
     * @Route("", methods="GET")
     *
     * @param SerializerInterface $serializer
     * @param League $league
     * @return JsonResponse
     */
    public function getAllLeagues(SerializerInterface $serializer, League $league): JsonResponse
    {
        $leaguesData = $league->getAllLeagues();

        return JsonResponse::fromJsonString($serializer->serialize($leaguesData, 'json'));
    }

    /**
     * @Route("/{league_id}", methods="GET", requirements={"league_id"="\d+"})
     *
     * @param int $league_id
     * @param SerializerInterface $serializer
     * @param League $league
     * @return JsonResponse
     * @throws \Exception
     */
    public function getLeagueById(int $league_id, SerializerInterface $serializer, League $league): JsonResponse
    {
        $leagueData = $league->getLeagueById($league_id);

        return JsonResponse::fromJsonString($serializer->serialize($leagueData, 'json'));
    }

    /**
     * @Route("/{league_id}/teams", methods="GET", requirements={"league_id"="\d+"})
     *
     * @param int $league_id
     * @param SerializerInterface $serializer
     * @param Team $team
     * @return JsonResponse
     * @throws \Exception
     */
    public function getLeagueTeams(int $league_id, SerializerInterface $serializer, Team $team): JsonResponse
    {
        $teamData = $team->getTeamsByLeagueId($league_id);

        return JsonResponse::fromJsonString($serializer->serialize($teamData, 'json'));
    }

    /**
     * @Route("/{league_id}", methods="DELETE", requirements={"league_id"="\d+"})
     *
     * @param int $league_id
     * @param SerializerInterface $serializer
     * @param League $league
     * @return JsonResponse
     * @throws \App\Exception\ApiException
     */
    public function deleteLeague(int $league_id, SerializerInterface $serializer, League $league): JsonResponse
    {
        $league->deleteLeague($league_id);

        return JsonResponse::fromJsonString($serializer->serialize(['message' => "League with id: $league_id was deleted"], 'json'));
    }
}