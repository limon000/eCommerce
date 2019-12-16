<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\User;
use App\Form\CancelType;
use App\Form\ClientType;
use App\Form\UserType;
use App\Repository\ArticleRepository;
use App\Repository\ClientRepository;
use App\Repository\CommandeRepository;
use App\Repository\DetailsRepository;
use App\Repository\UserRepository;
use App\Service\Stripe\StripeClient;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_ADMIN")
 *  @Route("/admin")
 */
class AdminController extends AbstractController
{
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
    public function orderDetail(ObjectManager $manager,StripeClient $stripeClient,Request $request,DetailsRepository $detailRepo,Commande $commande)
    {
        \Stripe\Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $detail = $detailRepo->findBy([
            'commandes' => $commande,
        ]);

        $form = $this->createForm(CancelType::class);
        $form->handleRequest($request);
        if($form->isSubmitted())
        {
            foreach ($detail as $item)
            {
                $article = $item->getArticles();
                $articleQte = $item->getArticles()->getQuantite();
                $comQte = $item->getQuantite();
                $article->setQuantite($articleQte+$comQte);
                $manager->persist($article);
                $manager->flush();
            }

            $stripeClient->createRefund($commande->getInvoiceId(),$commande->getOrderTotal()*100);
            $commande->setStatus("Canceled");
            $manager->persist($commande);
            $manager->flush();

        }

        return $this->render('admin/order/detail.html.twig',[
            'commande' => $commande,
            'details' => $detail,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/client/{id}",name="clientDetail")
     */
    public function clientDetail(Client $client,Request $request,ObjectManager $manager)
    {
        $form = $this->createForm(ClientType::class,$client);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($client);
            $manager->flush();
        }
        return $this->render('admin/clientDetail.html.twig',[
            'form' => $form->createView(),
            'client' => $client,
        ]);
    }

    /**
     * @Route("/client/{id}/orders",name="clientOrders")
     */
    public function clientOrder(Client $client,CommandeRepository $order)
    {
        $orders = $order->findBy([
            'client' => $client,
        ]);
        return $this->render('admin/clientOrder.html.twig',[
            'orders' => $orders,
        ]);
    }


}
