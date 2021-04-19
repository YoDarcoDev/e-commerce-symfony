<?php

namespace App\Controller;

use App\Taxes\Calculator;
use Cocur\Slugify\Slugify;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HelloController
{
    protected $calculator;

    public function __construct(Calculator $calculator)
    {
        $this->calculator = $calculator;
    }
    /**
     * @Route("/hello/{nom}", name="hello", defaults={"nom"="world"}, methods={"GET", "POST"})
     * @param $nom
     * @return Response
     */
    public function hello($nom, Slugify $slugify) : Response
    {
        $tva = $this->calculator->calcul(100);
        dump($tva);
        dump($slugify->slugify("Hello World"));
        return new Response("Hello $nom");
    }
}
