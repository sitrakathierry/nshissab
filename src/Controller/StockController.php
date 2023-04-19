<?php

namespace App\Controller;

use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AbstractController
{
    private $entityManager;
    private $session ;
    private $appService ;
    public function __construct(EntityManagerInterface $entityManager,SessionInterface $session, AppService $appService)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->appService = $appService ;
    }
    
    #[Route('/stock/creationproduit', name: 'stock_creationproduit')]
    public function stockCreationproduit(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 
        
        return $this->render('stock/creationproduit.html.twig', [
            "filename" => "stock",
            "titlePage" => "Création Produit",
            "with_foot" => true
        ]);
    }

    #[Route('/stock/general', name: 'stock_general')]
    public function stockGeneral(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/stockgeneral.html.twig', [
            "filename" => "stock",
            "titlePage" => "Stock Général",
            "with_foot" => false
        ]);
    }

    #[Route('/stock/categorie/creation', name: 'stock_cat_creation')]
    public function stockCatCreation(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/categorie/creation.html.twig', [
            "filename" => "stock",
            "titlePage" => "Création Catégorie",
            "with_foot" => true
        ]);
    }

    #[Route('/stock/categorie/consultation', name: 'stock_cat_consultation')]
    public function stockCatConsultation(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/categorie/consultation.html.twig', [
            "filename" => "stock",
            "titlePage" => "Liste des Catégories de Produit",
            "with_foot" => false
        ]);
    }

    #[Route('/stock/preferences', name: 'stock_preferences')]
    public function stockPreferences(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/preferences.html.twig', [
            "filename" => "stock",
            "titlePage" => "Préférences",
            "with_foot" => false
        ]);
    }

    
    #[Route('/stock/inventaire', name: 'stock_inventaire')]
    public function stockInventaire(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/inventaire.html.twig', [
            "filename" => "stock",
            "titlePage" => "Inventaire des Produits",
            "with_foot" => false
        ]);
    }

    #[Route('/stock/entrepot', name: 'stock_entrepot')]
    public function stockEntrepot(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/entrepot.html.twig', [
            "filename" => "stock",
            "titlePage" => "Entrepot",
            "with_foot" => true
        ]);
    }

    #[Route('/stock/fournisseur', name: 'stock_fournisseur')]
    public function stockFournisseur(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/fournisseur.html.twig', [
            "filename" => "stock",
            "titlePage" => "Fournisseur",
            "with_foot" => true
        ]);
    }

    #[Route('/stock/stockentrepot', name: 'stock_stockentrepot')]
    public function stockStockentrepot(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/stockentrepot.html.twig', [
            "filename" => "stock",
            "titlePage" => "Stock d'entrepot",
            "with_foot" => false
        ]);
    }

    #[Route('/stock/stockinterne/libellee', name: 'stock_int_libellee')]
    public function stockIntLibellee(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/stockinterne/libellee.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/creation', name: 'stock_int_creation')]
    public function stockIntCreation(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/stockinterne/creation.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/stock', name: 'stock_int_stock')]
    public function stockIntStock(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/stockinterne/stock.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/approvisionnement', name: 'stock_int_approvisionnement')]
    public function stockIntApprovisionnement(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/stockinterne/approvisionnement.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/sorties', name: 'stock_int_sorties')]
    public function stockIntSorties(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/stockinterne/sorties.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/entreesortie', name: 'stock_int_entreesortie')]
    public function stockIntEntreesortie(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/stockinterne/entreesortie.html.twig', [
            
        ]);
    }

    #[Route('/stock/approvisionnement/ajouter', name: 'stock_appr_ajouter')]
    public function stockApprAjouter(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/approvisionnement/ajouter.html.twig', [
            "filename" => "stock",
            "titlePage" => "Approvisionnement des Produits",
            "with_foot" => true
        ]);
    }

    #[Route('/stock/approvisionnement/liste', name: 'stock_appr_liste')]
    public function stockApprListe(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 

        return $this->render('stock/approvisionnement/liste.html.twig', [
            "filename" => "stock",
            "titlePage" => "Liste des approvisionnements",
            "with_foot" => false
        ]);
    }
}
