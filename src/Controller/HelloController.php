<?php

namespace App\Controller;

use App\Taxes\Calculator;
use App\Taxes\Detector;
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
     * @param Slugify $slugify
     * @param Detector $detector
     * @return Response
     */
    public function hello($nom, Slugify $slugify, Detector $detector) : Response
    {
        $tva = $this->calculator->calcul(100);

        dump($detector->detect(101));
        dump($detector->detect(10));

        dump($slugify->slugify("Hello World"));
        return new Response("Hello $nom");
    }
}

