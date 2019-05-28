<?php

namespace App\Security;

use App\Util\Jwt;
use Exception;
use Firebase\JWT\ExpiredException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class JwtUserAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var Jwt
     */
    private $jwt;

    /**
     * JwtUserAuthenticator constructor.
     * @param Jwt $jwt
     */
    public function __construct(Jwt $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return JsonResponse|Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => 'Invalid Credentials'
        ];

        if ($exception instanceof CustomUserMessageAuthenticationException) {
            $data['message'] = $exception->getMessageKey();
        }

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    /**
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return JsonResponse|Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'message' => 'Authentication Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        return $request->headers->has('Authorization');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getCredentials(Request $request)
    {
        if (!$request->headers->has('Authorization')) {
            throw new CustomUserMessageAuthenticationException('Missing Authorization Header');
        }

        $headerParts = explode(' ', $request->headers->get('Authorization'));

        if (!(count($headerParts) === 2 && $headerParts[0] === 'Bearer')) {
            throw new CustomUserMessageAuthenticationException('Malformed Authorization Header');
        }

        return $headerParts[1];
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            $payload = $this->jwt->decode($credentials);
        } catch (ExpiredException $e) {
            throw new CustomUserMessageAuthenticationException('Expired JWT');
        } catch (Exception $e) {
            throw new CustomUserMessageAuthenticationException('Malformed JWT');
        }

        if (!isset($payload->user)) {
            throw new CustomUserMessageAuthenticationException('Invalid JWT');
        }

        return $userProvider->loadUserByUsername($payload->user->id);
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool|void
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }
}