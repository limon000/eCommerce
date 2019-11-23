<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Service\Panier\PanierService;
use App\Service\Stripe\StripeClient;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @Route("/payment", name="payment", schemes={"%secure_channel%"})
     */
    public function index(Request $request,PanierService $panierService,SessionInterface $session,StripeClient $stripeClient,ObjectManager $manager)
    {
        $session->get('panier', []);

        if($request->isMethod('POST')) {
            $token = $request->get('stripeToken');
            \Stripe\Stripe::setApiKey($this->getParameter('stripe_secret_key'));


            $user = $this->getUser();

            if(!$user->getStripeCustomerId())
            {
                $stripeClient->createCustomer($user,$token);
            } else {
                $stripeClient->updateCustomerCard($user,$token);
            }
            foreach ($panierService->getFullCart() as $item) {
                $stripeClient->createInvoiceItem($item['article']->getPrix() * 100,$user,$item['article']->getNom());
            }

            $stripeClient->createInvoice($user,true);
            $commande = new Commande();
            foreach ($panierService->getFullCart() as $item)
            {
                $commande->setClient($this->getUser()->getClient())
                         ->setArticles($item['article'])
                         ->setDescription($item['article']->getNom())
                         ->setQuantity($item['quantite'])
                         ->setStatus("Completed")
                         ->setOrderDate(new \DateTime())
                         ->setOrderTotal($panierService->getTotal());
            }
            $manager->persist($commande);
            $manager->flush();


            $session->clear('panier');
            $this->addFlash('PaymentSuccess', 'Order Complete !');
            return $this->redirectToRoute('home');
        }



        return $this->render('payment/payment.html.twig',[
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
        ]);
    }
}
