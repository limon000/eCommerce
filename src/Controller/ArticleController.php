<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ArticleRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * @Route("/article/{id}", name="article")
     */
    public function showArticle(ArticleRepository $articleRepo,$id,Request $request,ObjectManager $manager,Article $articlee)
    {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);

        $article = $articleRepo->findBy([
            'id' => $id
        ]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $review->setCreatedAt(new \DateTime())
                   ->setArticle($articlee)
                   ->setUsername($this->getUser()->getLoginName());
            $manager->persist($review);
            $manager->flush();
            return $this->redirectToRoute('article', [
               'id' => $id
            ]);
        }
        return $this->render('article/show.html.twig',
            [
                'articles' => $article,
                'reviews' => $form->createView(),
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
    public function informatique(ArticleRepository $artcileRepo)
    {
        $article = $artcileRepo->findBy([
            'categorie' => "informatique"
        ]);

        return $this->render('article/informatique.html.twig',
            [
                'articles' => $article,
            ]);
    }

    /**
     * @Route("/telephonie", name="telephonie")
     */
    public function telephonie(ArticleRepository $artcileRepo)
    {
        $article = $artcileRepo->findBy([
            'categorie' => "telephonie"
        ]);

        return $this->render('article/telephonie.html.twig',
            [
                'articles' => $article,
            ]);
    }

}
