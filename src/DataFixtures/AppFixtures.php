<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    protected $slugger;

    public function __construct(SluggerInterface $slugger) {
        $this->slugger = $slugger;
    }


    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new \Liior\Faker\Prices($faker));
        $faker->addProvider(new \Bezhanov\Faker\Provider\Commerce($faker));
        $faker->addProvider(new \Bluemmb\Faker\PicsumPhotosProvider($faker));

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

