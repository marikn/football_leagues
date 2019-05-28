<?php

namespace App\Tests\Service;

use App\Entity\League;
use App\Exception\ApiException;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Response;

class LeagueTest extends KernelTestCase
{
    public function testGetAllLeagues()
    {
        $leagues = [
            0 => new League(),
            1 => new League()
        ];

        $leaguesRepository = $this->createMock(ObjectRepository::class);

        $leaguesRepository->expects($this->any())
            ->method('findAll')
            ->willReturn($leagues);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leaguesRepository);

        $league = new \App\Service\League($entityManager);

        $allLeagues = $league->getAllLeagues();

        $this->assertNotEmpty($allLeagues);
        $this->assertInternalType('array', $allLeagues);
        $this->assertEquals(2, count($allLeagues));
        $this->assertInstanceOf(League::class, $allLeagues[0]);
    }

    public function testGetLeagueById()
    {
        $league = new League();

        $leaguesRepository = $this->createMock(ObjectRepository::class);

        $leaguesRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn($league);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leaguesRepository);

        $league = new \App\Service\League($entityManager);

        $leagueResponse = $league->getLeagueById(1);

        $this->assertNotEmpty($leagueResponse);
        $this->assertInstanceOf(League::class, $leagueResponse);

    }

    public function testGetLeagueByIdWithNullResponseFromRepository()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);
        $this->expectExceptionMessage("League with id: 1 not found");

        $leaguesRepository = $this->createMock(ObjectRepository::class);

        $leaguesRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leaguesRepository);

        $league = new \App\Service\League($entityManager);

        $league->getLeagueById(1);
    }

    public function testDeleteLeague()
    {
        $league = new League();

        $leaguesRepository = $this->createMock(ObjectRepository::class);

        $leaguesRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn($league);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leaguesRepository);

        $league = new \App\Service\League($entityManager);

        $leagueResponse = $league->deleteLeague(1);

        $this->assertEmpty($leagueResponse);

    }

    public function testDeleteLeagueWithNullResponseFromRepository()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);
        $this->expectExceptionMessage("League with id: 1 not found");

        $leaguesRepository = $this->createMock(ObjectRepository::class);

        $leaguesRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leaguesRepository);

        $league = new \App\Service\League($entityManager);

        $league->deleteLeague(1);
    }
}