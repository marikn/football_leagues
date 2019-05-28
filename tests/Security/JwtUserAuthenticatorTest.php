<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\JwtUserAuthenticator;
use App\Util\Jwt;
use Firebase\JWT\ExpiredException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class JwtUserAuthenticatorTest extends KernelTestCase
{
    public function testSupports()
    {
        $request = new Request();
        $request->headers->add(['Authorization' => 'Bearer token']);

        $jwt = $this->createMock(Jwt::class);

        $jwtUserAuthenticator = new JwtUserAuthenticator($jwt);
        $response = $jwtUserAuthenticator->supports($request);

        $this->assertTrue($response);
    }

    public function testGetCredentials()
    {
        $jwt = $this->createMock(Jwt::class);

        $request = new Request();
        $request->headers->add(['Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjkyZTk3YmQzLWE0MDctNGNkNC05YzE3LWVhNDNjYzYxNzJiNiIsImNydCI6MTU1OTA1MDk5NiwiZXhwIjoxNTU5MTM3Mzk2LCJ1c2VyIjp7ImlkIjoiYmI5NGZmNzgtNzQzMy00YjJiLWExNTItZjVmNDM4ZTVlMzJkIiwicm9sZXMiOlsiUk9MRVNfVVNFUiJdfX0.Cf6PNKeo2kybwPENlzWoBN_tNxG60Lgvh1Z_m4X2CfM']);

        $jwtUserAuthenticator = new JwtUserAuthenticator($jwt);
        $credentials = $jwtUserAuthenticator->getCredentials($request);

        $this->assertEquals('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjkyZTk3YmQzLWE0MDctNGNkNC05YzE3LWVhNDNjYzYxNzJiNiIsImNydCI6MTU1OTA1MDk5NiwiZXhwIjoxNTU5MTM3Mzk2LCJ1c2VyIjp7ImlkIjoiYmI5NGZmNzgtNzQzMy00YjJiLWExNTItZjVmNDM4ZTVlMzJkIiwicm9sZXMiOlsiUk9MRVNfVVNFUiJdfX0.Cf6PNKeo2kybwPENlzWoBN_tNxG60Lgvh1Z_m4X2CfM', $credentials);
    }

    public function testGetCredentialsWithEmptyAuthorizationHeader()
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage("Missing Authorization Header");

        $jwt = $this->createMock(Jwt::class);

        $request = new Request();

        $jwtUserAuthenticator = new JwtUserAuthenticator($jwt);
        $jwtUserAuthenticator->getCredentials($request);
    }

    public function testGetCredentialsWithBrokenAuthorizationHeader()
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage("Malformed Authorization Header");

        $jwt = $this->createMock(Jwt::class);

        $request = new Request();
        $request->headers->add(['Authorization' => 'Malformed header']);

        $jwtUserAuthenticator = new JwtUserAuthenticator($jwt);
        $jwtUserAuthenticator->getCredentials($request);
    }

    public function testGetUser()
    {
        $payload = json_decode(json_encode([
            'id' => 1,
            'crt' => 0,
            'exp' => 1,
            'user' => [
                'id' => 1,
                'roles' => 'USER_ROLES',
            ],
        ]));

        $user = new User();

        $jwt = $this->createMock(Jwt::class);

        $jwt->expects($this->any())
            ->method('decode')
            ->willReturn($payload);

        $userProvider = $this->createMock(UserProviderInterface::class);

        $userProvider->expects($this->any())
            ->method('loadUserByUsername')
            ->with(1)
            ->willReturn($user);

        $jwtUserAuthenticator = new JwtUserAuthenticator($jwt);

        $user = $jwtUserAuthenticator->getUser('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjkyZTk3YmQzLWE0MDctNGNkNC05YzE3LWVhNDNjYzYxNzJiNiIsImNydCI6MTU1OTA1MDk5NiwiZXhwIjoxNTU5MTM3Mzk2LCJ1c2VyIjp7ImlkIjoiYmI5NGZmNzgtNzQzMy00YjJiLWExNTItZjVmNDM4ZTVlMzJkIiwicm9sZXMiOlsiUk9MRVNfVVNFUiJdfX0.Cf6PNKeo2kybwPENlzWoBN_tNxG60Lgvh1Z_m4X2CfM', $userProvider);
        $this->assertInstanceOf(User::class, $user);
    }

    public function testGetUserWithExpiredJWT()
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage("Expired JWT");

        $jwt = $this->createMock(Jwt::class);

        $jwt->expects($this->any())
            ->method('decode')
            ->willThrowException(new ExpiredException());

        $userProvider = $this->createMock(UserProviderInterface::class);

        $jwtUserAuthenticator = new JwtUserAuthenticator($jwt);

        $jwtUserAuthenticator->getUser('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjkyZTk3YmQzLWE0MDctNGNkNC05YzE3LWVhNDNjYzYxNzJiNiIsImNydCI6MTU1OTA1MDk5NiwiZXhwIjoxNTU5MTM3Mzk2LCJ1c2VyIjp7ImlkIjoiYmI5NGZmNzgtNzQzMy00YjJiLWExNTItZjVmNDM4ZTVlMzJkIiwicm9sZXMiOlsiUk9MRVNfVVNFUiJdfX0.Cf6PNKeo2kybwPENlzWoBN_tNxG60Lgvh1Z_m4X2CfM', $userProvider);
    }

    public function testGetUserWithMalformedJWT()
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage("Malformed JWT");

        $jwt = $this->createMock(Jwt::class);

        $jwt->expects($this->any())
            ->method('decode')
            ->willThrowException(new \Exception());

        $userProvider = $this->createMock(UserProviderInterface::class);

        $jwtUserAuthenticator = new JwtUserAuthenticator($jwt);

        $jwtUserAuthenticator->getUser('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjkyZTk3YmQzLWE0MDctNGNkNC05YzE3LWVhNDNjYzYxNzJiNiIsImNydCI6MTU1OTA1MDk5NiwiZXhwIjoxNTU5MTM3Mzk2LCJ1c2VyIjp7ImlkIjoiYmI5NGZmNzgtNzQzMy00YjJiLWExNTItZjVmNDM4ZTVlMzJkIiwicm9sZXMiOlsiUk9MRVNfVVNFUiJdfX0.Cf6PNKeo2kybwPENlzWoBN_tNxG60Lgvh1Z_m4X2CfM', $userProvider);
    }

    public function testGetUserWithInvalidJWT()
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage("Invalid JWT");

        $payload = json_decode(json_encode([
            'id' => 1,
            'crt' => 0,
            'exp' => 1
        ]));

        $user = new User();

        $jwt = $this->createMock(Jwt::class);

        $jwt->expects($this->any())
            ->method('decode')
            ->willReturn($payload);

        $userProvider = $this->createMock(UserProviderInterface::class);

        $userProvider->expects($this->any())
            ->method('loadUserByUsername')
            ->with(1)
            ->willReturn($user);

        $jwtUserAuthenticator = new JwtUserAuthenticator($jwt);

        $jwtUserAuthenticator->getUser('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjkyZTk3YmQzLWE0MDctNGNkNC05YzE3LWVhNDNjYzYxNzJiNiIsImNydCI6MTU1OTA1MDk5NiwiZXhwIjoxNTU5MTM3Mzk2LCJ1c2VyIjp7ImlkIjoiYmI5NGZmNzgtNzQzMy00YjJiLWExNTItZjVmNDM4ZTVlMzJkIiwicm9sZXMiOlsiUk9MRVNfVVNFUiJdfX0.Cf6PNKeo2kybwPENlzWoBN_tNxG60Lgvh1Z_m4X2CfM', $userProvider);
    }
}