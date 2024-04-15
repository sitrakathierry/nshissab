<?php

namespace App\Controller;

use App\Entity\AgdLivraison;
use App\Entity\Agence;
use App\Entity\CmdBonCommande;
use App\Entity\CmdStatut;
use App\Entity\FactCritereDate;
use App\Entity\FactDetails;
use App\Entity\FactHistoPaiement;
use App\Entity\Facture;
use App\Entity\HistoHistorique;
use App\Entity\LvrDetails;
use App\Entity\LvrLivraison;
use App\Entity\LvrStatut;
use App\Entity\ModModelePdf;
use App\Entity\User;
use App\Service\AppService;
use App\Service\PdfGenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class LivraisonController extends AbstractController
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
        $this->filename = "files/systeme/livraison/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"],
            "agence" => $this->agence  
        ]) ;
    }
    
    #[Route('/livraison/creation', name: 'lvr_livraison')]
    public function index(): Response
    {
        return $this->render('livraison/creation.html.twig', [
            "filename" => "livraison",
            "titlePage" => "Création bon de livraison",
            "with_foot" => true
        ]);
    } 
    
    #[Route('/livraison/facture/display', name: 'lvr_facture_display')]
    public function lvrFactureDisplay(Request $request)
    {
        $lvr_source = $request->request->get('lvr_source') ;
        $idSource = $request->request->get('idSource') ;

        if($lvr_source == "BonCommande")
        {
            $bonCommande = $this->entityManager->getRepository(CmdBonCommande::class)->find($idSource) ;
            $facture = $bonCommande->getFacture() ;
            $bonLivraisons = $this->entityManager->getRepository(LvrLivraison::class)->findBy([
                "source" => $bonCommande->getId(),
                "typeSource" => "BonCommande"
            ]) ;
        }
        else
        {
            $facture = $this->entityManager->getRepository(Facture::class)->find($idSource) ;

            $bonLivraisons = $this->entityManager->getRepository(LvrLivraison::class)->findBy([
                "source" => $facture->getId(),
                "typeSource" => "Facture"
            ]) ;
        }

        $itemLvrDetail = [] ;

        if(!is_null($bonLivraisons))
        {
            foreach($bonLivraisons as $bonLivraison)
            {
                $detailsLvrs = $this->entityManager->getRepository(LvrDetails::class)->findBy([
                    "livraison" => $bonLivraison
                ]) ;
                foreach($detailsLvrs as $detailsLvr)
                {
                    $itemLvr = [] ;
                    $itemLvr["id"] = $detailsLvr->getFactureDetail()->getId() ;

                    array_push($itemLvrDetail,$itemLvr) ; 
                } 
            }
        }

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
            $statutDtls = "" ;
            for ($i=0; $i < count($itemLvrDetail); $i++) { 
                $idDtlsLvr = $itemLvrDetail[$i]["id"] ; // Id Details de Livraison
                if($factureDetail->getId() == $idDtlsLvr)
                {
                    $statutDtls = '<b class="text-success">Livré</b>' ;
                    break ;
                }
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
            $element["statut"] = $statutDtls ;
 
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

        $response = $this->renderView('livraison/livraisonFactureDetails.html.twig',[
            "facture" => $infoFacture,
            "factureDetails" => $elements
        ]) ;

        return new Response($response) ;
    }

    #[Route('/livraison/creation/source/display', name: 'lvr_display_source_creation')]
    public function lvrDisplaySourceCreation(Request $request)
    {
        $source = $request->request->get('source') ; 
        
        // Chargement du bon de livraison
        $filename = $this->filename."bonLivraison(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateBonLivraison($filename,$this->agence) ;

        $bonLivraisons = json_decode(file_get_contents($filename)) ;

        if($source == "BonCommande") // Bon de commande
        {
            $filename = "files/systeme/commande/commande(agence)/".$this->nameAgence ;
            if(file_exists($filename))
                unlink($filename) ;
            if(!file_exists($filename))
                $this->appService->generateCommande($filename,$this->agence) ;

            $bonCommandes = json_decode(file_get_contents($filename)) ;

            $search = [
                "statut" => "VLD"
            ] ;
    
            $bonCommandes = $this->appService->searchData($bonCommandes,$search) ;

            $response = $this->renderView("commande/listCommande.html.twig",[
                "bonCommandes" => $bonCommandes
            ]) ;
        }
        else
        {
            $filename = "files/systeme/facture/facture(agence)/".$this->nameAgence ;

            if(!file_exists($filename))
                $this->appService->generateFacture($filename, $this->agence) ;

            $factures = json_decode(file_get_contents($filename)) ;

            $search = [
                "numFact" => "DF"
            ] ;
                
            $factures = $this->appService->searchData($factures,$search) ;

            $response = $this->renderView("commande/listFacture.html.twig",[
                "factures" => $factures
            ]) ;
        }

        return new Response($response) ;
    }

    #[Route('/livraison/bon/save', name: 'lvr_save_bon_livraison')]
    public function lvrSaveBonLivraison(Request $request)
    {
        $lvr_val_source = $request->request->get('lvr_val_source') ;
        $lvr_source = $request->request->get('lvr_source') ;
        $lvr_creation_description = $request->request->get('lvr_creation_description') ;
        $lvr_lieu = $request->request->get('lvr_lieu') ;
        $lvr_date = $request->request->get('lvr_date') ;

        $data = [
            $lvr_val_source,
            $lvr_lieu,
            $lvr_date
        ];

        $dataMessage = [
            empty($lvr_source) ? "Source bon de livraison " : $lvr_source,
            "Lieu",
            "Date"
        ] ;

        $result = $this->appService->verificationElement($data,$dataMessage) ;
        
        if(!$result["allow"])
            return new JsonResponse($result) ;
        
        $lvr_id_facture_detail = (array)$request->request->get('lvr_id_facture_detail') ;
        
        if(empty($lvr_id_facture_detail))
        {
            return new JsonResponse([
                "type" => "orange",
                "message" => "Séléctionner les éléments livré"
            ]) ;
        }

        $recordLivraison = $this->entityManager->getRepository(LvrLivraison::class)->findBy([
            "source" => $lvr_val_source,
            "typeSource" => $lvr_source
        ]);

        if($lvr_source == "BonCommande")
        {
            $bonCommande = $this->entityManager->getRepository(CmdBonCommande::class)->find($lvr_val_source) ;
            $numero = $bonCommande->getNumBonCmd() ;
        }
        else
        {
            $numero = $this->entityManager->getRepository(Facture::class)
                                            ->find($lvr_val_source)
                                            ->getNumFact() ;
        }

        $numBonLivraison = !is_null($recordLivraison) ? (count($recordLivraison) + 1) : 1 ;
        $numBonLivraison = str_pad($numBonLivraison, 3, "0", STR_PAD_LEFT);
        $numBonLivraison = $numero."/BIS-".$numBonLivraison ;

        $cmdStatut = $this->entityManager->getRepository(CmdStatut::class)->findOneBy([
            "reference" => "ECR"
        ]) ;

        $bonLivraison = new LvrLivraison() ;

        $bonLivraison->setAgence($this->agence) ;
        $bonLivraison->setSource($lvr_val_source) ;
        $bonLivraison->setTypeSource($lvr_source) ;
        $bonLivraison->setNumLivraison($numBonLivraison) ;
        $bonLivraison->setDate(\DateTime::createFromFormat('j/m/Y',$lvr_date)) ;
        $bonLivraison->setLieu($lvr_lieu) ;
        $bonLivraison->setDescription($lvr_creation_description) ;
        $bonLivraison->setStatut($cmdStatut) ;
        $bonLivraison->setCreatedAt(new \DateTimeImmutable) ;
        $bonLivraison->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($bonLivraison) ;
        $this->entityManager->flush() ;

        for ($i=0; $i < count($lvr_id_facture_detail); $i++) { 
            $idFDs = $lvr_id_facture_detail[$i] ;

            $factureDetail = $this->entityManager->getRepository(FactDetails::class)->find($idFDs) ;

            $lvrDetail = new LvrDetails() ;

            $lvrDetail->setLivraison($bonLivraison) ;
            $lvrDetail->setFactureDetail($factureDetail) ;
            $lvrDetail->setAgence($this->agence) ;
            $lvrDetail->setStatut(True) ;

            $this->entityManager->persist($lvrDetail) ;
            $this->entityManager->flush() ;
        }
        
        $filename = $this->filename."bonLivraison(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;
        if(!file_exists($filename))
            $this->appService->generateBonLivraison($filename,$this->agence) ;
        
        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "LVR",
            "nomModule" => "BON DE LIVRAISON",
            "refAction" => "CRT",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Nouveau Bon de Livraison -> ". $numBonLivraison ,
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

        return new JsonResponse($result) ;
    }

    #[Route('/livraison/bon/update', name: 'lvr_update_bon_livraison')]
    public function lvrUpdateBonLivraison(Request $request)
    {
        $lvr_creation_description = $request->request->get('lvr_creation_description') ;
        $lvr_lieu = $request->request->get('lvr_lieu') ;

        $data = [
            $lvr_lieu
        ];

        $dataMessage = [
            "Lieu"
        ] ;

        $result = $this->appService->verificationElement($data,$dataMessage) ;
        
        if(!$result["allow"])
            return new JsonResponse($result) ;

        $enr_date_livraison = $request->request->get('enr_date_livraison') ;
        $enr_produit_livree = explode(",",$request->request->get('enr_produit_livree'))  ;

        $lvr_id_facture_detail = (array)$request->request->get("lvr_id_facture_detail") ;

        $lvr_id_bon_livraison = $request->request->get("lvr_id_bon_livraison") ;
        
        $bonLivraison = $this->entityManager->getRepository(LvrLivraison::class)->find($lvr_id_bon_livraison);
                
        $bonLivraison->setDescription($lvr_creation_description) ;
        $bonLivraison->setLieu($lvr_lieu) ;

        $this->entityManager->flush() ;

        // DEBUT AJOUT LIVRAISON SUR AGENDA

        $agdLivraison = new AgdLivraison() ;

        $agdLivraison->setLivraison($bonLivraison) ;
        $agdLivraison->setDate(\DateTime::createFromFormat("d/m/Y",$enr_date_livraison)) ;
        $agdLivraison->setObjet("LIVRAISON DE ".str_pad(count($enr_produit_livree),2,"0",STR_PAD_LEFT)." PRODUIT, BON DE LIVRAISON N° ".$bonLivraison->getNumLivraison()) ;
        $agdLivraison->setStatut(True) ;
        $agdLivraison->setAgence($this->agence) ;

        $this->entityManager->persist($agdLivraison) ;
        $this->entityManager->flush() ;

        // FIN AJOUT LIVRAISON SUR AGENDA
        
        $lvrStatut = $this->entityManager->getRepository(LvrStatut::class)->findOneBy([
            "reference" => "LIVRE"
        ]);

        foreach ($enr_produit_livree as $assocDate) {
            // $idLvrDetail = $lvr_id_facture_detail[$i] ;

            $lvrDetail = $this->entityManager->getRepository(LvrDetails::class)->find($assocDate) ;

            $lvrDetail->setDateLivraison(\DateTime::createFromFormat("d/m/Y",$enr_date_livraison)) ;
        }

        $this->entityManager->flush() ;

        for ($i=0; $i < count($lvr_id_facture_detail); $i++) { 
            $idLvrDetail = $lvr_id_facture_detail[$i] ;

            $lvrDetail = $this->entityManager->getRepository(LvrDetails::class)->find($idLvrDetail) ;

            $lvrDetail->setLvrStatut($lvrStatut) ;
        }

        $this->entityManager->flush() ;

        $filename = $this->filename."bonLivraison(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;

        $filename = "files/systeme/agenda/agenda(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse([
            "type" => "green",
            "message" => "Mise à jour effectué"
        ]) ;
    }
    
    #[Route('/livraison/activities/consultation', name: 'lvr_consultation_livraison')]
    public function lvrConsultationLivraison()
    {
        $filename = $this->filename."bonLivraison(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateBonLivraison($filename,$this->agence) ;
        $livraisons = json_decode(file_get_contents($filename)) ;

        $critereDates = $this->entityManager->getRepository(FactCritereDate::class)->findAll() ;

        $groupedData = array();

        foreach ($livraisons as $item) {
            $key = $item->source . '|' . $item->typeSource;
            if (!isset($groupedData[$key])) {
                $groupedData[$key] = array();
            }
            $groupedData[$key][] = $item;
        }

        // dd($groupedData) ;

        return $this->render('livraison/consultation.html.twig', [
            "filename" => "livraison",
            "titlePage" => "Consultation bon de livraison",
            "with_foot" => false,
            "critereDates" => $critereDates,
            "livraisons" => $livraisons
        ]); 
    }

    #[Route('/livraison/bon/check', name: 'lvr_check_bon_livraison')]
    public function lvrCheckBonLivraison(Request $request)
    {
        $id = $request->request->get('id') ;
        $bonLivraison = $this->entityManager->getRepository(LvrLivraison::class)->find($id) ;
        $cmdStatut = $this->entityManager->getRepository(CmdStatut::class)->findOneBy([
            "reference" => "VLD"
        ]) ;

        $bonLivraison->setStatut($cmdStatut) ;

        $this->entityManager->flush() ;

        $filename = $this->filename."bonLivraison(agence)/".$this->nameAgence ;
        
        if(file_exists($filename))
            unlink($filename) ;
        
        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "LVR",
            "nomModule" => "BON DE LIVRAISON",
            "refAction" => "VLD",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Validation Bon de Livraison -> ". $bonLivraison->getNumLivraison() ,
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

        return new JsonResponse([
            "type" => "green",
            "message" => "Bon de livraison validé avec succès"
        ]) ;
    }

    #[Route('/livraison/bon/details/{id}', name: 'lvr_details_bon_livraison', defaults: ["id" => null])]
    public function lvrDetailsBonLivraison($id)
    {
        $bonLivraison = $this->entityManager->getRepository(LvrLivraison::class)->find($id) ;
        if($bonLivraison->getTypeSource() == "BonCommande")
        {
            $bonCommande = $this->entityManager->getRepository(CmdBonCommande::class)->find($bonLivraison->getSource()) ;
            $numero = $bonCommande->getNumBonCmd() ;
            $libelle = "Bon de Commande" ;
            $client = $this->appService->getFactureClient($bonCommande->getFacture())  ;
        }
        else
        {
            $facture = $this->entityManager->getRepository(Facture::class)
                                            ->find($bonLivraison->getSource()) ;
            $numero = $facture->getNumFact() ;
            $libelle = "facture" ;
            $client = $this->appService->getFactureClient($facture) ;
        }

        $livraison = [] ;

        $livraison["id"] = $bonLivraison->getId() ;
        $livraison["numLivraison"] = $bonLivraison->getNumLivraison() ;
        $livraison["libelleNum"] = $libelle ;
        $livraison["description"] = $bonLivraison->getDescription() ;
        $livraison["valeurNum"] = $numero ;
        $livraison["date"] = $bonLivraison->getDate()->format('d/m/Y') ;
        $livraison["lieu"] = $bonLivraison->getLieu() ;
        $livraison["client"] = $client['client'] ;

        $details = [] ;

        $lvrDetails = $this->entityManager->getRepository(LvrDetails::class)->findBy([
            "livraison" => $bonLivraison
        ]) ;

        foreach ($lvrDetails as $lvrDetail) {
            $detail = [] ;

            $detail["id"] = $lvrDetail->getId() ;
            $detail["type"] = $lvrDetail->getFactureDetail()->getActivite() ;
            $detail["designation"] = $lvrDetail->getFactureDetail()->getDesignation() ;
            $detail["quantite"] = $lvrDetail->getFactureDetail()->getQuantite() ;
            $detail["lvrStatut"] = !is_null($lvrDetail->getLvrStatut()) ? $lvrDetail->getLvrStatut()->getId() : null ;
            $detail["nomLvrStatut"] = !is_null($lvrDetail->getLvrStatut()) ? $lvrDetail->getLvrStatut()->getNom() : null ;
            $detail["dateLivraison"] = !is_null($lvrDetail->getDateLivraison()) ? $lvrDetail->getDateLivraison()->format("d/m/Y") : "-" ;

            array_push($details,$detail) ;
        } 

        return $this->render('livraison/detailsBonLivraison.html.twig', [
            "filename" => "livraison",
            "titlePage" => "Bon de Livraison",
            "with_foot" => true,
            "livraison" => $livraison, 
            "details" => $details 
        ]) ;
    }

    #[Route('/livraison/bon/description/update', name: 'lvr_bon_description_update')]
    public function lvrBonUpdateDescription(Request $request)
    {
        $idLivraison = $request->request->get("idLivraison") ;
        $lvr_creation_description = $request->request->get("lvr_creation_description") ;
        $lvr_lieu = $request->request->get("lvr_lieu") ;
        $lvr_date = $request->request->get("lvr_date") ;

        $livraison = $this->entityManager->getRepository(LvrLivraison::class)->find($idLivraison) ;

        $livraison->setDescription($lvr_creation_description) ;
        
        if(isset($lvr_lieu) && !empty($lvr_lieu))
            $livraison->setLieu($lvr_lieu) ;

        if(isset($lvr_date) && !empty($lvr_date))
            $livraison->setDate(\DateTime::createFromFormat("d/m/Y",$lvr_date)) ;

        $this->entityManager->flush() ;

        return new JsonResponse([""]) ;

    }

    #[Route('/livraison/bon/livraison/imprimer/{idLivraison}/{idModeleEntete}/{idModeleBas}', name: 'lvr_bon_livraison_detail_imprimer',
    defaults: [
        "idLivraison" => null,
        "idModeleEntete" => null,
        "idModeleBas" => null]
    )]
    public function lvrImprimerBonLivraison($idLivraison,$idModeleEntete,$idModeleBas)
    {
        $contentEntete = "" ;
        if(!empty($idModeleEntete) || !is_null($idModeleEntete))
        {
            $modeleEntete = $this->entityManager->getRepository(ModModelePdf::class)->find($idModeleEntete) ;
            $imageLeft = is_null($modeleEntete->getImageLeft()) ? "" : $modeleEntete->getImageLeft() ;
            $imageRight = is_null($modeleEntete->getImageRight()) ? "" : $modeleEntete->getImageRight() ;
            $contentEntete = $this->renderView("parametres/modele/forme/getForme".$modeleEntete->getFormeModele().".html.twig",[
                "imageContentLeft" => $imageLeft ,
                "textContentEditor" => $modeleEntete->getContenu() ,
                "imageContentRight" => $imageRight ,
            ]) ;
        }
        
        $contentBas = "" ;
        if(!empty($idModeleBas) || !is_null($idModeleBas))
        {
            $modeleBas = $this->entityManager->getRepository(ModModelePdf::class)->find($idModeleBas) ;
            $imageLeft = is_null($modeleBas->getImageLeft()) ? "" : $modeleBas->getImageLeft() ;
            $imageRight = is_null($modeleBas->getImageRight()) ? "" : $modeleBas->getImageRight() ;
            $contentBas = $this->renderView("parametres/modele/forme/getForme".$modeleBas->getFormeModele().".html.twig",[
                "imageContentLeft" => $imageLeft ,
                "textContentEditor" => $modeleBas->getContenu() ,
                "imageContentRight" => $imageRight ,
            ]) ;
        }

        $bonLivraison = $this->entityManager->getRepository(LvrLivraison::class)->find($idLivraison) ;
        if($bonLivraison->getTypeSource() == "BonCommande")
        {
            $bonCommande = $this->entityManager->getRepository(CmdBonCommande::class)->find($bonLivraison->getSource()) ;
            $facture = $bonCommande->getFacture() ;
        }
        else
        {
            $facture = $this->entityManager->getRepository(Facture::class)->find($bonLivraison->getSource()) ;
        }

        $dataFacture = [
            "numBonLivraison" => $bonLivraison->getNumLivraison() ,
            "numFact" => $facture->getNumFact() ,
            "type" => $facture->getType()->getReference() == "DF" ? "" : $facture->getType()->getNom() ,
            "deviseLettre" => is_null($this->agence->getDevise()) ? "" : $this->agence->getDevise()->getLettre() ,
            "description" => $bonLivraison->getDescription() 
        ] ;

        $client = $facture->getClient() ;

        $dataClient = [
            "statut" => "",   
            "nom" => "",   
            "adresse" => "",   
            "telephone" => "",   
        ] ;

        if(!is_null($client))
        {
            if(!is_null($client->getSociete()))
            {
                $dataClient = [
                    "statut" => $client->getType()->getNom(),   
                    "nom" => $client->getSociete()->getNom(),   
                    "adresse" => $client->getSociete()->getAdresse(),   
                    "telephone" => $client->getSociete()->getTelFixe(),   
                ] ;
            }
            else
            {
                $dataClient = [
                    "statut" => $client->getType()->getNom(),   
                    "nom" => $client->getClient()->getNom(),   
                    "adresse" => $client->getClient()->getAdresse(),   
                    "telephone" => $client->getClient()->getTelephone(),   
                ] ;
            }
        }

        $lvrStatut = $this->entityManager->getRepository(LvrStatut::class)->findOneBy([
            "reference" => "LIVRE"
        ]);

        $lvrDetails = $this->entityManager->getRepository(LvrDetails::class)->findBy([
            "livraison" => $bonLivraison,
            // "lvrStatut" => $lvrStatut,
            "statut" => True,
        ]) ;

        $dataDetails = [] ;
        $totalHt = 0 ;
        $totalTva = 0 ;

        foreach ($lvrDetails as $lvrDetail) {

            $detail = $lvrDetail->getFactureDetail() ;

            $tvaVal = is_null($detail->getTvaVal()) ? 0 : $detail->getTvaVal() ;
            $tva = (($detail->getPrix() * $tvaVal) / 100) * $detail->getQuantite();
            $total = $detail->getPrix() * $detail->getQuantite()  ;
            $remise = $this->appService->getFactureRemise($detail,$total) ; 
            
            $total = $total - $remise ;
            
            $element = [] ;
            $element["type"] = $detail->getActivite() ;
            $element["designation"] = $detail->getDesignation() ;
            $element["quantite"] = $detail->getQuantite() ;
            $element["format"] = "-" ;
            $element["prix"] = $detail->getPrix() ; 
            $element["tva"] = $tva ;
            $element["typeRemise"] = is_null($detail->getRemiseType()) ? "-" : $detail->getRemiseType()->getNotation() ;
            $element["valRemise"] = $detail->getRemiseVal() ;
            $element["statut"] = $detail->isStatut();
            $element["total"] = $total ;
            $element["nomStatut"] = is_null($lvrDetail->getLvrStatut()) ? "Non Livré" : "Livré" ;

            array_push($dataDetails,$element) ;

            $totalHt += $total ;
            $totalTva += $tva ;
        } 

        $dataFacture["totalHt"] = $totalHt ;
        $dataFacture["totalTva"] = $totalTva ;
        $dataFacture["remise"] = $this->appService->getFactureRemise($facture,$totalHt) ; 
        $dataFacture["devise"] = !is_null($facture->getDevise()) ;
        $dataFacture["lettre"] = $this->appService->NumberToLetter($totalHt) ;
        $dataFacture["date"] = $bonLivraison->getDate()->format("d/m/Y") ;
        $dataFacture["lieu"] = $bonLivraison->getLieu() ;

        if(!is_null($facture->getDevise()))
        {
            $dataFacture["deviseCaption"] = $facture->getDevise()->getLettre() ;
            $dataFacture["deviseValue"] = number_format($facture->getTotal()/$facture->getDevise()->getMontantBase(),2,","," ")." ".$facture->getDevise()->getSymbole();
        }

        $contentIMpression = $this->renderView("livraison/impressionBonLivraison.html.twig",[
            "contentEntete" => $contentEntete,
            "contentBas" => $contentBas,
            "facture" => $dataFacture,
            "client" => $dataClient,
            "details" => $dataDetails,
        ]) ;

        $pdfGenService = new PdfGenService() ;

        $pdfGenService->generatePdf($contentIMpression,$this->nameUser) ;
        
        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "LVR",
            "nomModule" => "BON DE LIVRAISON",
            "refAction" => "IMP",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Impression Bon de Livraison -> ". $bonLivraison->getNumLivraison() ,
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

        // Redirigez vers une autre page pour afficher le PDF
        return $this->redirectToRoute('display_pdf');
    }
}
