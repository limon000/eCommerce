<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\ArticleRepository;
use App\Repository\ClientRepository;
use App\Repository\CommandeRepository;
use App\Repository\DetailsRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @IsGranted("ROLE_ADMIN")
 *  @Route("/admin")
 */
class AdminController extends AbstractController
{

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/user", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        $user = $userRepository->findAll();

        return $this->render('admin/user/index.html.twig', [
            'users' => $user,
        ]);
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/user/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request,UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'User tried to access a page without having ROLE_ADMIN');
        $client = new Client();
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client->setFullname("null");
            $client->setBirthdate(new \DateTime('now'));
            $client->setAddress1("null");
            $client->setAddress2("null");
            $client->setCity("null");
            $client->setState("null");
            $client->setPostcode("null");
            $user->setClient($client);

            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/user/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/user/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user,UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/user/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }


        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/", name="admin")
     */
    public function admin(ClientRepository $clientRepo,CommandeRepository $comRepo,UserRepository $userRepo,ArticleRepository $articleRepo) : Response
    {
        $users = $userRepo->findAll();
        $articles = $articleRepo->findAll();
        $comCompleted = $comRepo->findBy([
            'status' => "Completed"
        ]);
        $comCanceled = $comRepo->findBy([
            'status' => "Canceled"
        ]);

        $somme = $comRepo->orderSum("Completed");

        $client = $clientRepo->findAll();
        $commande = $comRepo->findAll();




        return $this->render('admin/home_admin.html.twig',[
            'users' => $users,
            'articles' => $articles,
            'completed' => $comCompleted,
            'canceled' => $comCanceled,
            'somme' => $somme,
            'clients'=> $client,
            'commande' => $commande,
        ]);
    }

    /**
     * @Route("/orders", name="orders")
     */
    public function order(CommandeRepository $order)
    {
        $orders = $order->findAll();
        return $this->render('admin/order/index.html.twig',[
            'orders' => $orders,
        ]);
    }

    /**
     * @Route("/orders/detail/{id}", name="orderDetail")
     */
    public function orderDetail(DetailsRepository $detailRepo,Commande $commande)
    {

        $detail = $detailRepo->findBy([
            'commandes' => $commande,
        ]);
        return $this->render('admin/order/detail.html.twig',[
            'commande' => $commande,
            'details' => $detail,
        ]);
    }

}
