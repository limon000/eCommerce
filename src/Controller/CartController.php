<?php

namespace App\Controller;

use App\Service\Panier\PanierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        $this->addFlash('ProductSuccess', 'Product Added Successfully');
        return $this->redirectToRoute("panier");
    }

    /**
     * @Route("/panier/remove/{id}" , name="panier_remove")
     */
    public function remove($id,PanierService $panierService)
    {
        $panierService->remove($id);
        $this->addFlash('ProductFail', 'Product Deleted Successfully');


        return $this->redirectToRoute('panier');
    }


}
