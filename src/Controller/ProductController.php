<?php

namespace App\Controller;


use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class ProductController extends AbstractController
{
    /** Permet d'afficher des produits en fonction d'une catégory
     * @Route("/{slug}", name="product_category")
     * @param $slug
     * @param CategoryRepository $categoryRepository
     * @return Response
     */
    public function category($slug, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$category) {
            throw new NotFoundHttpException("La catégorie demandée n'existe pas");
        }

        return $this->render('product/category.html.twig', [
            'slug' => $slug,
            'category' => $category
        ]);
    }


    /**
     * Permet d'afficher la page d'un produit
     * @Route("/{category_slug}/{slug}", name="product_show")
     * @param $slug
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function show($slug, ProductRepository $productRepository): Response
    {
        $product = $productRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$product) {
            throw $this->createNotFoundException("Le produit demandé n'existe pas");
        }

        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }


    /**
     * Permet de générer et d'afficher le formulaire pour créer un produit
     * @Route("/admin/product/create", name="product_create")
     * @param FormFactoryInterface $factory
     * @param Request $request
     * @param SluggerInterface $slugger
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function create(FormFactoryInterface $factory, Request $request, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {
        $builder = $factory->createBuilder(ProductType::class);

        // Demander le formulaire
        $form = $builder->getForm();

        // Gestion de la Request
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            // Récupération des données du formulaire
            $product = $form->getData();

            // Création du slug
            $product->setSlug(strtolower($slugger->slug($product->getName())));

            // Enregistrer les données en BDD
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('product_show', [
                'category_slug' => $product->getCategory()->getSlug(),
                'slug' => $product->getSlug()
            ]);
        }

        // Afficher le formulaire
        $formView = $form->createView();

        return $this->render('product/create.html.twig', [
            'formView' => $formView
        ]);
    }


    /**
     * @Route("/admin/product/{id}/edit", name="product_edit")
     * @param $id
     * @param ProductRepository $productRepository
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function edit($id, ProductRepository $productRepository, Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $product = $productRepository->find($id);

        // Création du formulaire
        $form = $this->createForm(ProductType::class, $product); // $product => permet de lier des infos à notre formulaire

        // Gérer la requête (extrait les données de la requête pour les passer dans $product)
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Enregistrer en BDD
            $em->flush();

            // Redirection vers page produit
            return $this->redirectToRoute('product_show', [
                'category_slug' => $product->getCategory()->getSlug(),
                'slug' => $product->getSlug()
            ]);
        }

        // Afficher le formulaire
        $formView = $form->createView();

        return $this->render('product/edit.html.twig', [
            "product" => $product,
            "formView" => $formView
        ]);


    }
}
