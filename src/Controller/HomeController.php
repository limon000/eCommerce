<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Review;
use App\Entity\User;
use App\Repository\ReviewRepository;
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
    public function home()
    {
        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
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



}

