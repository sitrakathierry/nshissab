<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\CaisseCommande;
use App\Entity\CaissePanier;
use App\Entity\FactDetails;
use App\Entity\FactHistoPaiement;
use App\Entity\Facture;
use App\Entity\SavAnnulation;
use App\Entity\SavDetails;
use App\Entity\SavMotif;
use App\Entity\SavSpec;
use App\Entity\SavType;
use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SAVController extends AbstractController
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
        $this->filename = "files/systeme/sav/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"] 
        ]) ;
    }
    
    #[Route('/sav', name: 'app_s_a_v')]
    public function index(): Response
    {
        // $filename = "files/systeme/facture/facture(agence)/".$this->nameAgence ;

        // if(!file_exists($filename))
        //     $this->appService->generateFacture($filename, $this->agence) ;

        // $factures = json_decode(file_get_contents($filename)) ;
        
        // $search = [
        //     "numFact" => "DF"
        // ] ;
            
        // $factures = $this->appService->searchData($factures,$search) ;
        
        $this->appService->synchronisationServiceApresVente(["CAISSE","FACTURE"]) ;
        
        $types = $this->entityManager->getRepository(SavType::class)->findAll() ;

        $specs = $this->entityManager->getRepository(SavSpec::class)->findBy([],["rang" => "ASC"]) ;

        $filename = $this->filename."motif(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->genererSavMotif($filename,$this->agence) ;

        $motifs = json_decode(file_get_contents($filename)) ;

        $filename = $this->filename."annulation(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateSavAnnulation($filename,$this->agence) ;
        
        $annulations = json_decode(file_get_contents($filename)) ;

        $search = [
            "refSpec" => "AVR"
        ] ;

        $avoirs = $this->appService->searchData($annulations,$search) ;

        // Tableau regroupé par idC
        $tableauRegroupe = [];

        // Parcourir chaque élément du tableau initial
        foreach ($avoirs as $element) {
            $idC = $element->idC;
            // Vérifier si la clé idC existe déjà dans le tableau regroupé
            if (array_key_exists($idC, $tableauRegroupe)) {
                // Ajouter l'élément au tableau existant pour cette clé idC
                $tableauRegroupe[$idC][] = $element;
            } else {
                // Créer un nouveau tableau pour cette clé idC
                $tableauRegroupe[$idC] = [$element];
            }
        }
        
        $avoirs = [] ;
        foreach ($tableauRegroupe as $key => $value) {
            $item = [] ;
            $item["remboursee"] = 0 ;
            foreach ($tableauRegroupe[$key] as $element) {
                $item["client"] = $element->client ;
                $item["remboursee"] += floatval($element->remboursee) ;
            }

            $item["idC"] = $key ;

            array_push($avoirs,$item) ;
        } 

        $filename = "files/systeme/facture/facture(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateFacture($filename, $this->agence) ;

        $factures = json_decode(file_get_contents($filename)) ;

        $search = [
            "numFact" => "DF"
        ] ;
            
        $factures = $this->appService->searchData($factures,$search) ;

        return $this->render('sav/index.html.twig', [
            "filename" => "sav",
            "titlePage" => "Service Après Vente",
            "with_foot" => true,
            "types" => $types,
            "specs" => $specs,
            "motifs" => $motifs,
            "annulations" => $annulations ,
            "avoirs" => $avoirs,
            "factures" => $factures,
        ]);
    }
     
    #[Route('/sav/creation/motif', name: 'sav_creation_motif')]
    public function savCreationMotif()
    {
        $filename = $this->filename."motif(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->genererSavMotif($filename,$this->agence) ;

        $motifs = json_decode(file_get_contents($filename)) ;

        return $this->render('sav/creationMotif.html.twig', [
            "filename" => "sav",
            "titlePage" => "Motif",
            "with_foot" => false,
            "motifs" => $motifs
        ]);
    }

    #[Route('/sav/motif/update', name: 'sav_update_motif')]
    public function savUpdateMotif(Request $request)
    {
        $id = $request->request->get('id') ;
        $filename = $this->filename."motif(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->genererSavMotif($filename,$this->agence) ;
        $motifs = json_decode(file_get_contents($filename)) ;
        $result = [] ;
        foreach ($motifs as $motif) {
            if($motif->id == $id)
            {
                $result = $motif ;
                break ;
            }
        } 

        return new JsonResponse($result) ;
    }

    #[Route('/sav/motif/delete', name: 'sav_delete_motif')]
    public function savDeleteMotif(Request $request)
    {
        $id = $request->request->get('id') ;

        $motif = $this->entityManager->getRepository(SavMotif::class)->find($id) ;

        $motif->setStatut(False) ;
        // $this->entityManager->remove($entrepot);
        $this->entityManager->flush();

        $this->appService->genererSavMotif($this->filename."motif(agence)/".$this->nameAgence,$this->agence) ;
        
        return new JsonResponse([
            "message" => "Suppression effectuée",
            "type" => "green"
        ]) ;
    }

    #[Route('/sav/motif/save', name: 'sav_save_motif')]
    public function savSaveMotif(Request $request)
    {
        $sav_motif_nom = $request->request->get("sav_motif_nom") ;

        $data = [
            $sav_motif_nom,
        ] ;

        $dataMessage = [
            "Nom"
        ] ;

        $result = $this->appService->verificationElement($data,$dataMessage) ;
        if(!$result["allow"])
            return new JsonResponse($result) ;

        $id = $request->request->get('id') ;

        if(!isset($id))
        {
            $motif = new SavMotif() ;
        }
        else
        {
            $motif = $this->entityManager->getRepository(SavMotif::class)->find($id) ;
        }

        $motif->setNom($sav_motif_nom) ;
        $motif->setStatut(True) ;
        $motif->setAgence($this->agence) ;
        
        $this->entityManager->persist($motif);
        $this->entityManager->flush();

        $filename = $this->filename."motif(agence)/".$this->nameAgence ;
        $this->appService->genererSavMotif($filename,$this->agence) ;

        return new JsonResponse($result) ;
    }

    #[Route('/sav/facture/display', name: 'sav_facture_display')]
    public function savDisplayFacture(Request $request)
    {
        $idF = $request->request->get('idF') ; 

        $facture = $this->entityManager->getRepository(Facture::class)->find($idF) ;
        
        $infoFacture = [] ;

        $infoFacture["numFact"] = $facture->getNumFact() ;
        $infoFacture["modele"] = $facture->getModele()->getNom() ;
        $infoFacture["type"] = $facture->getType()->getNom() ;
        $infoFacture["date"] = $facture->getDate()->format("d/m/Y") ;
        $infoFacture["lieu"] = $facture->getLieu() ;

        $infoFacture["devise"] = !is_null($facture->getDevise()) ;

        if(!is_null($facture->getDevise()))
        {
            $infoFacture["deviseCaption"] = $facture->getDevise()->getLettre() ;
            $infoFacture["deviseValue"] = number_format($facture->getTotal()/$facture->getDevise()->getMontantBase(),2,",","")." ".$facture->getDevise()->getSymbole();
        }

        $histoPaiement = $this->entityManager->getRepository(FactHistoPaiement::class)->findOneBy([
            "facture" => $facture
        ]) ;
        
        $infoFacture["paiement"] = is_null($histoPaiement->getPaiement()) ? "-" : $histoPaiement->getPaiement()->getNom();
        
        if(!is_null($histoPaiement->getPaiement()))
        {
            $infoFacture["infoSup"] = !is_null($histoPaiement->getPaiement()->getNumCaption())  ;

            if(!is_null($histoPaiement->getPaiement()->getNumCaption()))
            {
                $infoFacture["numeroCaption"] = $histoPaiement->getPaiement()->getNumCaption() ;
                $infoFacture["numerovalue"] = $histoPaiement->getNumero() ;
                $infoFacture["libelleCaption"] =  $histoPaiement->getPaiement()->getLibelleCaption() ;
                $infoFacture["libelleValue"] = $histoPaiement->getLibelle() ;
            }

        }
        else
        {
            $infoFacture["infoSup"] = false ;
        }

        if($facture->getClient()->getType()->getId() == 2)
            $infoFacture["client"] = $facture->getClient()->getClient()->getNom() ;
        else
            $infoFacture["client"] = $facture->getClient()->getSociete()->getNom() ;
        
        $infoFacture["totalTva"] = ($facture->getTvaVal() == 0) ? "-" : $facture->getTvaVal();
        $infoFacture["totalTtc"] = $facture->getTotal() ;

        $factureDetails = $this->entityManager->getRepository(FactDetails::class)->findBy([
            "facture" => $facture
        ]) ;
        
        $totalHt = 0 ;
        $elements = [] ;

        foreach ($factureDetails as $factureDetail) {
            $tva = (($factureDetail->getPrix() * $factureDetail->getTvaVal()) / 100) * $factureDetail->getQuantite();
            $total = $factureDetail->getPrix() * $factureDetail->getQuantite()  ;

            if(!is_null($factureDetail->getRemiseType()))
            {
                if($factureDetail->getRemiseType()->getId() == 1)
                {
                    $remise = ($total * $factureDetail->getRemiseVal()) / 100 ; 
                }
                else
                {
                    $remise = $factureDetail->getRemiseVal() ;
                }
            }
            else
            {
                $remise = 0 ;
            }
            
            $total = $total - $remise ;

            $element = [] ;
            $element["id"] = $factureDetail->getId() ;
            $element["type"] = $factureDetail->getActivite() ;
            $element["designation"] = $factureDetail->getDesignation() ;
            $element["quantite"] = $factureDetail->getQuantite() ;
            $element["format"] = "-" ;
            $element["prix"] = $factureDetail->getPrix() ;
            $element["tva"] = ($tva == 0) ? "-" : $tva ;
            $element["typeRemise"] = is_null($factureDetail->getRemiseType()) ? "-" : $factureDetail->getRemiseType()->getNotation() ;
            $element["valRemise"] = $factureDetail->getRemiseVal() ;
            $element["total"] = $total ;
            $element["statut"] = $factureDetail->isStatut() ;
            array_push($elements,$element) ;
            if($factureDetail->isStatut())
                $totalHt += $total ;
        } 

        $infoFacture["totalHt"] = $totalHt ;

        if(!is_null($facture->getRemiseType()))
        {
            if($facture->getRemiseType()->getId() == 1)
            {
                $remiseG = ($totalHt * $facture->getRemiseVal()) / 100 ; 
            }
            else
            {
                $remiseG = $facture->getRemiseVal() ;
            }
        }
        else
        {
            $remiseG = 0 ;
        }
        $infoFacture["remise"] = $remiseG ;
        $infoFacture["lettre"] = $this->appService->NumberToLetter($facture->getTotal()) ;

        $response = $this->renderView('sav/savFactureDetails.html.twig',[
            "facture" => $infoFacture,
            "factureDetails" => $elements
        ]) ;

        return new Response($response) ;
    }

    #[Route('/sav/caisse/display', name: 'sav_caisse_display')]
    public function savCaisseDisplay(Request $request)
    {
        $idCs = $request->request->get("idCs") ;

        $caisseCommande = $this->entityManager->getRepository(CaisseCommande::class)->find($idCs) ;

        $remise = 0 ;

        if(!is_null($caisseCommande->getRemiseType()))
        {
            if($caisseCommande->getRemiseType()->getCalcul() == 1)
            {
                $remise = $caisseCommande->getRemiseValeur() ;
            }
            else
            {
                $remise = ($caisseCommande->getRemiseValeur() * $caisseCommande->getMontantPayee()) / 100 ;
            }
        }

        $totalTtc = $caisseCommande->getTva() + $caisseCommande->getMontantPayee() - $remise ;

        $caisse = [
            "numCommande" => $caisseCommande->getNumCommande() ,
            "totalHt" => $caisseCommande->getMontantPayee() ,
            "remise" => $remise,
            "totalTva" => $caisseCommande->getTva() ,
            "totalTtc" => $totalTtc ,
            "lettre" => $this->appService->NumberToLetter($totalTtc) ,
        ] ;

        $caissePaniers = $this->entityManager->getRepository(CaissePanier::class)->findBy([
            "commande" => $caisseCommande,
        ]) ;  

        $elements = [] ;

        foreach ($caissePaniers as $caissePanier) {
            $item = [] ;
            
            $filtreNomProduit = is_null($caissePanier->getVariationPrix()->getProduit()->getType()) ? "NA" : $caissePanier->getVariationPrix()->getProduit()->getType()->getNom() ;

            $item["id"] = $caissePanier->getId();
            $item["designation"] = $filtreNomProduit." | ".$caissePanier->getVariationPrix()->getProduit()->getNom() ;
            $item["quantite"] = $caissePanier->getQuantite() ;
            $item["prix"] = $caissePanier->getPrix() ;
            $item["tva"] = (($caissePanier->getPrix() * $caissePanier->getTva()) / 100 ) * $caissePanier->getQuantite() ;
            $item["total"] = $caissePanier->getQuantite() * $caissePanier->getPrix() ;
            $item["statut"] = $caissePanier->isStatut() ;

            array_push($elements,$item) ;
        }

        $caissePaniers = $elements ;

        $response = $this->renderView('sav/savCaisseDetails.html.twig',[
            "caisse" => $caisse,
            "caissePaniers" => $caissePaniers
        ]) ;

        return new Response($response) ;

    }

    #[Route('/sav/annulation/facture/save', name: 'sav_save_fact_annulation')]
    public function savSaveAnnulationFacture(Request $request)
    {
        $sav_caisse = $request->request->get('sav_caisse') ;
        $sav_facture = $request->request->get('sav_facture') ;
        $sav_type_annule = $request->request->get('sav_type_annule') ;
        $sav_val_spec = $request->request->get('sav_val_spec') ;
        $sav_motifs = $request->request->get('sav_motifs') ;
        $sav_percent = $request->request->get('sav_percent') ;
        $sav_annule_editor = $request->request->get('sav_annule_editor') ;
        $sav_lieu = $request->request->get('sav_lieu') ;
        $sav_date = $request->request->get('sav_date') ;
        $sav_type = $request->request->get('sav_type') ;
        
        $data = [
            $sav_type,
            $sav_type_annule,
            $sav_val_spec,
            $sav_motifs,
            $sav_lieu,
            $sav_date
        ];

        $dataMessage = [
            "Type Affichage",
            "Type Annulation",
            "Specification",
            "Motif",
            "Lieu",
            "Date"
        ] ;

        $result = $this->appService->verificationElement($data,$dataMessage) ;
        
        if(!$result["allow"])
            return new JsonResponse($result) ;
        
        $sav_facture_detail = (array)$request->request->get('sav_facture_detail') ;
        
        if(empty($sav_facture_detail))
        {
            return new JsonResponse([
                "type" => "orange",
                "message" => "Séléctionner les éléments à annuler"
            ]) ;
        }

        $specification = $this->entityManager->getRepository(SavSpec::class)->find($sav_val_spec) ;

        if($sav_type == "CAISSE")
        {
            $caisse = $this->entityManager->getRepository(CaisseCommande::class)->find($sav_caisse) ;
            $facture = null ;
            $client = null ;
            $recordAnnuation = $this->entityManager->getRepository(SavAnnulation::class)->findBy([
                "specification" => $specification,
                "caisse" => $caisse
            ]);

            $numAnnulation = !is_null($recordAnnuation) ? (count($recordAnnuation) + 1) : 1 ;
            if($numAnnulation == 1)
            {
                $numAnnulation = str_pad($numAnnulation, 3, "0", STR_PAD_LEFT);
                if($specification->getReference() == "RMB")
                    $numAnnulation = $caisse->getNumCommande()."/RTN" ;
                else if($specification->getReference() == "AVR")
                    $numAnnulation = $caisse->getNumCommande()."/ANL" ;
                else
                    $numAnnulation = $caisse->getNumCommande()."/ANL-ACN" ;
            }
            else
            {
                $firstAnnuation = $this->entityManager->getRepository(SavAnnulation::class)->findOneBy([
                    "specification" => $specification,
                    "caisse" => $caisse
                ],["id" => "ASC"]);

                $numAnnulation -= 1 ;
                $numAnnulation = str_pad($numAnnulation, 3, "0", STR_PAD_LEFT);
                $numAnnulation = $firstAnnuation->getNumCommande()."/BIS-".$numAnnulation ;
            }

            $caisse->setSynchro(null) ;
            $this->entityManager->flush() ;
        }
        else
        {
            $facture = $this->entityManager->getRepository(Facture::class)->find($sav_facture) ;
            $caisse = null ;
            $client = $facture->getClient() ;

            $recordAnnuation = $this->entityManager->getRepository(SavAnnulation::class)->findBy([
                "specification" => $specification,
                "facture" => $facture
            ]);

            $numAnnulation = !is_null($recordAnnuation) ? (count($recordAnnuation) + 1) : 1 ;
            if($numAnnulation == 1)
            {
                $numAnnulation = str_pad($numAnnulation, 3, "0", STR_PAD_LEFT);
                if($specification->getReference() == "RMB")
                    $numAnnulation = $facture->getNumFact()."/RTN" ;
                else if($specification->getReference() == "AVR")
                    $numAnnulation = $facture->getNumFact()."/ANL" ;
                else
                    $numAnnulation = $facture->getNumFact()."/ANL-ACN" ;
            }
            else
            {
                $firstAnnuation = $this->entityManager->getRepository(SavAnnulation::class)->findOneBy([
                    "specification" => $specification,
                    "facture" => $facture
                ],["id" => "ASC"]);

                $numAnnulation -= 1 ;
                $numAnnulation = str_pad($numAnnulation, 3, "0", STR_PAD_LEFT);
                $numAnnulation = $firstAnnuation->getNumFact()."/BIS-".$numAnnulation ;
            }

            $facture->setSynchro(null) ;
            $this->entityManager->flush() ;
        }
        
        $type = $this->entityManager->getRepository(SavType::class)->find($sav_type_annule) ;
        $motif = $this->entityManager->getRepository(SavMotif::class)->find($sav_motifs) ;
        
        if($specification->getReference() == "RMB")
        {
            $data = [
                $sav_percent,
            ];

            $dataMessage = [
                "Pourcentage",
            ] ;

            $result = $this->appService->verificationElement($data,$dataMessage) ;
            
            if(!$result["allow"])
                return new JsonResponse($result) ;
        }

        $this->entityManager->flush() ;

        $annulation = new SavAnnulation() ;

        $annulation->setAgence($this->agence) ;
        $annulation->setUser($this->userObj) ;
        $annulation->setFacture($facture) ;
        $annulation->setCaisse($caisse) ;
        $annulation->setClient($client) ;
        $annulation->setType($type) ;
        $annulation->setMotif($motif) ;
        $annulation->setSpecification($specification) ;
        $annulation->setPourcentage(empty($sav_percent) ? 0 : floatval($sav_percent)) ;
        $annulation->setNumFact($numAnnulation) ;
        $annulation->setDate(\DateTime::createFromFormat('j/m/Y',$sav_date)) ;
        $annulation->setLieu($sav_lieu) ;
        $annulation->setExplication($sav_annule_editor) ;
        $annulation->setStatut(True) ;
        $annulation->setCreatedAt(new \DateTimeImmutable) ;
        $annulation->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($annulation) ;
        $this->entityManager->flush() ;

        $totalHt = 0 ;
        $totalTva = 0 ;
        $totalTtc = 0 ;
        $montantAnnulation = 0 ;

        for ($i=0; $i < count($sav_facture_detail); $i++) { 
            if($sav_type == "FACTURE")
            {
                $idFd = $sav_facture_detail[$i] ;
                $factureDetail = $this->entityManager->getRepository(FactDetails::class)->find($idFd) ;
                $tva = (($factureDetail->getPrix() * $factureDetail->getTvaVal()) / 100) * $factureDetail->getQuantite();
                $total = $factureDetail->getPrix() * $factureDetail->getQuantite()  ;
    
                if(!is_null($factureDetail->getRemiseType()))
                {
                    if($factureDetail->getRemiseType()->getId() == 1)
                    {
                        $remise = ($total * $factureDetail->getRemiseVal()) / 100 ; 
                    }
                    else
                    {
                        $remise = $factureDetail->getRemiseVal() ;
                    }
                }
                else
                {
                    $remise = 0 ;
                }
                
                $total = $total - $remise ;
                
                $totalHt += $total ;
                $totalTva += $tva ;
                $caisseDetail = null ;
            }
            else
            {
                $idCsd = $sav_facture_detail[$i] ;
                $caisseDetail = $this->entityManager->getRepository(CaissePanier::class)->find($idCsd) ; ;
                $factureDetail = null ;

                $montantAnnulation += $caisseDetail->getPrix() * $caisseDetail->getQuantite() ;
            }

            $savDetail = new SavDetails() ;

            $savDetail->setAnnulation($annulation) ;
            $savDetail->setFactureDetail($factureDetail) ;
            $savDetail->setCaisseDetail($caisseDetail) ;
            $savDetail->setAgence($this->agence) ;
            $savDetail->setStatut(True) ;

            if($sav_type == "FACTURE")
            { 
                $factureDetail->setStatut(False) ;
            }else
            {
                $caisseDetail->setStatut(False) ;
            }
            
            $this->entityManager->persist($savDetail) ;
            $this->entityManager->flush() ;
        }

        if($sav_type == "FACTURE")
        {
            $totalTtc = $totalHt + $totalTva ;

            $annulation->setMontant($totalTtc) ;
    
            $facture->setTotal($facture->getTotal() - $totalTtc) ;
            $facture->setTvaVal($facture->getTvaVal() - $totalTva) ;
    
            $this->entityManager->flush() ;

            $dataFilenames = [
                $this->filename."annulation(agence)/".$this->nameAgence,
                "files/systeme/facture/facture(agence)/".$this->nameAgence,
            ] ;
        }
        else
        {
            $annulation->setMontant($montantAnnulation) ;
            
            $caisse->setMontantPayee($caisseDetail->getCommande()->getMontantPayee() - $montantAnnulation) ;
            $this->entityManager->flush() ;

            $dataFilenames = [
                $this->filename."annulation(agence)/".$this->nameAgence,
                "files/systeme/caisse/panierCommande(agence)/".$this->nameAgence,
            ] ;
        }

        foreach ($dataFilenames as $dataFilename) {
            if(file_exists($dataFilename))
                unlink($dataFilename) ;
        }
        
        return new JsonResponse($result) ;
    }

    #[Route('/sav/annulation/details/{id}', name: 'sav_details_annulation')]
    public function savDetailsAnnulation($id)
    {
        $annulation = $this->entityManager->getRepository(SavAnnulation::class)->find($id) ;
        $facture = $annulation->getFacture() ;
        $client = ($this->appService->getFactureClient($facture))["client"] ;
        $type = $annulation->getType()->getNom() ;
        $spec = $annulation->getSpecification()->getNom() ;

        $total = $annulation->getMontant() ;

        $retenu = 0 ;
        if($annulation->getPourcentage() == 0)
        {
            $retenu = "-" ;
            $signe = "" ;
            $remboursee = $annulation->getMontant() ;
        }
        else
        {
            $retenu = ($annulation->getMontant() * $annulation->getPourcentage()) / 100 ;
            $signe = "(".$annulation->getPourcentage()."%)" ;
            $remboursee = $annulation->getMontant() - $retenu ;
        }
        $donnee = [] ;
  
        $donnee["numFacture"] = $annulation->getNumFact() ;
        $donnee["spec"] = $spec ;
        $donnee["refSpec"] = $annulation->getSpecification()->getReference() ;
        $donnee["refType"] = $annulation->getType()->getReference() ;
        $donnee["date"] = $annulation->getDate()->format('d/m/Y') ;
        $donnee["lieu"] = $annulation->getLieu() ;
        $donnee["client"] = $client ;
        $donnee["motif"] = $annulation->getMotif()->getNom() ;
        $donnee["total"] = $total ;
        $donnee["retenu"] = $retenu ;
        $donnee["signe"] = $signe ;
        $donnee["remboursee"] = $remboursee ;
       
        
        $annulationDetails = $this->entityManager->getRepository(SavDetails::class)->findBy([
            "annulation" => $annulation
        ]) ;

        
        $totalHt = 0 ;
        $elements = [] ;
        $totalTva = 0 ;

        foreach ($annulationDetails as $annulationDetail) {

            $factureDetail = $annulationDetail->getFactureDetail() ;
            $tva = (($factureDetail->getPrix() * $factureDetail->getTvaVal()) / 100) * $factureDetail->getQuantite();
            $totale = $factureDetail->getPrix() * $factureDetail->getQuantite()  ;
            $remise = $this->appService->getFactureRemise($factureDetail,$totalHt) ; 
            $totale = $totale - $remise ;

            $element = [] ;
            $element["type"] = $factureDetail->getActivite() ;
            $element["designation"] = $factureDetail->getDesignation() ;
            $element["quantite"] = $factureDetail->getQuantite() ;
            $element["format"] = "-" ;
            $element["prix"] = $factureDetail->getPrix() ;
            $element["tva"] = ($tva == 0) ? "-" : $tva ;
            $element["typeRemise"] = is_null($factureDetail->getRemiseType()) ? "-" : $factureDetail->getRemiseType()->getNotation() ;
            $element["valRemise"] = $factureDetail->getRemiseVal() ;
            $element["total"] = $totale ;
            array_push($elements,$element) ;

            $totalHt += $totale ;
            $totalTva += $tva ;
        } 

        $donnee["totalHt"] = $totalHt ;
        $donnee["totalTva"] = $totalTva ;
        $donnee["lettre"] = $this->appService->NumberToLetter($total) ;

        return $this->render('sav/detailsItemAnnulation.html.twig', [
            "filename" => "sav",
            "titlePage" => "Annulation ".$type,
            "with_foot" => true,
            "factureDetails" => $elements,
            "annulation" => $donnee
        ]) ;
    }

    #[Route('/sav/annulation/client/details/{idC}', name: 'sav_annulation_details_client')]
    public function savDetailsAvoirClient($idC)
    {
        $filename = $this->filename."annulation(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateSavAnnulation($filename,$this->agence) ;
        
        $annulations = json_decode(file_get_contents($filename)) ;
        
        $search = [
            "idC" => $idC,
            "refSpec" => "AVR"
        ] ;

        $annulations = $this->appService->searchData($annulations,$search) ;
        
        $annulations = array_values($annulations) ; 

        $nomClient = $annulations[0]->client ; 

        return $this->render('sav/detailsAnnulationClient.html.twig', [
            "filename" => "sav",
            "titlePage" => "Avoir client : ",
            "with_foot" => false,
            "annulations" => $annulations,
            "nomClient" => $nomClient
        ]);
    }

    #[Route('/sav/contenu/annulation/get', name: 'sav_contenu_annulation_get')]
    public function savGetContenuAnnulation(Request $request)
    {
        $typeAffichage = $request->request->get('typeAffichage') ;
        $response = "" ;
        if($typeAffichage == "CAISSE")
        {
            $filename = "files/systeme/caisse/commande(agence)/".$this->nameAgence ; 
            if(!file_exists($filename))
                $this->appService->generateCaisseCommande($filename, $this->agence) ;

            $commandes = json_decode(file_get_contents($filename)) ;

            $response = $this->renderView("sav/contenu/getContentCaisse.html.twig",[
                "commandes" => $commandes
            ]) ;
        }
        else if($typeAffichage == "FACTURE")
        {
            $filename = "files/systeme/facture/facture(agence)/".$this->nameAgence ;
            if(!file_exists($filename))
                $this->appService->generateFacture($filename, $this->agence) ;

            $factures = json_decode(file_get_contents($filename)) ;
            
            $search = [
                "numFact" => "DF"
            ] ;
                
            $factures = $this->appService->searchData($factures,$search) ;

            $response = $this->renderView("sav/contenu/getContentFacture.html.twig",[
                "factures" => $factures
            ]) ;
        }

        return new Response($response) ;
    }
}
