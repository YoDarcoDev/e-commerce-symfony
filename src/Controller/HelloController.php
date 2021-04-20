<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;


class HelloController
{
    /**
     * @Route("/hello/{prenom}", name="hello", defaults={"prenom"="world"}, methods={"GET", "POST"})
     * @param $prenom
     * @param Environment $twig
     * @return Response
     */
    public function hello($prenom, Environment $twig) : Response
    {
        $html = $twig->render("hello.html.twig", [
            'prenom' => $prenom,
        ]);
        return new Response($html);
    }

    /**
     * @Route("/example", name="example")
     */
    public function example(Environment $twig)
    {
        $html = $twig->render("example.html.twig", [
            'age' => 33
        ]);
        return new Response($html);
    }
}

