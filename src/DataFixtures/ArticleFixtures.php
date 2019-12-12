<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for($i=0;$i < 20;$i++)
        {
            $article = new Article();
            $article
                    ->setNom("Routeur")
                    ->setPrix(12)
                    ->setCategorie("Reseaux & Securite")
                    ->setImage("5bb5e4bca14feef7168c9c641e184582.jpeg")
                    ->setQuantite(20)
                    ->setDescription("Un bon routeur")
                    ->setCreatedAt(new \DateTime())
                    ->setUserCreated("aymen000");
            $manager->persist($article);
        }


        $manager->flush();
    }
}
