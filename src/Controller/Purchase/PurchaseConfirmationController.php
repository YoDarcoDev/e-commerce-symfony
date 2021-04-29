<?php

namespace App\Controller\Purchase;

use App\Cart\CartService;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use App\Form\CartConfirmationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class PurchaseConfirmationController extends AbstractController
{
    protected $router;
    protected $cartService;
    protected $em;

    public function __construct(CartService $cartService, EntityManagerInterface $em)
    {
        $this->cartService = $cartService;
        $this->em = $em;
    }

    /**
     * @Route("/purchase/confirm", name="purchase_confirm")
     * @isGranted("ROLE_USER", message="Vous devez être connecté pour confirmer une commande")
     * @param Request $request
     * @return RedirectResponse
     */
    public function confirm(Request $request): RedirectResponse
    {
        // Lire les données du formulaire (FormFactoryInterface - Request)
        $form = $this->createForm(CartConfirmationType::class);

        // Analyse de la request, demande de la request directement dans la function car elle est unique et depend de la requête
        $form->handleRequest($request);

        // Si le formulaire n'a pas été soumis : on sort
        if (!$form->isSubmitted()) {

            // Message Flash (FlashBagInterface) (FlashBag est lié à la session donc à la requête, on le livre donc dans la function et non dans le construct)
            $this->addFlash('warning', "Vous devez remplir le formulaire de confirmation");
            return $this->redirectToRoute('cart_show');
        }

        // Si je ne suis pas connecté : on sort (Security)
        $user = $this->getUser();

       $cartItems = $this->cartService->getDetailedCartItems();

        // Si formulaire soumis, user connecté mais pas de produits dans le panier (CartService)
        if (count($cartItems) === 0) {
            $this->addFlash('warning', "Vous ne pouvez pas confirmer une commande avec un panier vide");
            return $this->redirectToRoute('cart_show');
        }

        // Création de la purchase, récupération des infos du formulaire(nom, adresse, code postal, city)
        /** @var Purchase */
        $purchase = $form->getData();

        // Lier avec le user connecté (Security)
        $purchase
                ->setUser($user)
                ->setPurchasedAt(new \DateTime())
                ->setTotal($this->cartService->getTotal());

        $this->em->persist($purchase);

        // Lier avec les produits du panier
        foreach($cartItems as $cartItem) {
            $purchaseItem = new PurchaseItem();
            $purchaseItem
                ->setPurchase($purchase)
                ->setProduct($cartItem->product)
                ->setProductName($cartItem->product->getName())
                ->setProductPrice($cartItem->product->getPrice())
                ->setQuantity($cartItem->quantity)
                ->setTotal($cartItem->getTotal());

            $this->em->persist($purchaseItem);
        }

        // Enregistrer la commande (EntityManagerInterface)
        $this->em->flush();

        // Vider le panier
        $this->cartService->empty();

        $this->addFlash('success', "La commande a bien été enregistrée");

        return $this->redirectToRoute('purchases_index');
    }
}