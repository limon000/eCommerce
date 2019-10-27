<?php

namespace App\Service\Panier;

use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PanierService {

    protected $session;
    protected $articleRepo;
    public function __construct(SessionInterface $session,ArticleRepository $articleRepo)
    {
        $this->session = $session;
        $this->articleRepo = $articleRepo;
    }

    public function add(int $id) {
        $panier = $this->session->get('panier', []);


        if(!empty($panier[$id]))
        {
            $panier[$id]++;
        }
        else {
            $panier[$id] = 1;
        }
        $this->session->set('panier', $panier);
    }

    public function remove(int $id) {
        $panier = $this->session->get('panier', []);
        if(!empty($panier[$id]))
        {
            unset($panier[$id]);
        }
        $this->session->set('panier' , $panier);
    }

    public function getFullCart() : array {
        $panier = $this->session->get('panier', []);
        $panierWithData = [];
        foreach ($panier as $id => $quantite)
        {
            $panierWithData[] = [
                'article' => $this->articleRepo->find($id),
                'quantite' => $quantite,
            ];
        }
        return $panierWithData;
    }

    public function getTotal() : float {
        $total = 0;
        foreach ($this->getFullCart() as $item)
        {
            $total += $item['article']->getPrix() * $item['quantite'];
        }
        return $total;
    }

}