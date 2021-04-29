<?php

namespace App\Controller\Purchase;

use App\Stripe\StripeService;
use App\Entity\Purchase;
use App\Repository\PurchaseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class PurchasePaymentController extends AbstractController
{
    /**
     * @Route("/purchase/pay/{id}", name="purchase_payment_form" )
     * @isGranted("ROLE_USER")
     * @param $id
     * @param PurchaseRepository $purchaseRepository
     * @param StripeService $stripeService
     * @return Response
     */
    public function showCardForm($id, PurchaseRepository $purchaseRepository, StripeService $stripeService): Response
    {
        $purchase = $purchaseRepository->find($id);

        // Eviter un $id inexistant
        if (!$purchase ||
            ($purchase && $purchase->getUser() !== $this->getUser()) ||
            ($purchase && $purchase->getStatus() === Purchase::STATUS_PAID))
        {
            return $this->redirectToRoute('cart_show');
        }

        // SERVICE STRIPE
        $intent = $stripeService->getPaymentIntent($purchase);

        return $this->render('purchase/payment.html.twig', [
            'clientSecret' => $intent->client_secret,
            'purchase' => $purchase,
            'stripePublicKey' => $stripeService->getPublicKey()
        ]);
    }
}
