<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Details;
use App\Repository\ArticleRepository;
use App\Service\Panier\PanierService;
use App\Service\Stripe\StripeClient;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @Route("/payment", name="payment", schemes={"%secure_channel%"})
     */
    public function index(ArticleRepository $articleRepo,MailerInterface $mailer,Request $request,PanierService $panierService,SessionInterface $session,StripeClient $stripeClient,ObjectManager $manager)
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
                $stripeClient->createInvoiceItem($item['article']->getPrix() * $item['quantite']* 100,$user,$item['article']->getNom());
            }

            $stripeClient->createInvoice($user,true);

            $commande = new Commande();
            foreach ($panierService->getFullCart() as $item)
            {
                $details = new Details();
                $article = $articleRepo->findOneBy([
                    'id' => $item['article'],
                ]);
                $article->setQuantite($article->getQuantite()-$item['quantite']);

                $commande->setClient($this->getUser()->getClient())
                         ->setStatus("Completed")
                         ->setOrderDate(new \DateTime())
                         ->setOrderTotal($panierService->getTotal());
                $manager->persist($commande);
                $details->setArticles($item['article'])
                        ->setCommandes($commande)
                        ->setQuantite($item['quantite']);
                $manager->persist($details);
            }
            $email = (new Email())
                ->to($this->getUser()->getEmail())
                ->from("aymenradhouen@gmail.com")
                ->subject("Order Details")
                ->text(
                    sprintf("Status : %s ,  Order Total : %s",$commande->getStatus(), $commande->getOrderTotal())
                );
            $mailer->send($email);

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
