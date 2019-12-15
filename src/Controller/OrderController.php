<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Details;
use App\Form\CancelType;
use App\Repository\ArticleRepository;
use App\Repository\ClientRepository;
use App\Repository\CommandeRepository;
use App\Repository\DetailsRepository;
use App\Service\Panier\PanierService;
use App\Service\Stripe\StripeClient;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
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

            $invoice = $stripeClient->createInvoice($user,true);

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
                         ->setOrderTotal($panierService->getTotal())
                         ->setInvoiceId($invoice->charge);
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
            return $this->redirectToRoute('confirm',['id' => $commande->getId()]);
        }

        return $this->render('payment/payment.html.twig',[
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/commande/{id}",name="confirm")
     */
    public function comfirmation(ObjectManager $manager,StripeClient $stripeClient,Request $request,DetailsRepository $detailRepo,Commande $commande)
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

        return $this->render('order/order.html.twig',[
            'commande' => $commande,
            'details' => $detail,
            'form' => $form->createView(),
        ]);
    }
}
