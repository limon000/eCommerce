<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Form\ClientType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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
         if ($this->getUser()) {
             $this->addFlash('logged' , 'You are already logged in !!');
           return $this->redirectToRoute('home');
         }

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
    public function edit(Client $client,Request $request,EntityManagerInterface $manager)
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

        return $this->render('home/profile.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/profile/{id}/edit_login", name="profile_edit_login")
     */
    public function editAccount(UserPasswordEncoderInterface $passwordEncoder,User $user,Request $request,EntityManagerInterface $manager)
    {
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
     * @Route("/reset", name="reset")
     */
    public function reset(ObjectManager $manager,MailerInterface $mailer,Request $request,UserRepository $userRepo,UserPasswordEncoderInterface $passwordEncoder)
    {
        if($request->isMethod('POST')){
            $email = $request->request->get('emailReset');
            $ver = $userRepo->findOneBy([
                'email' => $email,
            ]);
            if($ver)
            {
                $email = new Email();
                $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                $pass = substr(str_shuffle($permitted_chars), 0, 10);
                $email = (new Email())
                    ->from('aymenradhouen@gmail.com')
                    ->to($ver->getEmail())
                    ->subject("Your new password !")
                    ->text("Your new password is : ".$pass);
                $mailer->send($email);
                $ver->setPassword($passwordEncoder->encodePassword($ver,$pass));

                $manager->persist($ver);
                $manager->flush();
                $this->addFlash('emailSuccess', 'Email Found , Go to your email');
            }
            else {
                $this->addFlash('emailFail', 'No Email Found');
            }

        }


        return $this->render('home/resetPassword.html.twig');
    }

}
