<?php

namespace App\Controller;

use App\Cart\CartService;
use App\Form\CartConfirmationType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class CartController extends AbstractController
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CartService
     */
    protected $cartService;


    public function __construct(ProductRepository $productRepository, CartService $cartService)
    {
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
    }

    /**
     * @Route("/cart/add/{id}", name="cart_add", requirements={"id":"\d+" })
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function add($id, Request $request) : Response
    {
        //Sécurisation: est-ce que le produit existe
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException("Le produit $id n'existe pas");
        }

        $this->cartService->add($id);

        // FlashBag
        $this->addFlash('success', "Le produit a bien été ajouté au panier");

        // Redirect panier si on ajoute une quantité depuis le panier
        if($request->query->get('returnToCart')) {
            return $this->redirectToRoute('cart_show');
        }

        // Ajout Panier
        return $this->redirectToRoute('product_show', [
            'category_slug' => $product->getCategory()->getSlug(),
            'slug' => $product->getSlug()
        ]);
    }


    /**
     * @Route("/cart", name="cart_show")
     * @return Response
     */
    public function show(): Response
    {
        // Creation du formulaire
        $form = $this->createForm(CartConfirmationType::class);

        $detailedCart = $this->cartService->getDetailedCartItems();

        $total = $this->cartService->getTotal();



        return $this->render("cart/index.html.twig", [
            'items' => $detailedCart,
            'total' => $total,
            'confirmationForm' => $form->createView()
        ]);
    }


    /**
     * @Route("/cart/delete/{id}", name="cart_delete", requirements={"id": "\d+"})
     * @param $id
     * @return Response
     */
    public function delete($id): Response
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException("Le produit n'existe pas et ne peut donc pas être supprimé");
        }

        $this->cartService->remove($id);

        $this->addFlash('success', 'Le produit a bien été supprimé du panier');

        return $this->redirectToRoute("cart_show");
    }


    /**
     * @Route("/cart/decrement/{id}", name="cart_decrement", requirements={"id": "\d+"})
     * @param $id
     * @return Response
     */
    public function decrement($id) : Response
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException("Le produit n'existe pas et ne peut donc pas être décrémenté");
        }

        $this->cartService->decrement($id);

        $this->addFlash('success', "Le produit a bien été décrémenté");

        return $this->redirectToRoute("cart_show");
    }
}

