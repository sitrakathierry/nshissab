<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AbstractController
{
    #[Route('/stock/creationproduit', name: 'stock_creationproduit')]
    public function stockCreationproduit(): Response
    {
        return $this->render('stock/creationproduit.html.twig', [
            "filename" => "stock",
            "titlePage" => "Création Produit",
            "with_foot" => true
        ]);
    }

    #[Route('/stock/general', name: 'stock_general')]
    public function stockGeneral(): Response
    {
        return $this->render('stock/stockgeneral.html.twig', [
            "filename" => "stock",
            "titlePage" => "Stock Général",
            "with_foot" => false
        ]);
    }

    #[Route('/stock/categorie/creation', name: 'stock_cat_creation')]
    public function stockCatCreation(): Response
    {
        return $this->render('stock/categorie/creation.html.twig', [
            "filename" => "stock",
            "titlePage" => "Création Catégorie",
            "with_foot" => true
        ]);
    }

    #[Route('/stock/categorie/consultation', name: 'stock_cat_consultation')]
    public function stockCatConsultation(): Response
    {
        return $this->render('stock/categorie/consultation.html.twig', [
            
        ]);
    }

    #[Route('/stock/preferences', name: 'stock_preferences')]
    public function stockPreferences(): Response
    {
        return $this->render('stock/preferences.html.twig', [
            
        ]);
    }

    
    #[Route('/stock/inventaire', name: 'stock_inventaire')]
    public function stockInventaire(): Response
    {
        return $this->render('stock/inventaire.html.twig', [
            
        ]);
    }

    #[Route('/stock/entrepot', name: 'stock_entrepot')]
    public function stockEntrepot(): Response
    {
        return $this->render('stock/entrepot.html.twig', [
            
        ]);
    }

    #[Route('/stock/fournisseur', name: 'stock_fournisseur')]
    public function stockFournisseur(): Response
    {
        return $this->render('stock/fournisseur.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockentrepot', name: 'stock_stockentrepot')]
    public function stockStockentrepot(): Response
    {
        return $this->render('stock/stockentrepot.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/libellee', name: 'stock_int_libellee')]
    public function stockIntLibellee(): Response
    {
        return $this->render('stock/stockinterne/libellee.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/creation', name: 'stock_int_creation')]
    public function stockIntCreation(): Response
    {
        return $this->render('stock/stockinterne/creation.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/stock', name: 'stock_int_stock')]
    public function stockIntStock(): Response
    {
        return $this->render('stock/stockinterne/stock.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/approvisionnement', name: 'stock_int_approvisionnement')]
    public function stockIntApprovisionnement(): Response
    {
        return $this->render('stock/stockinterne/approvisionnement.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/sorties', name: 'stock_int_sorties')]
    public function stockIntSorties(): Response
    {
        return $this->render('stock/stockinterne/sorties.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/entreesortie', name: 'stock_int_entreesortie')]
    public function stockIntEntreesortie(): Response
    {
        return $this->render('stock/stockinterne/entreesortie.html.twig', [
            
        ]);
    }
}
