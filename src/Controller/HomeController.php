<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class HomeController
{
    /**
     * @return JsonResponse
     */
    public function home(): JsonResponse
    {
        return new JsonResponse(['message'=> 'Welcome to the API application.']);
    }
}