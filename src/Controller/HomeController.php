<?php

namespace App\Controller;

use App\Entity\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @Route("/account/{id}", name="account")
     * @IsGranted("ROLE_USER")
     */
    public function account(Client $client): Response
    {


        return $this->render('home/account.html.twig',[
            'client' => $client,
        ]);
    }


}

