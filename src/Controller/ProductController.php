<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


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

        if($form->isSubmitted()) {

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
     * @return Response
     */
    public function edit($id, ProductRepository $productRepository, Request $request, EntityManagerInterface $em): Response
    {
        $product = $productRepository->find($id);

        // Création du formulaire
        $form = $this->createForm(ProductType::class, $product);

        // Gérer la requête
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //dd($form->getData());


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
