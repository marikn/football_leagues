<?php

namespace App\Controller;

use App\Entity\Token;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Util\Jwt;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LoginController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    /**
     * @var Jwt
     */
    private $jwt;

    /**
     * @var string
     */
    private $jwtTtl;

    /**
     * LoginController constructor.
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @param Jwt $jwt
     * @param string $jwtTtl
     */
    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $userPasswordEncoder,
        Jwt $jwt,
        string $jwtTtl
    ) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->jwt = $jwt;
        $this->jwtTtl = $jwtTtl;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function login(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->userRepository->findOneActiveByUsername($data['username']);
        if (
            !$user instanceof User ||
            !$this->userPasswordEncoder->isPasswordValid($user, $data['password'])
        ) {
            throw new UnauthorizedHttpException('Basic realm="API Login"', 'Invalid credentials.');
        }

        $id = Uuid::uuid4()->toString();
        $createdAt = (new DateTime());
        $expiresAt = (new DateTime())->modify($this->jwtTtl);

        $tokenData = [
            'id' => $id,
            'crt' => $createdAt->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
            'user' => [
                'id' => $user->getId(),
                'roles' => $user->getRoles(),
            ],
        ];

        $token = new Token();
        $token->setId($id);
        $token->setCreatedAt($createdAt);
        $token->setExpiresAt($expiresAt);
        $token->setUser($user);
        $token->setData($this->jwt->encode($tokenData));

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return new JsonResponse(['token' => $token->getData()], Response::HTTP_CREATED);
    }
}