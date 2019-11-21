<?php

namespace App\Controller;

use App\Entity\Order;
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
            $order = new Order();
                foreach ($panierService->getFullCart() as $item) {
                          $order->setArticle($item['article'])
                                ->setQuantity($item['quantite'])
                                ->setClient($user->getClient())
                                ->setFullname($user->getClient()->getFullname())
                                ->setStatus('Completed')
                                ->setOrderDate(new \DateTime())
                                ->setOrdertotal($panierService->getTotal());
                        }
            $manager->persist($order);
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
