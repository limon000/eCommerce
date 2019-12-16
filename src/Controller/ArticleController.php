<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ArticleRepository;
use App\Repository\ReviewRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Panier\PanierService;


class ArticleController extends AbstractController
{
    /**
     * @Route("/article/{id}", name="article")
     */
    public function showArticle(ReviewRepository $reviewRepo,PanierService $panierService,Article $article,Request $request,ObjectManager $manager)
    {

        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);

        $form->handleRequest($request);
        $avg = $reviewRepo->avg($article);
        $star5 = $reviewRepo->longeur(5,$article);
        $star4 = $reviewRepo->longeur(4,$article);
        $star3 = $reviewRepo->longeur(3,$article);
        $star2 = $reviewRepo->longeur(2,$article);
        $star1 = $reviewRepo->longeur(1,$article);


        if($form->isSubmitted() && $form->isValid())
        {
            if(!$this->getUser())
            {
                return $this->redirectToRoute('app_login');
            }
            $rate = $request->request->get('rating');
            $review->setCreatedAt(new \DateTime())
                   ->setArticle($article)
                   ->setUsername($this->getUser()->getLoginName())
                   ->setRating($rate);
            $manager->persist($review);
            $manager->flush();
            return $this->redirectToRoute('article', [
               'id' => $article->getId(),
            ]);
        }
        return $this->render('article/show.html.twig',
            [
                'article' => $article,
                'reviews' => $form->createView(),
                'items' => $panierService->getFullCart(),
                'moyenne' => $avg,
                'star5' => $star5,
                'star4' => $star4,
                'star3' => $star3,
                'star2' => $star2,
                'star1' => $star1,
            ]);
    }

    /**
     * @Route("/review/remove/{id}" , name="review_remove")
     */
    public function delete(Review $review)
    {

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($review);
        $entityManager->flush();

        return $this->redirectToRoute('article', [
            'id' => $review->getArticle()->getId()
        ]);
    }


    /**
     * @Route("/informatique", name="informatique")
     */
    public function informatique(Request $request,PaginatorInterface $paginator,ArticleRepository $artcileRepo)
    {
        $article = $artcileRepo->findBy([
            'categorie' => "informatique"
        ]);
        $pagination = $paginator->paginate($article,$request->query->getInt('page',1),6);

        return $this->render('article/informatique.html.twig',
            [
                'articles' => $pagination,
            ]);
    }

    /**
     * @Route("/telephonie", name="telephonie")
     */
    public function telephonie(Request $request,PaginatorInterface $paginator,ArticleRepository $artcileRepo)
    {
        $article = $artcileRepo->findBy([
            'categorie' => "telephonie"
        ]);
        $pagination = $paginator->paginate($article,$request->query->getInt('page',1),6);


        return $this->render('article/telephonie.html.twig',
            [
                'articles' => $pagination,
            ]);
    }

    /**
     * @Route("/tv", name="tv")
     */
    public function tv(Request $request,PaginatorInterface $paginator,ArticleRepository $artcileRepo)
    {
        $article = $artcileRepo->findBy([
            'categorie' => "TV | Photo & Son"
        ]);
        $pagination = $paginator->paginate($article,$request->query->getInt('page',1),6);


        return $this->render('article/tv.html.twig',
            [
                'articles' => $pagination,
            ]);
    }

    /**
     * @Route("/gaming", name="gaming")
     */
    public function gaming(Request $request,PaginatorInterface $paginator,ArticleRepository $artcileRepo)
    {
        $article = $artcileRepo->findBy([
            'categorie' => "Gaming"
        ]);

        $pagination = $paginator->paginate($article,$request->query->getInt('page',1),6);

        return $this->render('article/gaming.html.twig',
            [
                'articles' => $pagination,
            ]);
    }

    /**
     * @Route("/impression", name="impression")
     */
    public function impression(Request $request,PaginatorInterface $paginator,ArticleRepository $artcileRepo)
    {
        $article = $artcileRepo->findBy([
            'categorie' => "Impression"
        ]);
        $pagination = $paginator->paginate($article,$request->query->getInt('page',1),6);

        return $this->render('article/impression.html.twig',
            [
                'articles' => $pagination,
            ]);
    }

    /**
     * @Route("/reseau", name="reseau")
     */
    public function reseau(Request $request,PaginatorInterface $paginator,ArticleRepository $artcileRepo)
    {
        $article = $artcileRepo->findBy([
            'categorie' => "Reseaux & Securite"
        ]);
        $pagination = $paginator->paginate($article,$request->query->getInt('page',1),6);

        return $this->render('article/reseau.html.twig',
            [
                'articles' => $pagination,
            ]);
    }

}
