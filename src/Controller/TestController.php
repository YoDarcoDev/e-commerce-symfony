<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController
{
    public function index() {
        dump("Ca fonctionne");
        die();
    }

    /**
     * @Route("test/{age}, name="test", methods={"GET", "POST"}, defaults={0}, requirements={\d+}, schemes={"http", "https})
     * @param Request $request
     * @param $age
     * @return Response
     */
    public function test(Request $request, $age) {
        return new Response("Vous avez $age ans");
    }
}

