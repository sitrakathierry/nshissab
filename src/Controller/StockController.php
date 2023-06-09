<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\PrdApprovisionnement;
use App\Entity\PrdCategories;
use App\Entity\PrdEntrepot;
use App\Entity\PrdFournisseur;
use App\Entity\PrdHistoEntrepot;
use App\Entity\PrdHistoFournisseur;
use App\Entity\PrdMargeType;
use App\Entity\PrdPreferences;
use App\Entity\PrdVariationPrix;
use App\Entity\Produit;
use App\Entity\User;
use App\Service\AppService;
use App\Service\ExcelGenService;
use App\Service\PdfGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AbstractController
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
        $this->filename = "files/systeme/stock/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"] 
        ]) ;
    }
    
    #[Route('/stock/creationproduit', name: 'stock_creationproduit')]
    public function stockCreationproduit(): Response
    {
        $filename = $this->filename."preference(user)/".$this->nameUser.".json" ;
        $preferences = json_decode(file_get_contents($filename)) ;

        $filename = $this->filename."entrepot(agence)/".$this->nameAgence ;
        $entrepots = (json_decode(file_get_contents($filename))) ;

        $filename = $this->filename."fournisseur(agence)/".$this->nameAgence ;
        $fournisseurs = json_decode(file_get_contents($filename)) ;
        
        $marge_types = $this->entityManager->getRepository(PrdMargeType::class)->findBy([
            'agence' => $this->agence 
        ]) ;

        return $this->render('stock/creationproduit.html.twig', [
            "filename" => "stock",
            "titlePage" => "Création Produit",
            "with_foot" => true,
            "categories" => $preferences,
            "entrepots" => $entrepots,
            "fournisseurs" => $fournisseurs,
            "marge_types" => $marge_types 
        ]);
    }

    
    #[Route('/stock/creationproduit/save', name: 'stock_save_creationProduit')]
    public function stockSaveCreationProduit(Request $request)
    {
        $codeProduit = $request->request->get('code_produit') ;
        $produitcChk = $this->entityManager->getRepository(Produit::class)->findOneBy([
            "codeProduit" => $codeProduit
        ]) ;

        if(!empty($codeProduit) && !is_null($produitcChk))
        {
            return new JsonResponse([
                "title" => "Code existant",
                "message" => "Veuillez changer le code car elle existe déjà",
                "type" => "orange"
            ]) ;
        }

        $prod_categorie = $request->request->get('prod_categorie') ;
        $code_produit = $request->request->get('code_produit') ;
        $prod_nom = $request->request->get('prod_nom') ;
        $unite_produit = $request->request->get('unite_produit') ;
        $produit_editor = $request->request->get('produit_editor') ;
        $qr_code_produit = $request->request->get('qr_code_produit') ;

        $data = [
            $prod_categorie,
            $code_produit,
            $prod_nom,
            $unite_produit,
        ];

        $dataMessage = [
            "Catégorie",
            "Code Produit",
            "Nom",
            "Unité"
        ] ;

        $result = $this->appService->verificationElement($data,$dataMessage) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $crt_code = (array)$request->request->get('crt_code') ;
        $crt_indice = $request->request->get('crt_indice') ;
        $crt_entrepot = $request->request->get('crt_entrepot') ;
        $crt_prix_achat = $request->request->get('crt_prix_achat') ;
        $crt_prix_revient = $request->request->get('crt_prix_revient') ;
        $crt_calcul = $request->request->get('crt_calcul') ; // Marge Type
        $crt_prix_vente = $request->request->get('crt_prix_vente') ;
        $crt_stock_alert = $request->request->get('crt_stock_alert') ;
        $crt_charge = $request->request->get('crt_charge') ;
        $crt_marge = $request->request->get('crt_marge') ;
        $crt_stock = $request->request->get('crt_stock') ;
        $crt_expiree_le = $request->request->get('crt_expiree_le') ;
        
        $tableau = [] ;
        foreach ($crt_code as $key => $value) {
            $data = [
                $crt_entrepot[$key],
                $crt_prix_achat[$key],
                $crt_prix_revient[$key],
                $crt_prix_vente[$key],
                $crt_stock_alert[$key],
                $crt_charge[$key],
                $crt_marge[$key],
                $crt_stock[$key]
            ];
    
            $dataMessage = [
                "Entrepot",
                "Prix Achat",
                "Prix Revient",
                "Prix Vente",
                "Stock Alert",
                "Charge",
                "Marge",
                "Stock",
            ] ;

            $result = $this->appService->verificationElement($data,$dataMessage) ;

            if(!$result["allow"])
                return new JsonResponse($result) ;
            
            $uniteTableau = [] ;
            
            $uniteTableau["entrepot"] = $crt_entrepot[$key] ;
            $uniteTableau["indice"] = $crt_indice[$key] ;
            $uniteTableau["prix_vente"] = $crt_prix_vente[$key] ;

            array_push($tableau,$uniteTableau) ;
        }

        $doublons = $this->appService->detecter_doublons($tableau) ;

        if(!empty($doublons))
            return new JsonResponse([
                "message" => "Veuiller vérifier vos variations de produit car il y a des doublons (Entrepot, Indice et Prix de Vente)" ,
                "type" => "orange"
            ]) ;

        $produit = new Produit() ;
        
        $preference = $this->entityManager->getRepository(PrdPreferences::class)->find($prod_categorie) ;  

        $produit->setAgence($this->agence) ;
        $produit->setPreference($preference) ;
        $produit->setUser($this->userObj) ;
        $produit->setCodeProduit($code_produit) ;
        $produit->setQrCode($qr_code_produit) ;
        $produit->setImages(null) ;
        $produit->setNom($prod_nom) ;
        $produit->setDescription($produit_editor) ;
        $produit->setUnite($unite_produit) ;
        $produit->setStock(null) ;
        $produit->setStatut(True) ;
        $produit->setCreatedAt(new \DateTimeImmutable) ;
        $produit->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($produit) ;
        $this->entityManager->flush() ;

        $stockProduit = 0 ;
        $indice = 0 ;
        foreach ($crt_code  as $key => $value) {
            $variationPrix = new PrdVariationPrix() ;

            $variationPrix->setProduit($produit) ;
            $variationPrix->setPrixVente($crt_prix_vente[$key]) ;
            $variationPrix->setStock($crt_stock[$key]) ;
            $variationPrix->setStockAlert($crt_stock_alert[$key]) ;
            $variationPrix->setStatut(True) ;
            $variationPrix->setCreatedAt(new \DateTimeImmutable) ;
            $variationPrix->setUpdatedAt(new \DateTimeImmutable) ;

            $this->entityManager->persist($variationPrix) ;
            $this->entityManager->flush() ;

            $histoEntrepot = new PrdHistoEntrepot() ;

            $entrepot = $this->entityManager->getRepository(PrdEntrepot::class)->find($crt_entrepot[$key]) ; 
            if(empty($crt_indice[$key]))
                $crt_indice[$key] = null ;
            
            if(empty($crt_expiree_le[$key]))
                $crt_expiree_le[$key] = null ;

            $histoEntrepot->setEntrepot($entrepot) ;
            $histoEntrepot->setVariationPrix($variationPrix) ;
            $histoEntrepot->setIndice($crt_indice[$key]) ;
            $histoEntrepot->setStock($crt_stock[$key]) ;
            $histoEntrepot->setStatut(True) ;
            $histoEntrepot->setAgence($this->agence) ;
            $histoEntrepot->setCreatedAt(new \DateTimeImmutable) ;
            $histoEntrepot->setUpdatedAt(new \DateTimeImmutable) ;

            $this->entityManager->persist($histoEntrepot) ;
            $this->entityManager->flush() ;

            $approvisionnement = new PrdApprovisionnement() ;

            $margeType = $this->entityManager->getRepository(PrdMargeType::class)->find($crt_calcul[$key]) ;

            $approvisionnement->setUser($this->userObj) ;
            $approvisionnement->setHistoEntrepot($histoEntrepot) ;
            $approvisionnement->setVariationPrix($variationPrix) ;
            $approvisionnement->setMargeType($margeType) ;
            $approvisionnement->setQuantite($crt_stock[$key]) ;
            $approvisionnement->setPrixAchat($crt_prix_achat[$key]) ;
            $approvisionnement->setCharge($crt_charge[$key]) ;
            $approvisionnement->setMargeValeur($crt_marge[$key]) ;
            $approvisionnement->setPrixRevient($crt_prix_revient[$key]) ;
            $approvisionnement->setPrixVente($crt_prix_vente[$key]) ;
            $expirer = !empty($crt_expiree_le[$key]) ? \DateTime::createFromFormat('j/m/Y', $crt_expiree_le[$key]) : null;
            $approvisionnement->setExpireeLe($expirer) ;
            $approvisionnement->setDateAppro(null) ;
            $approvisionnement->setDescription("Création de Produit Code : ".$crt_code[$key]) ;
            $approvisionnement->setCreatedAt(new \DateTimeImmutable) ;
            $approvisionnement->setUpdatedAt(new \DateTimeImmutable) ;

            $this->entityManager->persist($approvisionnement) ;
            $this->entityManager->flush() ;

            $crt_fournisseur = (array)$request->request->get('crt_fournisseur') ;
            $crt_count_fournisseur = $request->request->get('crt_count_fournisseur') ;

            for ($i=$indice; $i < $crt_count_fournisseur[$key] + $indice; $i++) { 
                $histoFournisseur = new PrdHistoFournisseur() ;
                $fournisseur = $this->entityManager->getRepository(PrdFournisseur::class)->find($crt_fournisseur[$i][0]) ;
                
                $histoFournisseur->setFournisseur($fournisseur) ;
                $histoFournisseur->setApprovisionnement($approvisionnement) ;
                $histoFournisseur->setCreatedAt(new \DateTimeImmutable) ;
                $histoFournisseur->setUpdatedAt(new \DateTimeImmutable) ;

                $this->entityManager->persist($histoFournisseur) ;
                $this->entityManager->flush() ;
            } 
            
            $indice += $crt_count_fournisseur[$key];

            $stockProduit += $crt_stock[$key] ;
        }

        $produit->setStock($stockProduit) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."stock_general(agence)/".$this->nameAgence ;
        unlink($filename) ;


        $filename1 = $this->filename."stock_entrepot(agence)/".$this->nameAgence ;
        unlink($filename1) ;

        return new JsonResponse($result) ;

    }

    #[Route('/stock/creationproduit/code/check', name: 'stock_check_codeProduit')]
    public function stockCheckCodeProduit(Request $request)
    {
        $codeProduit = $request->request->get('codeProduit') ;
        $produit = $this->entityManager->getRepository(Produit::class)->findOneBy([
            "codeProduit" => $codeProduit
        ]) ;

        if(!is_null($produit))
        {
            return new JsonResponse([
                "title" => "Code existant",
                "message" => "Veuillez changer le code car elle existe déjà",
                "type" => "orange"
            ]) ;
        }

        return new JsonResponse([
            "type" => "green"
        ]) ;
    }

    #[Route('/stock/general', name: 'stock_general')]
    public function stockGeneral(): Response
    {
        $filename = $this->filename."preference(user)/".$this->nameUser.".json" ;
        $preferences = json_decode(file_get_contents($filename)) ;

        $filename = $this->filename."stock_general(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateProduitStockGeneral($filename, $this->agence) ;

        $stockGenerales = json_decode(file_get_contents($filename)) ;
        
        $societe = $this->agence->getNom() ;
 
        return $this->render('stock/stockgeneral.html.twig', [
            "filename" => "stock",
            "titlePage" => "Stock Général",
            "with_foot" => false,
            "societe" => $societe,
            "categories" => $preferences,
            "stockGenerales" => $stockGenerales
        ]);
    }

    #[Route('/stock/general/ticket/excel', name: 'stock_general_excel_ticket')]
    public function stockExcelTicketStockGeneral(ExcelGenService $excelgenerate)
    {
        $entete = ["PRODUITS","PRIX"] ;
        $params = [] ;
        $produit = $this->entityManager->getRepository(Produit::class)->findBy([
            "agence" => $this->agence,
            "statut" => True
        ]) ;
        foreach ($produit as $produit) {
            $variationPrix = $this->entityManager->getRepository(PrdVariationPrix::class)->findBy([
                "produit" => $produit,
                "statut" => True
            ]) ; 
            
            foreach ($variationPrix as $variationPrix) {
                $produitElem = $variationPrix->getProduit()->getCodeProduit()." | ".$variationPrix->getProduit()->getNom() ;
                $prix = $variationPrix->getPrixVente() ;
                array_push($params,[$produitElem,$prix]) ;
            }
        } 
        $excelgenerate->generateExcelFile($entete,$params,'TICKET_PRODUITS.xlsx') ;
        return new Response("") ;
    }
    
    #[Route('/stock/general/search', name: 'stock_search_stock_general')]
    public function stockGeneralSearch(Request $request)
    {
        $filename = $this->filename."stock_general(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateProduitStockGeneral($filename, $this->agence) ;

        $stockGenerales = json_decode(file_get_contents($filename)) ;

        $id = $request->request->get('id') ;
        $idC = $request->request->get('idC') ;

        $search = [
            "id" => $id,
            "idC" => $idC
        ] ;

        $stockGenerales = $this->appService->searchData($stockGenerales,$search) ;

        $response = $this->renderView("stock/searchStockGenerales.html.twig", [
            "stockGenerales" => $stockGenerales
        ]) ;

        return new Response($response) ; 
    }

    #[Route('/stock/categorie/creation/{id}', name: 'stock_cat_creation', defaults:['id' => null])]
    public function stockCatCreation($id): Response
    {
        
        $categorie = [] ;
        if(!is_null($id))
        {
            $user = $this->session->get("user") ;
            $agence = $this->entityManager->getRepository(Agence::class)->find($user["agence"]) ; 

            $filename = "files/systeme/stock/categorie(agence)/".strtolower($agence->getNom())."-".$agence->getId().".json" ;
            if(!file_exists($filename))
                $this->appService->generateStockCategorie($filename, $agence) ;
            $categories = json_decode(file_get_contents($filename)) ;
            foreach ($categories as $cat) {
                if($cat->id == $id)
                {
                    $categorie = $cat ;
                    break ;
                }
            }
            
        }

        return $this->render('stock/categorie/creation.html.twig', [
            "filename" => "stock",
            "titlePage" => "Création Catégorie",
            "with_foot" => true,
            "categorie" => $categorie
        ]);
    }

    #[Route('/stock/categorie/save',name: 'stock_save_categorie')]
    public function stockCategorieSave(Request $request)
    {
        $image = $request->request->get('image') ;
        $nom = $request->request->get('nom') ;
        $id = $request->request->get('id') ;

        $user = $this->session->get("user") ;
        $agence = $this->entityManager->getRepository(Agence::class)->find($user["agence"]) ; 

        $base64Image = explode(";base64,",$image) ;
        if(!isset($base64Image[1]))
        {
            $image = "" ;
        }

        $reponse = [] ;
        if(empty($id))
        {
            $categorie = new PrdCategories() ;
            $reponse["message"] = "Catégorie ajoutée" ;
            $reponse["type"] = "green" ;
        }
        else
        {
            $categorie = $this->entityManager->getRepository(PrdCategories::class)->find($id) ; 
            $reponse["message"] = "Mise à jour terminée" ;
            $reponse["type"] = "dark" ;
        }

        $categorie->setAgence($agence) ;
        $categorie->setImages($image) ;
        $categorie->setNom($nom) ;
        $categorie->setCreatedAt(new \DateTimeImmutable) ;
        $categorie->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($categorie);
        $this->entityManager->flush();

        $filename = "files/systeme/stock/categorie(agence)/".strtolower($agence->getNom())."-".$agence->getId().".json" ;
        $this->appService->generateStockCategorie($filename, $agence) ;

        return new JsonResponse($reponse) ;
    }

    #[Route('/stock/categorie/delete',name:'stock_delete_categorie')]
    public function stockCategorieDelete(Request $request)
    {
        $id = $request->request->get("id") ;
        $categorie = $this->entityManager->getRepository(PrdCategories::class)->find($id) ; 

        $this->entityManager->remove($categorie);
        $this->entityManager->flush();

        $user = $this->session->get("user") ;
        $agence = $this->entityManager->getRepository(Agence::class)->find($user["agence"]) ; 

        $filename = "files/systeme/stock/categorie(agence)/".strtolower($agence->getNom())."-".$agence->getId().".json" ;
        $this->appService->generateStockCategorie($filename, $agence) ;

        return new JsonResponse(["message" => "Catégorie supprimée", "type" => "green"]) ;
    }   

    #[Route('/stock/categorie/consultation/{nom}', name: 'stock_cat_consultation', defaults : ["nom" => null])]
    public function stockCatConsultation($nom): Response
    {
        $user = $this->session->get("user") ;
        $agence = $this->entityManager->getRepository(Agence::class)->find($user["agence"]) ; 

        $filename = "files/systeme/stock/categorie(agence)/".strtolower($agence->getNom())."-".$agence->getId().".json" ;
        if(!file_exists($filename))
        {   
            $this->appService->generateStockCategorie($filename, $agence) ;
        }
        $categories = json_decode(file_get_contents($filename)) ;

        if(!is_null($nom))
        {
            $responses = $this->appService->searchData($categories,[$nom]) ;
            $categories = $responses ;
        }
        return $this->render('stock/categorie/consultation.html.twig', [
            "filename" => "stock",
            "titlePage" => "Liste des Catégories de Produit",
            "with_foot" => false,
            "categories" => $categories
        ]);
    }

    #[Route('/stock/preferences', name: 'stock_preferences')]
    public function stockPreferences(): Response
    {
        $filename = $this->filename."categorie(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
        {   
            $this->appService->generateStockCategorie($filename, $this->agence) ;
        }
        $categories = json_decode(file_get_contents($filename)) ;

        $preferences = $this->appService->filterProdPreferences($this->filename,$this->nameAgence,$this->nameUser,$this->userObj) ;
        
        return $this->render('stock/preferences.html.twig', [
            "filename" => "stock",
            "titlePage" => "Préférences",
            "with_foot" => false,
            "categories" => $categories,
            "preferences" => $preferences
        ]);
    }

    #[Route('/stock/preferences/save', name: 'stock_save_prefs')]
    public function stockSavePrefs(Request $request)
    {
        $preferences = (array)$request->request->get('preferences') ;
        $preferences = explode(",",$preferences[0]) ;

        foreach ($preferences as $key => $value) {
            $preference = new PrdPreferences() ;
            $categorie = $this->entityManager->getRepository(PrdCategories::class)->find($value) ;

            $preference->setCategorie($categorie) ;
            $preference->setUser($this->userObj) ;
            $preference->setStatut(True) ;
            $preference->setCreatedAt(new \DateTimeImmutable) ;
            $preference->setUpdatedAt(new \DateTimeImmutable) ;

            $this->entityManager->persist($preference) ;
            $this->entityManager->flush() ;
        }

        $filename = $this->filename."preference(user)/".$this->nameUser.".json" ;
        $this->appService->generateStockPreferences($filename,$this->userObj) ;

        $dataPreferences = json_decode(file_get_contents($filename)) ;

        $result = $this->renderView("stock/searchPreferences.html.twig",[
            "preferences" => $dataPreferences
        ]) ;

        return new Response($result) ;
    }
    
    #[Route('/stock/preferences/delete', name: 'stock_delete_prefs')]
    public function stockDeletePrefs(Request $request)
    {
        $id = $request->request->get('id') ;

        $preference = $this->entityManager->getRepository(PrdPreferences::class)->find($id) ;

        $this->entityManager->remove($preference) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."preference(user)/".$this->nameUser.".json" ;
        $this->appService->generateStockPreferences($filename,$this->userObj) ;

        return new JsonResponse([
            "message" => "Suppression effectuée",
            "type" => "green"
        ]) ;
    }
    
    
    #[Route('/stock/inventaire', name: 'stock_inventaire')]
    public function stockInventaire(): Response
    {
        

        return $this->render('stock/inventaire.html.twig', [
            "filename" => "stock",
            "titlePage" => "Inventaire des Produits",
            "with_foot" => false
        ]);
    }
    
    #[Route('/stock/entrepot/produit/get', name: 'stock_get_produit_et_entrepot')]
    public function stockGetEntrepotProduit()
    {
        $filename = $this->filename."entrepot(agence)/".$this->nameAgence ;
        if(!file_exists($filename))  
            $this->appService->generateStockEntrepot($filename,$this->agence) ;
        
        $entrepots = json_decode(file_get_contents($filename)) ;

        $filename = $this->filename."stock_general(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateProduitStockGeneral($filename, $this->agence) ;

        $stockGenerales = json_decode(file_get_contents($filename)) ;

        $filename = $this->filename."fournisseur(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateStockFournisseur($filename,$this->agence) ;

        $fournisseurs = json_decode(file_get_contents($filename)) ;

        $response = [
            "entrepots" => $entrepots,
            "stockGenerales" => $stockGenerales,
            "fournisseurs" => $fournisseurs
        ] ;

        return new JsonResponse($response) ;
    }

    #[Route('/stock/entrepot/produit/prix/get', name: 'stock_get_prix_produitE')]
    public function stockGetPrixProduitsE(Request $request)
    {
        $idE = $request->request->get('idE') ;
        $idP = $request->request->get('idP') ; 

        $prixProduits = $this->entityManager->getRepository(PrdHistoEntrepot::class)->getPrixProduitsE($idE, $idP) ;

        return new JsonResponse($prixProduits) ;
    }

    #[Route('/stock/produit/variation/details', name: 'stock_details_variation_prix')]
    public function stockDetailsVariationProduit(Request $request)
    {
        $idVar = $request->request->get('idVar') ;

        $result = [] ;

        $approvisionnement = $this->entityManager->getRepository(PrdApprovisionnement::class)->getLastApproVariationPrix($idVar) ;

        $histoFournisseur = $this->entityManager->getRepository(PrdHistoFournisseur::class)->findBy([
            "approvisionnement" => $approvisionnement
        ]) ;

        $fournisseur = [] ;
        foreach ($histoFournisseur as $histoFournisseur) {
            array_push($fournisseur,$histoFournisseur->getFournisseur()->getId()) ;
        }

        $result["prixAchat"] = $approvisionnement->getPrixAchat() ; 
        $result["charge"] = $approvisionnement->getCharge() ; 
        $result["calcul"] = $approvisionnement->getMargeType()->getId() ; 
        $result["marge"] = $approvisionnement->getMargeValeur() ; 
        $result["prixRevient"] = $approvisionnement->getPrixRevient() ; 
        $result["prixVente"] = $approvisionnement->getPrixVente() ; 
        $result["expireeLe"] = is_null($approvisionnement->getExpireeLe()) ? "" : $approvisionnement->getExpireeLe()->format("d/m/Y") ; 
        $result["fournisseur"] = $fournisseur ; 

        return new JsonResponse($result) ;
    }

    #[Route('/stock/entrepot/produit/find', name: 'stock_find_produit_in_entrepot')]
    public function stockFindProduitInEntrepot(Request $request)
    {
        $idE = $request->request->get('idE') ;

        $produitEntrepots = $this->entityManager->getRepository(PrdHistoEntrepot::class)->getProduitsInEntrepots($idE) ;
        
        $result = [] ;
        $result["vide"] = empty($produitEntrepots) ;
        $result["produitEntrepots"] = $produitEntrepots ;
        return new JsonResponse($result) ;
    }

    #[Route('/stock/entrepot', name: 'stock_entrepot')]
    public function stockEntrepot(): Response
    {
        
        $filename = $this->filename."entrepot(agence)/".$this->nameAgence ;
        if(!file_exists($filename))  
            $this->appService->generateStockEntrepot($filename,$this->agence) ;
        
        $entrepots = json_decode(file_get_contents($filename)) ;

        return $this->render('stock/entrepot.html.twig', [
            "filename" => "stock",
            "titlePage" => "Entrepot",
            "with_foot" => true,
            "entrepots" => $entrepots
        ]);
    }

    #[Route('/stock/entrepot/save', name: 'stock_save_entrepot')]
    public function stockSaveEntrepot(Request $request)
    {
        $nom = $request->request->get("nom") ;
        $adresse = $request->request->get("adresse") ;
        $telephone = $request->request->get("telephone") ;

        $data = [
            $nom,
            $adresse,
            $telephone,
        ] ;

        $dataMessage = [
            "Nom",
            "Adresse",
            "Téléphone",
        ] ;

        $result = $this->appService->verificationElement($data,$dataMessage) ;
        if(!$result["allow"])
            return new JsonResponse($result) ;

        $id = $request->request->get('id') ;

        if(!isset($id))
        {
            $entrepot = new PrdEntrepot() ;
            $entrepot->setCreatedAt(new \DateTimeImmutable) ;
        }
        else
        {
            $entrepot = $this->entityManager->getRepository(PrdEntrepot::class)->find($id) ;
        }

        $entrepot->setNom($nom) ;
        $entrepot->setAdresse($adresse) ;
        $entrepot->setTelephone($telephone) ;
        $entrepot->setAgence($this->agence) ;
        $entrepot->setUpdatedAt(new \DateTimeImmutable) ;
        
        $this->entityManager->persist($entrepot);
        $this->entityManager->flush();
        
        $this->appService->generateStockEntrepot($this->filename."entrepot(agence)/".$this->nameAgence,$this->agence) ;

        return new JsonResponse($result) ;
    }
    
    #[Route('/stock/entrepot/edit', name: 'stock_edit_entrepot')]
    public function stockEditEntrepot(Request $request)
    {
        $id = $request->request->get('id') ;
        $filename = $this->filename."entrepot(agence)/".$this->nameAgence ;
        $entrepots = json_decode(file_get_contents($filename)) ;
        $result = [] ;
        foreach ($entrepots as $entrepot) {
            if($entrepot->id == $id)
            {
                $result = $entrepot ;
                break ;
            }
        } 

        return new JsonResponse($result) ;
    }

    #[Route('/stock/entrepot/delete', name: 'stock_delete_entrepot')]
    public function stockDeleteEntrepot(Request $request)
    {
        $id = $request->request->get('id') ;

        $entrepot = $this->entityManager->getRepository(PrdEntrepot::class)->find($id) ;

        $entrepot->setStatut(False) ;
        // $this->entityManager->remove($entrepot);
        $this->entityManager->flush();

        $this->appService->generateStockEntrepot($this->filename."entrepot(agence)/".$this->nameAgence,$this->agence) ;
        
        return new JsonResponse([
            "message" => "Suppression effectuée",
            "type" => "green"
        ]) ;
    }

    #[Route('/stock/entrepot/search', name:'stock_search_entrepot')]
    public function stockSearchEntrepot(Request $request)
    {
        $nom = $request->request->get('nom') ;
        $adresse = $request->request->get('adresse') ;
        $telephone = $request->request->get('telephone') ;

        $filename = $this->filename."entrepot(agence)/".$this->nameAgence ;
        $entrepots =(array)(json_decode(file_get_contents($filename))) ;
        
        $datas = [
            "nom" => $nom,
            "adresse" => $adresse,
            "telephone" => $telephone
        ] ;

        $entrepots = $this->appService->searchData($entrepots,$datas) ; 

        $result = $this->renderView('stock/entrepot/search.html.twig',[
            "entrepots" => $entrepots
        ]) ;

        return new Response($result) ;
    }

    #[Route('/stock/fournisseur', name: 'stock_fournisseur')]
    public function stockFournisseur(): Response
    {
        $filename = $this->filename."fournisseur(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateStockFournisseur($filename,$this->agence) ;
        $fournisseurs = json_decode(file_get_contents($filename)) ;
        return $this->render('stock/fournisseur.html.twig', [
            "filename" => "stock",
            "titlePage" => "Fournisseur",
            "with_foot" => true,
            "fournisseurs" => $fournisseurs
        ]);
    }
    
    #[Route('/stock/fournisseur/save', name: 'stock_save_fournisseur')]
    public function stockSaveFournisseur(Request $request)
    {
        $frns_nom = $request->request->get('frns_nom') ; 
        $frns_tel_bureau = $request->request->get('frns_tel_bureau') ; 
        $frns_adresse = $request->request->get('frns_adresse') ; 
        $frns_nom_contact = $request->request->get('frns_nom_contact') ; 
        $frns_tel_mobile = $request->request->get('frns_tel_mobile') ; 
        $frns_email = $request->request->get('frns_email') ; 

        $data = [
            $frns_nom,
            $frns_tel_bureau,
            $frns_adresse,
            $frns_nom_contact,
            $frns_tel_mobile,
            $frns_email,
        ] ;

        $dataMessage = [
            "Nom",
            "Tél Bureau",
            "Adresse",
            "Nom Contact",
            "Tél Mobile",
            "Email"
        ] ;

        $result = $this->appService->verificationElement($data,$dataMessage) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;
        
        $fournisseur = new PrdFournisseur() ;

        $fournisseur->setNom($frns_nom) ;
        $fournisseur->setTelBureau($frns_tel_bureau) ;
        $fournisseur->setAdresse($frns_adresse) ;
        $fournisseur->setNomContact($frns_nom_contact) ;
        $fournisseur->setTelMobile($frns_tel_mobile,) ;
        $fournisseur->setEmail($frns_email) ;
        $fournisseur->setStatut(True) ;
        $fournisseur->setAgence($this->agence) ;
        $fournisseur->setCreatedAt(new \DateTimeImmutable) ;
        $fournisseur->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($fournisseur) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."fournisseur(agence)/".$this->nameAgence ;
        $this->appService->generateStockFournisseur($filename,$this->agence) ;
        
        $fournisseurs = json_decode(file_get_contents($filename)) ;

        $response = $this->renderView("stock/searchFournisseur.html.twig", [
            "fournisseurs" => $fournisseurs
        ]) ;

        return new Response($response) ;
    }

    #[Route('/stock/stockentrepot', name: 'stock_stockentrepot')]
    public function stockStockentrepot(): Response
    {
        $filename = $this->filename."preference(user)/".$this->nameUser.".json" ;
        $preferences = json_decode(file_get_contents($filename)) ;

        $filename = $this->filename."stock_general(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateProduitStockGeneral($filename, $this->agence) ;

        $stockGenerales = json_decode(file_get_contents($filename)) ;

        $filename = $this->filename."entrepot(agence)/".$this->nameAgence ;
        if(!file_exists($filename))  
            $this->appService->generateStockEntrepot($filename,$this->agence) ;

        $entrepots = json_decode(file_get_contents($filename)) ; 

        $filename = $this->filename."stock_entrepot(agence)/".$this->nameAgence ;
        if(!file_exists($filename))  
            $this->appService->generateStockInEntrepot($filename, $this->agence);

        $stockEntrepots = json_decode(file_get_contents($filename)) ; 

        return $this->render('stock/stockentrepot.html.twig', [
            "filename" => "stock",
            "titlePage" => "Stock d'entrepot",
            "with_foot" => false,
            "entrepots" => $entrepots,
            "categories" => $preferences,
            "stockGenerales" => $stockGenerales,
            "stockEntrepots" => $stockEntrepots
        ]);
    }

    #[Route('/stock/stockentrepot/search', name: 'stock_search_stock_entrepot')]
    public function stockStockInEntrepotSearch(Request $request)
    {
        $filename = $this->filename."stock_entrepot(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateStockInEntrepot($filename, $this->agence) ;

        $stockEntrepots = json_decode(file_get_contents($filename)) ;

        $idE = $request->request->get('idE') ;
        $idC = $request->request->get('idC') ;
        $idP = $request->request->get('idP') ;

        $search = [
            "idE" => $idE,
            "idC" => $idC,
            "idP" => $idP
        ] ;

        $stockEntrepots = $this->appService->searchData($stockEntrepots,$search) ;

        $response = $this->renderView("stock/searchStockEntrepots.html.twig", [
            "stockEntrepots" => $stockEntrepots
        ]) ;

        return new Response($response) ; 
    }

    #[Route('/stock/produit/prix/get', name: 'stock_get_produit_prix')]
    public function stockGetProduitPrix(Request $request)
    {
        $idP = $request->request->get('idP') ;

        $produitPrix = $this->entityManager->getRepository(PrdVariationPrix::class)->getProdtuiPrixParIndice($idP);
        
        $produit = $this->entityManager->getRepository(Produit::class)->find($idP) ;

        $tva = $produit->getTvaType() ;

        return new JsonResponse([
            "produitPrix" => $produitPrix,
            "tva" => is_null($tva) ? "" : $tva->getValeur() 
        ]) ;
    }

    

    #[Route('/stock/produit/get', name: 'stock_get_produit')]
    public function stockGetProduit()
    {
        $filename = $this->filename."stock_general(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateProduitStockGeneral($filename, $this->agence) ;
        
        $stockGenerales = json_decode(file_get_contents($filename)) ;

        return new JsonResponse($stockGenerales) ;
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
 
    #[Route('/stock/approvisionnement/ajouter', name: 'stock_appr_ajouter')]
    public function stockApprAjouter(): Response
    {
        $filename = $this->filename."stock_general(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateProduitStockGeneral($filename, $this->agence) ;

        $stockGenerales = json_decode(file_get_contents($filename)) ;

        $filename = $this->filename."entrepot(agence)/".$this->nameAgence ;
        if(!file_exists($filename))  
            $this->appService->generateStockEntrepot($filename,$this->agence) ;

        $entrepots = json_decode(file_get_contents($filename)) ; 

        $filename = $this->filename."fournisseur(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateStockFournisseur($filename,$this->agence) ;
        $fournisseurs = json_decode(file_get_contents($filename)) ;


        return $this->render('stock/approvisionnement/ajouter.html.twig', [
            "filename" => "stock",
            "titlePage" => "Approvisionnement des Produits",
            "with_foot" => true,
            "stockGenerales" => $stockGenerales,
            "entrepots" => $entrepots,
            "fournisseurs" => $fournisseurs
        ]);
    }

    #[Route('/stock/approvisionnement/save', name: 'stock_save_approvisionnement')]
    public function stockSaveApprovisionnement(Request $request)
    {
        $enr_ref_entrepot = $request->request->get('enr_ref_entrepot') ;
        $enr_appro_type = (array)$request->request->get('enr_appro_type') ;
        $enr_ref_appro_produit = $request->request->get('enr_ref_appro_produit') ;
        $enr_appro_indice = $request->request->get('enr_appro_indice') ;
        $enr_appro_fournisseur = $request->request->get('enr_appro_fournisseur') ;
        $enr_appro_expireeLe = $request->request->get('enr_appro_expireeLe') ;
        $enr_appro_quantite = $request->request->get('enr_appro_quantite') ;
        $enr_appro_prix_achat = $request->request->get('enr_appro_prix_achat') ;
        $enr_appro_charge = $request->request->get('enr_appro_charge') ;
        $enr_appro_prix_revient = $request->request->get('enr_appro_prix_revient') ;
        $enr_appro_calcul = $request->request->get('enr_appro_calcul') ;
        $enr_appro_marge = $request->request->get('enr_appro_marge') ;
        $enr_appro_prix_vente = $request->request->get('enr_appro_prix_vente') ;

        $enr_appro_date = $request->request->get('enr_appro_date') ; // NOT AN ARRAY !!!
        foreach ($enr_appro_type as $key => $value) {  
            // Etape 1 : Afficher tous les prix de produit dans PrdVariationPrix à partir de l'idProduit
            $produit = $this->entityManager->getRepository(Produit::class)->find($enr_ref_appro_produit[$key]) ;
            
            $produit->setStock($produit->getStock() + intval($enr_appro_quantite[$key])) ;
            $produit->setUpdatedAt(new \DateTimeImmutable) ;
            $this->entityManager->flush() ;

            $variationPrix = $this->entityManager->getRepository(PrdVariationPrix::class)->findOneBy([
                "produit" => $produit,
                "prixVente" => $enr_appro_prix_vente[$key]
            ]);

            if(!is_null($variationPrix))
            {
                $variationPrix->setStock($variationPrix->getStock() + intval($enr_appro_quantite[$key])) ;
                $variationPrix->setUpdatedAt(new \DateTimeImmutable) ;
                $this->entityManager->flush() ;

                $indice = empty($enr_appro_indice[$key]) ? null : $enr_appro_indice[$key] ;
                $entrepot = $this->entityManager->getRepository(PrdEntrepot::class)->find($enr_ref_entrepot[$key]) ;
                
                $histoEntrepot = $this->entityManager->getRepository(PrdHistoEntrepot::class)->findOneBy([
                    "entrepot" => $entrepot,
                    "variationPrix" => $variationPrix,
                    "indice" => $indice
                ]) ;

                $histoEntrepot->setStock($histoEntrepot->getStock() + intval($enr_appro_quantite[$key])) ;
                $histoEntrepot->setUpdatedAt(new \DateTimeImmutable) ;
                $this->entityManager->flush() ;
            }
            else
            {
                $variationPrix = new PrdVariationPrix() ;

                $variationPrix->setProduit($produit) ;
                $variationPrix->setPrixVente($enr_appro_prix_vente[$key]) ;
                $variationPrix->setStock($enr_appro_quantite[$key]) ;
                $variationPrix->setStockAlert(10) ;
                $variationPrix->setStatut(True) ;
                $variationPrix->setCreatedAt(new \DateTimeImmutable) ;
                $variationPrix->setUpdatedAt(new \DateTimeImmutable) ;

                $this->entityManager->persist($variationPrix) ;
                $this->entityManager->flush() ;

                $histoEntrepot = new PrdHistoEntrepot() ;

                $entrepot = $this->entityManager->getRepository(PrdEntrepot::class)->find($enr_ref_entrepot[$key]) ; 
                if(empty($enr_appro_indice[$key]))
                    $enr_appro_indice[$key] = null ;
                
                if(empty($crt_expiree_le[$key]))
                    $crt_expiree_le[$key] = null ;

                $histoEntrepot->setEntrepot($entrepot) ;
                $histoEntrepot->setVariationPrix($variationPrix) ;
                $histoEntrepot->setIndice($enr_appro_indice[$key]) ;
                $histoEntrepot->setStock($enr_appro_quantite[$key]) ;
                $histoEntrepot->setStatut(True) ;
                $histoEntrepot->setAgence($this->agence) ;
                $histoEntrepot->setCreatedAt(new \DateTimeImmutable) ;
                $histoEntrepot->setUpdatedAt(new \DateTimeImmutable) ;

                $this->entityManager->persist($histoEntrepot) ;
                $this->entityManager->flush() ;
            }

            $approvisionnement = new PrdApprovisionnement() ;
            $margeType = $this->entityManager->getRepository(PrdMargeType::class)->find($enr_appro_calcul[$key]) ;

            $approvisionnement->setUser($this->userObj) ;
            $approvisionnement->setHistoEntrepot($histoEntrepot) ;
            $approvisionnement->setVariationPrix($variationPrix) ;
            $approvisionnement->setMargeType($margeType) ;
            $approvisionnement->setQuantite($enr_appro_quantite[$key]) ;
            $approvisionnement->setPrixAchat($enr_appro_prix_achat[$key]) ;
            $approvisionnement->setCharge($enr_appro_charge[$key]) ;
            $approvisionnement->setMargeValeur($enr_appro_marge[$key]) ;
            $approvisionnement->setPrixRevient($enr_appro_prix_revient[$key]) ;
            $approvisionnement->setPrixVente($enr_appro_prix_vente[$key]) ;
            $expirer = !empty($crt_expiree_le[$key]) ? \DateTime::createFromFormat('j/m/Y',$enr_appro_expireeLe[$key]) : null;
            $approvisionnement->setExpireeLe($expirer) ;
            $dateTimeDateAppro = \DateTime::createFromFormat('j/m/Y',$enr_appro_date);
            $approvisionnement->setDateAppro($dateTimeDateAppro) ; 
            $approvisionnement->setDescription("Approvisionnement de Produit Code : ".$produit->getCodeProduit()) ;
            $approvisionnement->setCreatedAt(new \DateTimeImmutable) ;
            $approvisionnement->setUpdatedAt(new \DateTimeImmutable) ;
            
            $this->entityManager->persist($approvisionnement) ;
            $this->entityManager->flush() ;

            if(!empty($enr_appro_fournisseur[$key]))
            {
                if(strlen($enr_appro_fournisseur[$key]) == 1)
                {
                    $histoFournisseur = new PrdHistoFournisseur() ;
                    $fournisseur = $this->entityManager->getRepository(PrdFournisseur::class)->find($enr_appro_fournisseur[$key]) ;
                    $histoFournisseur->setFournisseur($fournisseur) ;
                    $histoFournisseur->setApprovisionnement($approvisionnement) ;
                    $histoFournisseur->setCreatedAt(new \DateTimeImmutable) ;
                    $histoFournisseur->setUpdatedAt(new \DateTimeImmutable) ;

                    $this->entityManager->persist($histoFournisseur) ;
                    $this->entityManager->flush() ;
                }
                else
                {
                    $fournisseurArray = explode(",",$enr_appro_fournisseur[$key]) ;
                    for ($i=0; $i < count($fournisseurArray) ; $i++) { 
                        $histoFournisseur = new PrdHistoFournisseur() ;
                        $fournisseur = $this->entityManager->getRepository(PrdFournisseur::class)->find($fournisseurArray[$i]) ;

                        $histoFournisseur->setFournisseur($fournisseur) ;
                        $histoFournisseur->setApprovisionnement($approvisionnement) ;
                        $histoFournisseur->setCreatedAt(new \DateTimeImmutable) ;
                        $histoFournisseur->setUpdatedAt(new \DateTimeImmutable) ;
        
                        $this->entityManager->persist($histoFournisseur) ;
                        $this->entityManager->flush() ;
                    }
                }
            }
        }
        
        $filename = $this->filename."stock_general(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;

        $filename = $this->filename."stock_entrepot(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse([
            "type" => "green",
            "message" => "Approvisionnement effectué"
        ]) ;

    }

    #[Route('/stock/approvisionnement/liste', name: 'stock_appr_liste')]
    public function stockApprListe(): Response
    {
        

        return $this->render('stock/approvisionnement/liste.html.twig', [
            "filename" => "stock",
            "titlePage" => "Liste des approvisionnements",
            "with_foot" => false
        ]);
    }
}
