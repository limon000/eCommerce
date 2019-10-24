<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function account(ClientRepository $clientRepo,$id)
    {

        $clients = $clientRepo->findBy(
        ['id' => $id]
        );


        return $this->render('home/account.html.twig',[
            'clients' => $clients,
        ]);
    }
}

