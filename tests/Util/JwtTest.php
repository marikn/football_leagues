<?php

namespace App\Tests\Util;

use App\Util\Jwt;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class JwtTest extends KernelTestCase
{
    public function testEncode()
    {
        static::bootKernel();
        $container = self::$kernel->getContainer();

        $jwtAlgorithm = $container->getParameter('jwt_algorithm');
        $jwtSecret    = $container->getParameter('jwt_secret');
        $jwtTtl       = $container->getParameter('jwt_ttl');

        $jwt = new Jwt($jwtAlgorithm, $jwtSecret);

        $tokenData = [
            'id' => 1,
            'crt' => (new DateTime())->getTimestamp(),
            'exp' => (new DateTime())->modify($jwtTtl)->getTimestamp(),
            'user' => [
                'id' => 1,
                'roles' => 'USER_ROLE',
            ],
        ];

        $encoded = $jwt->encode($tokenData);
        $decoded = $jwt->decode($encoded);

        $this->assertEquals(json_decode(json_encode($tokenData)), $decoded);
    }
}