<?php

namespace App\Cart;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    protected $session;
    protected $productRepository;


    // Injection des services dans le construct car aucune route n'est assignée à nos méthodes
    public function __construct(SessionInterface $session, ProductRepository $productRepository)
    {
        $this->session = $session;
        $this->productRepository = $productRepository;
    }


    /**
     * Permet de récupérer le tableau des produits
     * @return array
     */
    protected function getCart() : array
    {
        // 1. Retrouver le panier dans la session, si il n'existe pas, on crée un tableau vide
        return $this->session->get('cart', []);
    }


    /**
     * Permet de sauvegarder le tableau des produits
     * @param array $cart
     */
    protected function saveCart(array $cart)
    {
        // 6. Enregistrer le tableau mis  à jour dans la session
        $this->session->set('cart', $cart);
    }


    /**
     * Permet de vider le panier une fois la commande terminée
     */
    public function empty()
    {
        $this->saveCart([]);
    }

    /**
     * Permet d'ajouter un produit au panier
     * @param int $id
     */
    public function add(int $id)
    {
        // Récupérer le tableau des produits
        $cart = $this->getCart();

        // Voir si le produit ($id) existe déjà dans le tableau
        // Si l'id existe dans mon tableau $cart, si oui augmenter la quantité, si non la quantité vaut 1
        if (!array_key_exists($id, $cart)) {
            $cart[$id] = 0;
        }

        $cart[$id]++;

        // Sauvegarder le tableau
        $this->saveCart($cart);
    }


    public function remove(int $id)
    {
        $cart = $this->getCart();

        unset($cart[$id]);

        // Mise à jour dans la session
        $this->saveCart($cart);
    }



    public function decrement(int $id)
    {
        $cart = $this->getCart();

        if (!array_key_exists($id, $cart)) {
            return;
        }

        // Soit le produit est à 1 en quantité alors il faut le supprimer
        if ($cart[$id] === 1) {
            $this->remove($id);
            return;
        }

        // Si quantité produit > 1 : on décrémente et Maj panier
        $cart[$id]--;
        $this->saveCart($cart);
    }




    /**
     * Calculer le total du panier à partir du tableau de session
     * @return int
     */
    public function getTotal() : int
    {
        $total = 0;

        foreach($this->getCart() as $id => $quantity) {

            $product = $this->productRepository->find($id);

            // Si produit null recommencer la boucle sans mettre le produit dans le tableau
            if (!$product) {
                continue;
            }
            $total += $product->getPrice() * $quantity;
        }
        return $total;
    }


    /**
     * Récupérer un tableau avec tous les produits du panier
     * @return CartItem[]
     */
    public function getDetailedCartItems() : array
    {
        // Panier
        $detailedCart = [];

        foreach($this->getCart() as $id => $quantity) {

            $product = $this->productRepository->find($id);

            // Si produit null recommencer la boucle sans mettre le produit dans le tableau
            if (!$product) {
                continue;
            }

            $detailedCart[] = new CartItem($product, $quantity);
        }
        return $detailedCart;
    }
}