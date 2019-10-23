<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class AdminController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/admin", name="admin")
     */
    public function admin()
    {

        return $this->render('admin/home_admin.html.twig');
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/admin/users", name="admin_users")
     */
    public function create(GuardAuthenticatorHandler $guardHandler,Request $request,ObjectManager $manager,UserPasswordEncoderInterface $passwordEncoder,LoginFormAuthenticator $authenticator)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'User tried to access a page without having ROLE_ADMIN');
        $client = new Client();
        $user = new User();
        $form = $this->createForm(UserType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $client->setFullname("null");
            $client->setBirthdate(new \DateTime('now'));
            $client->setAddress1("null");
            $client->setAddress2("null");
            $client->setCity("null");
            $client->setState("null");
            $client->setPostcode("null");
            $client->setUser($user);
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->persist($client);
            $manager->flush();

            return $this->redirectToRoute('admin_users');
        }



            return $this->render('admin/admin.html.twig' , [
                'adminForm' => $form->createView(),
            ]);
    }



}
