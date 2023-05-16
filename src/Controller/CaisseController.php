<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\CaisseCommande;
use App\Entity\CaissePanier;
use App\Entity\PrdHistoEntrepot;
use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CaisseController extends AbstractController
{
    private $entityManager;
    private $session ;
    private $appService ;
    private $agence ;
    private $user ;
    private $filename ; 
    private $nameAgence ; 
    private $nameUser ; 
    private $userObj ; 
    public function __construct(EntityManagerInterface $entityManager,SessionInterface $session, AppService $appService)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->appService = $appService ;
        $this->appService->checkUrl() ;
        $this->user = $this->session->get("user") ;
        $this->agence = $this->entityManager->getRepository(Agence::class)->find($this->user["agence"]) ; 
        $this->filename = "files/systeme/caisse/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"] 
        ]) ;
    }
    
    #[Route('/caisse', name: 'caisse_activity')]
    public function index(): Response
    {
        $filename = "files/systeme/stock/stock_general(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateProduitStockGeneral($filename, $this->agence) ;

        $stockGenerales = json_decode(file_get_contents($filename)) ;
        return $this->render('caisse/caisse.html.twig', [
            "filename" => "caisse",
            "titlePage" => "Vente des Produits",
            "with_foot" => true,
            "stockGenerales" => $stockGenerales
        ]);
    }

    
    #[Route('/caisse/activity/save', name: 'caisse_save_activites')]
    public function caisseSaveActivity(Request $request)
    {
        $cs_mtn_recu = $request->request->get('cs_mtn_recu') ; 
        $csenr_total_general = $request->request->get('csenr_total_general') ; 
        $csenr_date_caisse = $request->request->get('csenr_date_caisse') ; 
        
        $lastRecordCommande = $this->entityManager->getRepository(CaisseCommande::class)->findOneBy([], ['id' => 'DESC']);

        $numCommande = !is_null($lastRecordCommande) ? ($lastRecordCommande->getId()+1) : 1 ;

        $numCommande = str_pad($numCommande, 5, "0", STR_PAD_LEFT);

        $commande = new CaisseCommande() ;
        
        $commande->setAgence($this->agence) ;
        $commande->setUser($this->userObj) ; 
        $commande->setNumCommande($numCommande) ;
        $commande->setMontantRecu($cs_mtn_recu) ;
        $commande->setMontantPayee($csenr_total_general) ;
        $dateTime = \DateTimeImmutable::createFromFormat('d/m/Y', $csenr_date_caisse);
        $commande->setDate($dateTime) ;
        $commande->setStatut(True) ;
        $commande->setCreatedAt(new \DateTimeImmutable) ;
        $commande->setUpdatedAt(new \DateTimeImmutable) ;
        
        $this->entityManager->persist($commande) ;
        $this->entityManager->flush() ;

        $csenr_produit = (array)$request->request->get('csenr_produit') ; 
        $csenr_prix = $request->request->get('csenr_prix') ; 
        $csenr_prixText = $request->request->get('csenr_prixText') ; 
        $csenr_quantite = $request->request->get('csenr_quantite') ; 

        foreach ($csenr_produit as $key => $value) {
            $panier = new CaissePanier() ;

            $histoEntrepot = $this->entityManager->getRepository(PrdHistoEntrepot::class)->find($csenr_prix[$key]) ;

            $panier->setCommande($commande) ;
            $panier->setHistoEntrepot($histoEntrepot) ;
            $panier->setPrix(intval($csenr_prixText[$key])) ;
            $panier->setQuantite($csenr_quantite[$key]) ;
            $panier->setStatut(True) ;

            $this->entityManager->persist($panier) ;
            $this->entityManager->flush() ;
        } 

        $filename = $this->filename."panierCommande(agence)/".$this->nameAgence ; 
        if(file_exists($filename))
            unlink($filename) ;
        $this->appService->generateCaissePanierCommande($filename, $this->agence->getId()) ; 

        $filename = $this->filename."commande(agence)/".$this->nameAgence ; 
        if(file_exists($filename))
            unlink($filename) ;
        $this->appService->generateCaisseCommande($filename, $this->agence) ;

        return new JsonResponse([
            "type" => "green",
            "message" => "Enregistrement éffectué"
        ]) ;
    }

    #[Route('/caisse/vente/liste', name: 'caisse_liste_vente')]
    public function caisseListeVente()
    {
        $filename = $this->filename."panierCommande(agence)/".$this->nameAgence ; 
        if(!file_exists($filename))   
            $this->appService->generateCaissePanierCommande($filename, $this->agence->getId()) ; 
        
        $paniersCommande = json_decode(file_get_contents($filename)) ;

        $filename = "files/systeme/stock/stock_general(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateProduitStockGeneral($filename, $this->agence) ;

        $stockGenerales = json_decode(file_get_contents($filename)) ;

        $filename = $this->filename."commande(agence)/".$this->nameAgence ; 
        if(!file_exists($filename))
            $this->appService->generateCaisseCommande($filename, $this->agence) ;

        $commandes = json_decode(file_get_contents($filename)) ;

        return $this->render('caisse/vente.html.twig', [
            "filename" => "caisse",
            "titlePage" => "Liste des ventes",
            "with_foot" => false,
            "stockGenerales" => $stockGenerales,
            "paniersCommande" => $paniersCommande,
            "commandes" => $commandes
        ]);
    }
}
