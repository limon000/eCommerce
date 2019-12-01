<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ContactType;
use App\Repository\ArticleRepository;
use App\Repository\CommandeRepository;
use App\Repository\DetailsRepository;
use App\Repository\ReviewRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(PaginatorInterface $paginator,ArticleRepository $artcileRepo,Request $request)
    {
        $article = $artcileRepo->findAll();
        shuffle($article);
        $pagination = $paginator->paginate($article,$request->query->getInt('page',1),6);


        return $this->render('home/home.html.twig',[
            'articles' => $pagination
        ]);
    }

    /**
     * @Route("/profile/{id}", name="account")
     * @IsGranted("ROLE_USER")
     */
    public function account(DetailsRepository $detailsRepo,Request $request,PaginatorInterface $paginator,Client $client,ReviewRepository $reviewRepo,CommandeRepository $comRepo): Response
    {

            $commande = $comRepo->findBy([
                'client' => $client,
            ]);
            $details = $detailsRepo->findBy([
               'commandes' => $commande
            ]);

            $review = $reviewRepo->findBy([
                'username' => $this->getUser()->getLoginName(),
            ]);

            $pagination = $paginator->paginate($commande,$request->query->getInt('page',1),5);

        return $this->render('home/account.html.twig',[
            'client' => $client,
            'review' => $review,
            'commandes' => $pagination,
            'details' => $details,
        ]);

    }

    /**
     * @Route("/resultats", name="search_results")
     */
    public function search(Request $request,PaginatorInterface $paginator,ObjectManager $manager,ArticleRepository $articleRepo)
    {
        if($request->isMethod('GET')) {
            $search = $request->query->get('search');
            $resultats = $articleRepo->search($search);
            $pagination = $paginator->paginate($resultats,$request->query->getInt('page',1),6);
            if(!$resultats)
            {
                $this->addFlash('error', 'Pas de resultat');
            }
        }

        return $this->render('article/recherche.html.twig',[
            'resultat' => $pagination,
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/contact", name="contact")
     */
    public function contact(MailerInterface $mailer,Request $request)
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $email = (new Email())
                   ->from($form->get('email')->getData())
                   ->to('aymenradhouen@gmail.com')
                   ->subject("Contact Letter")
                   ->text($form->get('message')->getData());
            $mailer->send($email);
            $this->addFlash('MessageSuccess' , 'Message Sent Successfully');
            return $this->redirectToRoute('home');
        }

        return $this->render('home/contact.html.twig',[
            'form' => $form->createView(),
        ]);
    }




}

