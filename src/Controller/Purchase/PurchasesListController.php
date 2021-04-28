<?php

namespace App\Controller\Purchase;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class PurchasesListController extends AbstractController
{
    /**
     * Permet de lister toutes les commandes d'un user
     * @Route("/purchases", name="purchases_index")
     * @isGranted("ROLE_USER", message="Vous devez être connecté pour accéder à vos commandes")
     */
    public function index(): Response
    {
        // S'assurer que la personne est connectée sinon redirect page d'accueil -> Security
        // Préciser que ca vient de entity User pour utiliser getPurchases()
        /** @var User $user */
        $user = $this->getUser();


        // Savoir qui est connecté -> Security
        // Passer le user connecté a twig afin d'afficher ses commandes -> Environment Twig, Response
        return $this->render('purchase/index.html.twig', [
            'purchases' => $user->getPurchases()
        ]);
    }
}

