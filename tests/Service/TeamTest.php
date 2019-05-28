<?php

namespace App\Tests\Service;

use \App\Entity\DTO\Team as TeamDTO;
use App\Entity\League;
use App\Entity\Team;
use App\Exception\ApiException;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TeamTest extends KernelTestCase
{
    public function testGetAllTeams()
    {
        $teams = [
            0 => new Team(),
            1 => new Team()
        ];

        $teamRepository = $this->createMock(ObjectRepository::class);

        $teamRepository->expects($this->any())
            ->method('findAll')
            ->willReturn($teams);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($teamRepository);

        $serializer = $this->createMock(SerializerInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $team = new \App\Service\Team($entityManager, $serializer, $validator);

        $allTeams = $team->getAllTeams();

        $this->assertNotEmpty($allTeams);
        $this->assertInternalType('array', $allTeams);
        $this->assertEquals(2, count($allTeams));
        $this->assertInstanceOf(Team::class, $allTeams[0]);
    }

    public function testGetTeamById()
    {
        $team = new Team();

        $teamRepository = $this->createMock(ObjectRepository::class);

        $teamRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn($team);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($teamRepository);

        $serializer = $this->createMock(SerializerInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $team = new \App\Service\Team($entityManager, $serializer, $validator);

        $teamResponse = $team->getTeamById(1);

        $this->assertNotEmpty($teamResponse);
        $this->assertInstanceOf(Team::class, $teamResponse);
    }

    public function testGetTeamByIdWithNullResponseFromRepository()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);
        $this->expectExceptionMessage("Team with id: 1 not found");

        $teamRepository = $this->createMock(ObjectRepository::class);

        $teamRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($teamRepository);

        $serializer = $this->createMock(SerializerInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $team = new \App\Service\Team($entityManager, $serializer, $validator);

        $team->getTeamById(1);
    }

    public function testGetTeamByLeagueId()
    {
        $league = new League();

        $teams = [
            0 => new Team(),
            1 => new Team()
        ];

        $leagueRepository = $this->createMock(ObjectRepository::class);

        $leagueRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn($league);

        $teamRepository = $this->createMock(ObjectRepository::class);

        $teamRepository->expects($this->any())
            ->method('findBy')
            ->with(['league' => $league])
            ->willReturn($teams);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->with($this->logicalOr(
                $this->equalTo(League::class),
                $this->equalTo(Team::class)
            ))->will($this->returnCallback(
                function ($class) use ($teamRepository, $leagueRepository) {
                    switch ($class) {
                        case Team::class:
                            return $teamRepository;
                            break;
                        case League::class:
                            return $leagueRepository;
                            break;
                    }
                }
            ));

        $serializer = $this->createMock(SerializerInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $team = new \App\Service\Team($entityManager, $serializer, $validator);

        $allTeams = $team->getTeamsByLeagueId(1);

        $this->assertNotEmpty($allTeams);
        $this->assertInternalType('array', $allTeams);
        $this->assertEquals(2, count($allTeams));
        $this->assertInstanceOf(Team::class, $allTeams[0]);
    }

    public function testGetTeamByLeagueIdWithNullResponseFromLeagueRepository()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);
        $this->expectExceptionMessage("League with id: 1 not found");

        $teams = [
            0 => new Team(),
            1 => new Team()
        ];

        $leagueRepository = $this->createMock(ObjectRepository::class);

        $leagueRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn(null);

        $teamRepository = $this->createMock(ObjectRepository::class);

        $teamRepository->expects($this->any())
            ->method('findBy')
            ->with(['league' => null])
            ->willReturn($teams);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->with($this->logicalOr(
                $this->equalTo(League::class),
                $this->equalTo(Team::class)
            ))->will($this->returnCallback(
                function ($class) use ($teamRepository, $leagueRepository) {
                    switch ($class) {
                        case Team::class:
                            return $teamRepository;
                            break;
                        case League::class:
                            return $leagueRepository;
                            break;
                    }
                }
            ));

        $serializer = $this->createMock(SerializerInterface::class);
        $validator  = $this->createMock(ValidatorInterface::class);

        $team = new \App\Service\Team($entityManager, $serializer, $validator);

        $team->getTeamsByLeagueId(1);
    }

    public function testGetTeamByLeagueIdWithNullResponseFromTeamRepository()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);
        $this->expectExceptionMessage("League with id: 1 does not have any team");

        $league = new League();

        $leagueRepository = $this->createMock(ObjectRepository::class);

        $leagueRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn($league);

        $teamRepository = $this->createMock(ObjectRepository::class);

        $teamRepository->expects($this->any())
            ->method('findBy')
            ->with(['league' => $league])
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->with($this->logicalOr(
                $this->equalTo(League::class),
                $this->equalTo(Team::class)
            ))->will($this->returnCallback(
                function ($class) use ($teamRepository, $leagueRepository) {
                    switch ($class) {
                        case Team::class:
                            return $teamRepository;
                            break;
                        case League::class:
                            return $leagueRepository;
                            break;
                    }
                }
            ));

        $serializer = $this->createMock(SerializerInterface::class);
        $validator  = $this->createMock(ValidatorInterface::class);

        $team = new \App\Service\Team($entityManager, $serializer, $validator);

        $team->getTeamsByLeagueId(1);
    }

    public function testAddTeam()
    {
        $content = '{"name": "test_name","strip": "test_strip"}';
        $league = new League();
        $teamDTO = (new TeamDTO())
            ->setName('test_name')
            ->setStrip('test_strip');

        $leagueRepository = $this->createMock(ObjectRepository::class);

        $leagueRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn($league);

        $teamRepository = $this->createMock(ObjectRepository::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->with($this->logicalOr(
                $this->equalTo(League::class),
                $this->equalTo(Team::class)
            ))->will($this->returnCallback(
                function ($class) use ($teamRepository, $leagueRepository) {
                    switch ($class) {
                        case Team::class:
                            return $teamRepository;
                            break;
                        case League::class:
                            return $leagueRepository;
                            break;
                    }
                }
            ));

        $serializer = $this->createMock(SerializerInterface::class);

        $serializer->expects($this->any())
            ->method('deserialize')
            ->with($content, TeamDTO::class, 'json')
            ->willReturn($teamDTO);

        $validator = $this->createMock(ValidatorInterface::class);

        $validator->expects($this->any())
            ->method('validate')
            ->with($teamDTO, null, 'team_add')
            ->willReturn(new ConstraintViolationList());

        $team = new \App\Service\Team($entityManager, $serializer, $validator);

        $teamResponse = $team->addTeam($content, 1);
        $this->assertInstanceOf(Team::class, $teamResponse);
        $this->assertEquals($teamDTO->getName(), $teamResponse->getName());
        $this->assertEquals($teamDTO->getStrip(), $teamResponse->getStrip());
        $this->assertInstanceOf(League::class, $teamResponse->getLeague());
    }

    public function testAddTeamWithViolations()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_ACCEPTABLE);
        $this->expectExceptionMessage("Team name should not be blank.");

        $content = '{"name": "","strip": "test_strip"}';
        $league = new League();
        $teamDTO = (new TeamDTO())
            ->setName('')
            ->setStrip('test_strip');

        $leagueRepository = $this->createMock(ObjectRepository::class);

        $leagueRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn($league);

        $teamRepository = $this->createMock(ObjectRepository::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->with($this->logicalOr(
                $this->equalTo(League::class),
                $this->equalTo(Team::class)
            ))->will($this->returnCallback(
                function ($class) use ($teamRepository, $leagueRepository) {
                    switch ($class) {
                        case Team::class:
                            return $teamRepository;
                            break;
                        case League::class:
                            return $leagueRepository;
                            break;
                    }
                }
            ));

        $serializer = $this->createMock(SerializerInterface::class);

        $serializer->expects($this->any())
            ->method('deserialize')
            ->with($content, TeamDTO::class, 'json')
            ->willReturn($teamDTO);

        $validator = $this->createMock(ValidatorInterface::class);

        $validator->expects($this->any())
            ->method('validate')
            ->with($teamDTO, null, 'team_add')
            ->willReturn(new ConstraintViolationList([new ConstraintViolation(
                "Team name should not be blank.",
                "Team name should not be blank.",
                ["{{ value }}" => null],
                new Team(),
                'name',
                null

            )]));

        $team = new \App\Service\Team($entityManager, $serializer, $validator);

        $team->addTeam($content, 1);
    }

    public function testAddTeamWithNullResponseFromRepository()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);
        $this->expectExceptionMessage("League with id: 1 not found");

        $content = '{"name": "test_name","strip": "test_strip"}';

        $teamDTO = (new TeamDTO())
            ->setName('test_name')
            ->setStrip('test_strip');

        $leagueRepository = $this->createMock(ObjectRepository::class);

        $leagueRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn(null);

        $teamRepository = $this->createMock(ObjectRepository::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->with($this->logicalOr(
                $this->equalTo(League::class),
                $this->equalTo(Team::class)
            ))->will($this->returnCallback(
                function ($class) use ($teamRepository, $leagueRepository) {
                    switch ($class) {
                        case Team::class:
                            return $teamRepository;
                            break;
                        case League::class:
                            return $leagueRepository;
                            break;
                    }
                }
            ));

        $serializer = $this->createMock(SerializerInterface::class);

        $serializer->expects($this->any())
            ->method('deserialize')
            ->with($content, TeamDTO::class, 'json')
            ->willReturn($teamDTO);

        $validator = $this->createMock(ValidatorInterface::class);

        $validator->expects($this->any())
            ->method('validate')
            ->with($teamDTO, null, 'team_add')
            ->willReturn(new ConstraintViolationList());

        $team = new \App\Service\Team($entityManager, $serializer, $validator);

        $team->addTeam($content, 1);
    }

    public function testUpdateTeam()
    {
        $content = '{"name": "test_name","strip": "test_strip"}';
        $teamBeforeUpdate = (new Team())
            ->setName('old_name')
            ->setStrip('old_strip')
            ->setLeague(new League());

        $teamDTO = (new TeamDTO())
            ->setName('test_name')
            ->setStrip('test_strip');

        $teamRepository = $this->createMock(ObjectRepository::class);

        $teamRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn($teamBeforeUpdate);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($teamRepository);

        $serializer = $this->createMock(SerializerInterface::class);

        $serializer->expects($this->any())
            ->method('deserialize')
            ->with($content, TeamDTO::class, 'json')
            ->willReturn($teamDTO);

        $validator = $this->createMock(ValidatorInterface::class);

        $validator->expects($this->any())
            ->method('validate')
            ->with($teamDTO, null, 'team_update')
            ->willReturn(new ConstraintViolationList());

        $team = new \App\Service\Team($entityManager, $serializer, $validator);

        $teamResponse = $team->updateTeam($content, 1);
        $this->assertInstanceOf(Team::class, $teamResponse);
        $this->assertEquals($teamDTO->getName(), $teamResponse->getName());
        $this->assertEquals($teamDTO->getStrip(), $teamResponse->getStrip());
        $this->assertInstanceOf(League::class, $teamResponse->getLeague());
    }

    public function testUpdateTeamWithViolations()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_ACCEPTABLE);
        $this->expectExceptionMessage("Team strip should be type of alpha.");

        $content = '{"name": "test_name","strip": "111"}';
        $teamBeforeUpdate = (new Team())
            ->setName('old_name')
            ->setStrip('old_strip')
            ->setLeague(new League());

        $teamDTO = (new TeamDTO())
            ->setName('test_name')
            ->setStrip('test_strip');

        $teamRepository = $this->createMock(ObjectRepository::class);

        $teamRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn($teamBeforeUpdate);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($teamRepository);

        $serializer = $this->createMock(SerializerInterface::class);

        $serializer->expects($this->any())
            ->method('deserialize')
            ->with($content, TeamDTO::class, 'json')
            ->willReturn($teamDTO);

        $validator = $this->createMock(ValidatorInterface::class);

        $validator->expects($this->any())
            ->method('validate')
            ->with($teamDTO, null, 'team_update')
            ->willReturn(new ConstraintViolationList([new ConstraintViolation(
                "Team strip should be type of alpha.",
                "Team strip should be type of {{ type }}.",
                [
                    "{{ value }}" => "1",
                    "{{ type }}" => "alpha"
                ],
                new Team(),
                'name',
                null
            )]));

        $team = new \App\Service\Team($entityManager, $serializer, $validator);

        $team->updateTeam($content, 1);
    }

    public function testUpdateTeamWithNullResponseFromRepository()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);
        $this->expectExceptionMessage("Team with id: 1 not found");

        $content = '{"name": "test_name","strip": "test_strip"}';

        $teamDTO = (new TeamDTO())
            ->setName('test_name')
            ->setStrip('test_strip');

        $teamRepository = $this->createMock(ObjectRepository::class);

        $teamRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($teamRepository);

        $serializer = $this->createMock(SerializerInterface::class);

        $serializer->expects($this->any())
            ->method('deserialize')
            ->with($content, TeamDTO::class, 'json')
            ->willReturn($teamDTO);

        $validator = $this->createMock(ValidatorInterface::class);

        $validator->expects($this->any())
            ->method('validate')
            ->with($teamDTO, null, 'team_update')
            ->willReturn(new ConstraintViolationList());

        $team = new \App\Service\Team($entityManager, $serializer, $validator);

        $team->updateTeam($content, 1);
    }
}