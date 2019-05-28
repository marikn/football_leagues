<?php

namespace App\Controller;

use App\Service\Team;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\ApiException;

/**
 * @Route("/teams")
 */
class TeamController extends AbstractController
{
    /**
     * @Route("", methods="GET")
     *
     * @param SerializerInterface $serializer
     * @param Team $team
     * @return JsonResponse
     */
    public function getAllTeams(SerializerInterface $serializer, Team $team): JsonResponse
    {
        $teamsData = $team->getAllTeams();

        return JsonResponse::fromJsonString($serializer->serialize($teamsData, 'json'));
    }

    /**
     * @Route("/{team_id}", methods="GET", requirements={"team_id"="\d+"})
     *
     * @param int $team_id
     * @param SerializerInterface $serializer
     * @param Team $team
     * @return JsonResponse
     * @throws ApiException
     */
    public function getTeamById(int $team_id, SerializerInterface $serializer, Team $team): JsonResponse
    {
        $teamData = $team->getTeamById($team_id);

        return JsonResponse::fromJsonString($serializer->serialize($teamData, 'json'));
    }

    /**
     * @Route("/{league_id}", methods="POST", requirements={"league_id"="\d+"})
     *
     * @param int $league_id
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Team $team
     * @return JsonResponse
     * @throws ApiException
     */
    public function addTeam(int $league_id, Request $request, SerializerInterface $serializer, Team $team): JsonResponse
    {
        $teamEntity = $team->addTeam($request->getContent(), $league_id);

        return JsonResponse::fromJsonString($serializer->serialize($teamEntity, 'json'), Response::HTTP_CREATED);
    }

    /**
     * @Route("/{team_id}", methods="PUT", requirements={"team_id"="\d+"})
     *
     * @param int $team_id
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Team $team
     * @return JsonResponse
     * @throws ApiException
     */
    public function updateTeam(int $team_id, Request $request, SerializerInterface $serializer, Team $team): JsonResponse
    {
        $teamEntity = $team->updateTeam($request->getContent(), $team_id);

        return JsonResponse::fromJsonString($serializer->serialize($teamEntity, 'json'));
    }
}