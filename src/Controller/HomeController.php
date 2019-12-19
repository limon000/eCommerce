<?php

namespace App\Controller;

use App\Form\ClientType;
use App\Form\ContactType;
use App\Form\RegistrationFormType;
use App\Repository\ArticleRepository;
use App\Repository\ClientRepository;
use App\Repository\CommandeRepository;
use App\Repository\DetailsRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(PaginatorInterface $paginator,ArticleRepository $artcileRepo,Request $request)
    {

        $articles = $artcileRepo->findAll();
        shuffle($articles);
        $pagination = $paginator->paginate($articles,$request->query->getInt('page',1),6);

        return $this->render('home/home.html.twig',[
            'articles' => $pagination,
        ]);
    }

    /**
     * @Route("/profile", name="account")
     * @IsGranted("ROLE_USER")
     */
    public function account(ClientRepository $clientRepo,DetailsRepository $detailsRepo,Request $request,PaginatorInterface $paginator,ReviewRepository $reviewRepo,CommandeRepository $comRepo): Response
    {

        $client = $clientRepo->findOneBy([
           'id' => $this->getUser()->getClient()->getId(),
        ]);

            $commande = $comRepo->findBy([
                'client' => $client,
            ]);
            $details = $detailsRepo->findBy([
               'commandes' => $commande
            ]);

            $review = $reviewRepo->findBy([
                'username' => $this->getUser()->getLoginName(),
            ]);


        return $this->render('home/account.html.twig',[
            'client' => $client,
            'review' => $review,
            'commandes' => $commande,
            'details' => $details,
        ]);

    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/profile/edit", name="profile_edit")
     */
    public function edit(ClientRepository $clientRepo,Request $request,EntityManagerInterface $manager)
    {
        $client = $clientRepo->findOneBy([
            'id' => $this->getUser()->getClient()->getId(),
        ]);
        $form = $this->createForm(ClientType::class , $client);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($client);
            $manager->flush();
            $this->addFlash('success', 'Profile Updated !');
            return $this->redirectToRoute('account');
        }

        return $this->render('home/profile.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/profile/edit_login", name="profile_edit_login")
     */
    public function editAccount(UserRepository $userRepo,UserPasswordEncoderInterface $passwordEncoder,Request $request,EntityManagerInterface $manager)
    {
        $user = $userRepo->findOneBy([
            'id' => $this->getUser()->getId(),
        ]);

        $form = $this->createForm(RegistrationFormType::class , $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Profile Updated !');
            return $this->redirectToRoute('home');
        }


        return $this->render('home/editlogin.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
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

