<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    protected $slugger;
    protected $encoder;

    /**
     * AppFixtures constructor.
     * @param SluggerInterface $slugger
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(SluggerInterface $slugger, UserPasswordEncoderInterface $encoder) {
        $this->slugger = $slugger;
        $this->encoder = $encoder;
    }


    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new \Liior\Faker\Prices($faker));
        $faker->addProvider(new \Bezhanov\Faker\Provider\Commerce($faker));
        $faker->addProvider(new \Bluemmb\Faker\PicsumPhotosProvider($faker));


        // Création d'un user admin
        $admin = new User;

        // Hachage du mot de passe
        $hash = $this->encoder->encodePassword($admin, "password");

        $admin
            ->setEmail('admin@gmail.com')
            ->setFullName("Admin")
            ->setPassword($hash)
            ->setRoles(['ROLE_ADMIN']);

        $manager->persist($admin);


        // CREATION DES USERS
        $users = []; // Tableau pour les purchases

        for ($i = 0; $i < 5; $i++) {
            $user = new User();

            $hash = $this->encoder->encodePassword($user, 'password');

            $user
                ->setEmail("user$i@gmail.com")
                ->setFullName($faker->name())
                ->setPassword($hash);

            // Ajout des users dans un tableau
            $users[] = $user;

            $manager->persist($user);
        }

        // CREATION DES CATEGORIES (nom, slug)
        $products = [];

        for ($c = 0; $c < 3; $c++) {
            $category = new Category;
            $category
                    ->setName($faker->department())
                    ->setSlug(strtolower($this->slugger->slug($category->getName())));

            $manager->persist($category);

            // CREATION DES PRODUITS QUI SONT LIES A DES CATEGORIES
            for ($i = 0; $i < mt_rand(15, 20); $i++) {
                $product = new Product;
                $product
                    ->setName($faker->productName())
                    ->setPrice($faker->price(4000, 20000))
                    ->setSlug(strtolower($this->slugger->slug($product->getName())))
                    ->setCategory($category)
                    ->setShortDescription(($faker->paragraph()))
                    ->setMainPicture($faker->imageUrl(400, 400, true));

                $products[] = $product;

                $manager->persist($product);
            }
        }

        // CREATION DES PURCHASES
        for ($p = 0; $p < mt_rand(20, 40); $p++) {
            $purchase = new Purchase;

            $purchase
                ->setFullName($faker->name)
                ->setAddress($faker->streetAddress)
                ->setPostalCode($faker->postcode)
                ->setCity($faker->city)
                ->setUser($faker->randomElement($users)) // Va prendre un élément au hasard de notre tableau $users
                ->setTotal(mt_rand(2000, 30000))
                ->setPurchasedAt($faker->dateTimeBetween('-6 months')); // Il y a 6 mois et maintenant

            // Récupérer entre 3 et 5 produits de tous mes produits crées
            $selectedProducts = $faker->randomElements($products, mt_rand(3, 5));

            // Pour chacun de ces produits, création d'une ligne de commande
            foreach($selectedProducts as $product) {
                $purchaseItem = new PurchaseItem();
                $purchaseItem
                    ->setProduct($product)
                    ->setQuantity(mt_rand(1, 3))
                    ->setProductName($product->getName())
                    ->setProductPrice($product->getprice())
                    ->setTotal($purchaseItem->getProductPrice() * $purchaseItem->getQuantity())
                    ->setPurchase($purchase);

                $manager->persist($purchaseItem);
            }

            // Par défaut c'est PENDING, mais dans 90% des cas je le passerais en PAID
            if ($faker->boolean(90)) {
                $purchase->setStatus(Purchase::STATUS_PAID);
            }

            $manager->persist($purchase);
        }

        $manager->flush();
    }
}


