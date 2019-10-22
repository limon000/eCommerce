<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Form\ClientType;
use App\Form\RegistrationFormType;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/profile/{id}/edit", name="profile_edit")
     */
    public function edit(Client $client,Request $request,EntityManagerInterface $manager,UserPasswordEncoderInterface $encoder,ClientRepository $clientRepo,$id)
    {
        $form = $this->createForm(ClientType::class , $client);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($client);
            $manager->flush();
            $this->addFlash('success', 'Profile Updated !');
            return $this->redirectToRoute('account');
        }
        $client = $clientRepo->findBy(
            ['id' => $id]
        );

        return $this->render('home/profile.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
            'clients' => $client,
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/profile/{id}/edit_login", name="profile_edit_login")
     */
    public function editAccount(User $user,Request $request,EntityManagerInterface $manager,UserPasswordEncoderInterface $encoder,UserRepository $userRepo,$id)
    {
        $form = $this->createForm(RegistrationFormType::class , $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Profile Updated !');
            return $this->redirectToRoute('account');
        }
        $user = $userRepo->findBy(
            ['id' => $id]
        );


        return $this->render('home/editlogin.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
            'users' => $user,
        ]);
    }

}
