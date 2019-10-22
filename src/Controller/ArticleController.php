<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/add-article", name="article")
     */
    public function article(Request $request,ObjectManager $manager)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class , $article);


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $article->setCreatedAt(new \DateTime())
                    ->setUser($this->getUser());

            $file = $article->getImage();
            $filename = md5(uniqid()).'.'.$file->guessExtension();
            $file->move($this->getParameter('upload_directory'),$filename);
            $article->setImage($filename);
            $manager->persist($article);
            $manager->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('article/article.html.twig', [
            'articleForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/informatique", name="informatique")
     */
    public function show(ArticleRepository $articlesRepo)
    {
        $articles = $articlesRepo->findAll();


        return $this->render('article/informatique.html.twig',[
            'articles' => $articles,
        ]);
    }
}
