<?php

namespace App\DataFixtures;

use App\Entity\Category;
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


        // Création des utilisateurs
        for ($i = 0; $i < 5; $i++) {
            $user = new User;

            $hash = $this->encoder->encodePassword($user, 'password');

            $user
                ->setEmail("user$i@gmail.com")
                ->setFullName($faker->name())
                ->setPassword($hash);

            $manager->persist($user);
        }

        // Création de la catégorie (nom, slug)
        for ($c = 0; $c < 3; $c++) {
            $category = new Category;
            $category
                    ->setName($faker->department())
                    ->setSlug(strtolower($this->slugger->slug($category->getName())));

            $manager->persist($category);

            // Création produits qui seront reliés à des categorise
            for ($i = 0; $i < mt_rand(15, 20); $i++) {
                $product = new Product;
                $product
                    ->setName($faker->productName())
                    ->setPrice($faker->price(4000, 20000))
                    ->setSlug(strtolower($this->slugger->slug($product->getName())))
                    ->setCategory($category)
                    ->setShortDescription(($faker->paragraph()))
                    ->setMainPicture($faker->imageUrl(400, 400, true));

                $manager->persist($product);
            }
        }
        $manager->flush();
    }
}


