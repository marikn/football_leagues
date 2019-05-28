<?php

namespace App\DataFixtures;

use App\Entity\League;
use App\Entity\Strip;
use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppFixtures extends Fixture implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $em = $this->container->get('doctrine')->getEntityManager('default');

        $colors = [
            'white',
            'black',
            'red',
            'orange',
            'yellow',
            'green',
            'blue',
            'indigo',
            'violet'
        ];

        $leagues = [
            'Premier League' => [
                'AFC Bournemouth',
                'Arsenal',
                'Brighton',
                'Burnley',
                'Crystal Palace',
                'Cardiff City',
                'Chelsea',
                'Everton',
                'Fulham',
                'Huddersfield Town',
                'Leicester City',
                'Manchester City',
                'Manchester United',
                'Newcastle Utd',
                'Southampton',
                'Tottenham Hotspur',
                'Watford',
                'West Ham United',
                'Wolverhampton Wanderers'
            ],
            'Championship' => [
                'Aston Villa',
                'Birmingham City',
                'Blackburn Rovers',
                'Brentford',
                'Bristol City',
                'Derby County',
                'Hull City',
                'Ipswich Town',
                'Leeds United',
                'Middlesbrough',
                'Millwall',
                'Norwich City',
                'Nottingham Forest',
                'Preston North End',
                'Queens Park Rangers',
                'Reading',
                'Rotherham United',
                'Sheffield United',
                'Sheffield Wednesday',
                'Stoke City',
                'Swansea City',
                'West Bromwich Albion',
                'Wigan Athletic'
            ],
            'League One' => [
                'AFC Wimbledon',
                'Accrington Stanley',
                'Barnsley',
                'Blackpool',
                'Bradford City',
                'Bristol Rovers',
                'Burton Albion',
                'Charlton Athletic',
                'Coventry City',
                'Doncaster Rovers',
                'Fleetwood Town',
                'Gillingham',
                'Luton Town',
                'Oxford United',
                'Peterborough United',
                'Plymouth Argyle',
                'Portsmouth',
                'Rochdale',
                'Scunthorpe United',
                'Shrewsbury Town',
                'Southend United',
                'Sunderland',
                'Walsall',
                'Wycombe Wanderers'
            ],
            'League Two' => [
                'Bury',
                'Cambridge United',
                'Carlisle United',
                'Cheltenham Town',
                'Colchester United',
                'Crawley Town',
                'Crewe Alexandra',
                'Exeter City',
                'Forest Green Rovers',
                'Grimsby Town',
                'Lincoln City',
                'MK Dons',
                'Macclesfield Town',
                'Mansfield Town',
                'Newport County',
                'Northampton Town',
                'Notts County',
                'Oldham Athletic',
                'Port Vale',
                'Stevenage',
                'Swindon Town',
                'Tranmere Rovers',
                'Yeovil Town.'
            ],
            'National League' => [
                'AFC Fylde',
                'Aldershot Town',
                'Barnet',
                'Barrow',
                'Boreham Wood',
                'Braintree Town',
                'Bromley',
                'Chesterfield',
                'Dagenham & Redbridge',
                'Dover Athletic',
                'Eastleigh',
                'Ebbsfleet United',
                'Gateshead',
                'FC Halifax Town',
                'Harrogate Town',
                'Havant & Waterlooville',
                'Leyton Orient',
                'Maidenhead United',
                'Salford City',
                'Solihull Moors',
                'Sutton United',
                'Wrexham AFC.'
            ]
        ];

        foreach ($leagues as $leagueName => $teams) {
            $league = new League();
            $league->setName($leagueName);

            $manager->persist($league);

            foreach ($teams as $teamName) {
                $team = new Team();
                $team->setName($teamName);
                $team->setLeague($league);
                $team->setStrip($colors[rand(0, 8)]);

                $manager->persist($team);
            }
        }

        $manager->flush();
    }
}
