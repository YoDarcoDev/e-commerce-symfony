<?php

namespace App\Controller\Purchase;


use App\Cart\CartService;
use App\Entity\Purchase;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class PurchasePaymentSuccessController extends AbstractController
{
    /**
     * @Route("/purchase/terminate/{id}", name="purchase_payment_success")
     * @isGranted("ROLE_USER")
     * @param $id
     * @param PurchaseRepository $purchaseRepository
     * @param EntityManagerInterface $em
     * @param CartService $cartService
     */
    public function success($id, PurchaseRepository $purchaseRepository, EntityManagerInterface $em, CartService $cartService)
    {
        // Récupération de la commande
        $purchase = $purchaseRepository->find($id);

        // Si pas de commande, ou si le user n'est pas celui à qui appartient la commande
        // Ou si il y a une commande avec déjà le statut PAID
        if (!$purchase ||
            ($purchase && $purchase->getUser() !== $this->getUser()) ||
            ($purchase && $purchase->getStatus() === Purchase::STATUS_PAID))
        {
            $this->addFlash('warning', "La commande n'existe pas" );
            return $this->redirectToRoute('purchases_index');
        }

        // Changer le statut PENDING en PAID
        $purchase->setStatus(Purchase::STATUS_PAID);

        $em->flush();

        // Vider le panier
        $cartService->empty();

        // Rediriger vers liste des commandes et envoyer un flash
        $this->addFlash('success', "Votre paiement a bien été pris en compte");
        return $this->redirectToRoute('purchases_index');
    }
}