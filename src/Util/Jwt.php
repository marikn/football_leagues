<?php

namespace App\Util;

use Firebase\JWT\JWT as FirebaseJWT;
use stdClass;

class Jwt
{
    /**
     * @var string
     */
    private $jwtAlgorithm;

    /**
     * @var string
     */
    private $jwtSecret;

    /**
     * Jwt constructor.
     * @param string $jwtAlgorithm
     * @param string $jwtSecret
     */
    public function __construct(
        string $jwtAlgorithm,
        string $jwtSecret
    ) {
        $this->jwtAlgorithm = $jwtAlgorithm;
        $this->jwtSecret = $jwtSecret;
    }

    /**
     * @param iterable $tokenData
     * @return string
     */
    public function encode(iterable $tokenData): string
    {
        return FirebaseJWT::encode($tokenData, $this->jwtSecret, $this->jwtAlgorithm);
    }

    /**
     * @param string $tokenString
     * @return stdClass
     */
    public function decode(string $tokenString): stdClass
    {
        return FirebaseJWT::decode($tokenString, $this->jwtSecret, [$this->jwtAlgorithm]);
    }
}