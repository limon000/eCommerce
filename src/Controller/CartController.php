<?php

namespace App\Controller;

use App\Service\Panier\PanierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{

    /**
     * @Route("/panier", name="panier")
     */
    public function panier(PanierService $panierService)
    {

        return $this->render('article/panier.html.twig', [
            'items' => $panierService->getFullCart(),
            'total' => $panierService->getTotal(),
        ]);
    }

    /**
     * @Route("/panier/add/{id}" , name="panier_add")
     */
    public function add($id,PanierService $panierService,Request $request)
    {

        $panierService->add($id,$request);
        return $this->redirectToRoute("panier");
    }

    /**
     * @Route("/panier/remove/{id}" , name="panier_remove")
     */
    public function remove($id,PanierService $panierService)
    {
        $panierService->remove($id);

        return $this->redirectToRoute('panier');
    }

    /**
     * @Route("/payment", name="payment")
     */
    public function index(Request $request,PanierService $panierService,SessionInterface $session)
    {
        \Stripe\Stripe::setApiKey('sk_test_slLLiq3g14CyTcksAsrHfJl300x94MIOOM');

        $token = $request->request->get('stripeToken');
        $charge = \Stripe\Charge::create([
            'amount' => $panierService->getTotal()*100,
            'currency' => 'usd',
            'source' => 'tok_visa',
            'receipt_email' => 'jenny.rosen@example.com',
        ]);

        return $this->render('payment/payment.html.twig');
    }




}
