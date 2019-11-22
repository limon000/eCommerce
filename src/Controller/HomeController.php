<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\ReviewRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(ArticleRepository $artcileRepo)
    {
        $article = $artcileRepo->findAll();
        shuffle($article);

        return $this->render('home/home.html.twig',[
            'articles' => $article
        ]);
    }

    /**
     * @Route("/profile/{id}", name="account")
     * @IsGranted("ROLE_USER")
     */
    public function account(Client $client,ReviewRepository $reviewRepo,User $user): Response
    {

        $review = $reviewRepo->findBy([
            'username' => $user->getLoginName(),
        ]);
        return $this->render('home/account.html.twig',[
            'client' => $client,
            'review' => $review,
        ]);
    }

    /**
     * @Route("/resultats", name="search_results")
     */
    public function search(Request $request,ObjectManager $manager,ArticleRepository $articleRepo)
    {
        if($request->isMethod('GET')) {
            $search = $request->query->get('search');

            $resultats = $articleRepo->findBy([
                'nom' => $search,
            ]);
            if(!$resultats)
            {
                $this->addFlash('error', 'Pas de resultat');
            }
        }

        return $this->render('article/recherche.html.twig',[
            'resultat' => $resultats,
        ]);
    }



}

