<?php

namespace App\Controller;

use App\Entity\Agence;
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
        $filename = "files/systeme/facture/facture(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateFacture($filename, $this->agence) ;

        $factures = json_decode(file_get_contents($filename)) ;
        
        $search = [
            "numFact" => "DF"
        ] ;
            
        $factures = $this->appService->searchData($factures,$search) ;
        
        
        $types = $this->entityManager->getRepository(SavType::class)->findAll() ;

        $specs = $this->entityManager->getRepository(SavSpec::class)->findAll() ;

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
            array_push($avoirs,$item) ;
        } 

        return $this->render('sav/index.html.twig', [
            "filename" => "sav",
            "titlePage" => "Service Après Vente",
            "with_foot" => true,
            "factures" => $factures,
            "types" => $types,
            "specs" => $specs,
            "motifs" => $motifs,
            "annulations" => $annulations ,
            "avoirs" => $avoirs
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
            "statut" => True,
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
            array_push($elements,$element) ;

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

    #[Route('/sav/annulation/facture/save', name: 'sav_save_fact_annulation')]
    public function savSaveAnnulationFacture(Request $request)
    {
        $sav_facture = $request->request->get('sav_facture') ;
        $sav_type_annule = $request->request->get('sav_type_annule') ;
        $sav_val_spec = $request->request->get('sav_val_spec') ;
        $sav_motifs = $request->request->get('sav_motifs') ;
        $sav_percent = $request->request->get('sav_percent') ;
        $sav_annule_editor = $request->request->get('sav_annule_editor') ;
        $sav_lieu = $request->request->get('sav_lieu') ;
        $sav_date = $request->request->get('sav_date') ;

        $data = [
            $sav_facture,
            $sav_type_annule,
            $sav_val_spec,
            $sav_motifs,
            $sav_lieu,
            $sav_date
        ];

        $dataMessage = [
            "Facture",
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

        $facture = $this->entityManager->getRepository(Facture::class)->find($sav_facture) ;

        $recordAnnuation = $this->entityManager->getRepository(SavAnnulation::class)->findBy([
            "facture" => $facture
        ]);

        $specification = $this->entityManager->getRepository(SavSpec::class)->find($sav_val_spec) ;
        $numTrueFacture = explode("/",$facture->getNumFact()) ;
        if($specification->getReference() == "RMB")
        {
            $numAnnulation = $numTrueFacture[0]."/".$numTrueFacture[1]."/RTN" ;
        }
        else
        {
            $numAnnulation = !is_null($recordAnnuation) ? (count($recordAnnuation) + 1) : 1 ;
            $numAnnulation = str_pad($numAnnulation, 3, "0", STR_PAD_LEFT);
            $numAnnulation = $numTrueFacture[0]."/".$numTrueFacture[1]."/ANL-".$numAnnulation ;
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

        $facture->setNumFact($numAnnulation) ;
        if($type->getreference() == "TOT")
        {
            $facture->setStatut(False) ;
        }
        else
        {
            $allfactureDetails = $this->entityManager->getRepository(FactDetails::class)->findBy([
                "statut" => True,
                "facture" => $facture
            ]) ;

            if(count($allfactureDetails) == count($sav_facture_detail))
            {
                $facture->setStatut(False) ;
            }
        }
        
        $this->entityManager->flush() ;

        $annulation = new SavAnnulation() ;

        $annulation->setAgence($this->agence) ;
        $annulation->setUser($this->userObj) ;
        $annulation->setFacture($facture) ;
        $annulation->setClient($facture->getClient()) ;
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

        for ($i=0; $i < count($sav_facture_detail); $i++) { 
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

            $savDetail = new SavDetails() ;

            $savDetail->setAnnulation($annulation) ;
            $savDetail->setFactureDetail($factureDetail) ;
            $savDetail->setAgence($this->agence) ;
            $savDetail->setStatut(True) ;

            $factureDetail->setStatut(False) ;
            $this->entityManager->persist($savDetail) ;
            $this->entityManager->flush() ;

            $totalHt += $total ;
            $totalTva += $tva ;
        }
        
        $totalTtc = $totalHt + $totalTva ;

        $annulation->setMontant($totalTtc) ;

        $facture->setTotal($facture->getTotal() - $totalTtc) ;
        $facture->setTvaVal($facture->getTvaVal() - $totalTva) ;

        $this->entityManager->flush() ;

        $filename = $this->filename."annulation(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;
        if(!file_exists($filename))
            $this->appService->generateSavAnnulation($filename,$this->agence) ;
        
        $filename = "files/systeme/facture/facture(agence)/".$this->nameAgence ;
        unlink($filename) ;
        
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
        $donnee["refType"] = $annulation->getSpecification()->getReference() ;
        $donnee["date"] = $annulation->getDate()->format('d/m/Y') ;
        $donnee["lieu"] = $annulation->getLieu() ;
        $donnee["client"] = $client ;
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

            if(!is_null($factureDetail->getRemiseType()))
            {
                if($factureDetail->getRemiseType()->getId() == 1)
                {
                    $remise = ($totale * $factureDetail->getRemiseVal()) / 100 ; 
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
}
