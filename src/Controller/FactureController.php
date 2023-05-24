<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\CltHistoClient;
use App\Entity\Devise;
use App\Entity\FactCritereDate;
use App\Entity\FactDetails;
use App\Entity\FactHistoPaiement;
use App\Entity\FactModele;
use App\Entity\FactPaiement;
use App\Entity\FactRemiseType;
use App\Entity\FactType;
use App\Entity\Facture;
use App\Entity\User;
use App\Service\AppService;
use App\Service\ExcelGenService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class FactureController extends AbstractController
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
        $this->filename = "files/systeme/facture/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"] 
        ]) ;
    }
    
    #[Route('/facture/creation', name: 'ftr_creation')]
    public function factureCreation(): Response
    {
        $modeles = $this->entityManager->getRepository(FactModele::class)->findAll() ; 
        $types = $this->entityManager->getRepository(FactType::class)->findAll() ; 
        $paiements = $this->entityManager->getRepository(FactPaiement::class)->findAll() ; 

        $clients = $this->entityManager->getRepository(CltHistoClient::class)->findBy([
            "agence" => $this->agence 
        ]) ; 

        $devises = $this->entityManager->getRepository(Devise::class)->findBy([
            "agence" => $this->agence,
            "statut" => True
        ]) ; 
        $filename = "files/systeme/stock/stock_general(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateProduitStockGeneral($filename, $this->agence) ;
        
        $stockGenerales = json_decode(file_get_contents($filename)) ;
        $agcDevise = $this->appService->getAgenceDevise($this->agence) ;

        return $this->render('facture/creation.html.twig', [
            "filename" => "facture",
            "titlePage" => "Création Facture",
            "with_foot" => true,
            "modeles" => $modeles,
            "types" => $types,
            "paiements" => $paiements,
            "clients" => $clients,
            "stockGenerales" => $stockGenerales,
            "devises" => $devises,
            "agcDevise" => $agcDevise
        ]);
    }

    #[Route('/facture/consultation', name: 'ftr_consultation')]
    public function factureConsultation(): Response
    { 
        $filename = $this->filename."facture(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateFacture($filename, $this->agence) ;

        $factures = json_decode(file_get_contents($filename)) ;

        $modeles = $this->entityManager->getRepository(FactModele::class)->findAll() ; 
        $types = $this->entityManager->getRepository(FactType::class)->findAll() ; 

        $clients = $this->entityManager->getRepository(CltHistoClient::class)->findBy([
            "agence" => $this->agence 
        ]) ; 

        $critereDates = $this->entityManager->getRepository(FactCritereDate::class)->findAll() ;

        return $this->render('facture/consultation.html.twig', [
            "filename" => "facture",
            "titlePage" => "Consultation Facture",
            "with_foot" => false,
            "factures" => $factures,
            "modeles" => $modeles,
            "types" => $types,
            "clients" => $clients,
            "critereDates" => $critereDates
        ]);
    }

    #[Route('/facture/activite/details/{id}', name: 'ftr_details_activite' , defaults : ["id" => null])]
    public function factureDetailsActivites($id)
    {
        $facture = $this->entityManager->getRepository(Facture::class)->find($id) ;

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
        
        return $this->render('facture/detailsFacture.html.twig', [
            "filename" => "facture",
            "titlePage" => "Détails Facture",
            "with_foot" => true,
            "facture" => $infoFacture,
            "factureDetails" => $elements
        ]) ;
    }

    #[Route('/facture/creation/save', name: 'fact_save_activites')]
    public function factSaveActivities(Request $request)
    {
        // dd($request->request) ; 
        $fact_modele = $request->request->get('fact_modele') ; 
        $fact_type = $request->request->get('fact_type') ; 
        $fact_paiement = $request->request->get('fact_paiement') ; 
        $fact_client = $request->request->get('fact_client') ; 
        $facture_editor = $request->request->get('facture_editor') ; 
        $fact_lieu = $request->request->get('fact_lieu') ; 
        $fact_date = $request->request->get('fact_date') ; 
        $fact_num = $request->request->get('fact_num') ;
        $fact_libelle = $request->request->get('fact_libelle') ;

        // dd($fact_libelle) ;
        $fact_enr_total_general = $request->request->get('fact_enr_total_general') ;

        $data = [
            $fact_modele,
            $fact_type,
            $fact_client
        ] ;
        
        $dataMessage = [
            "Modele",
            "Type",
            "Client"
        ];

        $type = $this->entityManager->getRepository(FactType::class)->find($fact_type) ; 
        if($type->getReference() == "DF")
        {
            array_push($data,$fact_paiement) ;
            array_push($dataMessage,"Paiement") ;
        }
        
        $paiement = $this->entityManager->getRepository(FactPaiement::class)->find($fact_paiement) ; 

        if(!is_null($paiement))
        {
            if(!is_null($paiement->getLibelleCaption()))
            {
                array_push($data,$fact_libelle) ;
                array_push($dataMessage,$paiement->getLibelleCaption()) ;
            }

            if(!is_null($paiement->getNumCaption()))
            {
                array_push($data,$fact_num) ;
                array_push($dataMessage,$paiement->getNumCaption()) ;
            }
        }
        

        $result = $this->appService->verificationElement($data, $dataMessage) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $fact_enr_prod_type = (array)$request->request->get('fact_enr_prod_type') ;
        if(empty($fact_enr_prod_type))
        {
            $result["type"] = "orange" ;
            $result["message"] = "Veuiller insérer un élément" ;
            return new JsonResponse($result) ;
        }

        $fact_type_remise_prod_general = !empty($request->request->get('fact_type_remise_prod_general')) ? $this->entityManager->getRepository(FactRemiseType::class)->find($request->request->get('fact_type_remise_prod_general')) : null ; 
        if(!is_null($fact_type_remise_prod_general))
            $fact_remise_prod_general = !empty($request->request->get('fact_remise_prod_general')) ? $request->request->get('fact_type_remise_prod_general') : null ; 
        else
            $fact_remise_prod_general = null ;
        
        $fact_enr_total_tva = $request->request->get('fact_enr_total_tva') ; 

        $client = $this->entityManager->getRepository(CltHistoClient::class)->find($fact_client) ; 
        
        $modele = $this->entityManager->getRepository(FactModele::class)->find($fact_modele) ; 
        
        $lastRecordFacture = $this->entityManager->getRepository(Facture::class)->findOneBy([], ['id' => 'DESC']);
        $numFacture = !is_null($lastRecordFacture) ? ($lastRecordFacture->getId()+1) : 1 ;
        $numFacture = str_pad($numFacture, 3, "0", STR_PAD_LEFT);
        $numFacture = $type->getReference()."-".$numFacture."/".date('y') ; 
        
        $fact_enr_val_devise = $request->request->get('fact_enr_val_devise') ; 
        $fact_enr_val_devise = empty($fact_enr_val_devise) ? null : $this->entityManager->getRepository(Devise::class)->find($fact_enr_val_devise) ;

        $facture = new Facture() ;

        $facture->setAgence($this->agence) ;
        $facture->setUser($this->userObj) ;
        $facture->setClient($client) ;
        $facture->setType($type);
        $facture->setModele($modele) ;
        $facture->setRemiseType($fact_type_remise_prod_general) ;
        $facture->setRemiseVal($fact_remise_prod_general) ;
        $facture->setNumFact($numFacture) ;
        $facture->setDescription($facture_editor) ;
        $facture->setTvaVal(floatval($fact_enr_total_tva)) ;
        $facture->setLieu($fact_lieu) ;
        $dateTime = \DateTimeImmutable::createFromFormat('d/m/Y', $fact_date);
        $facture->setDate($dateTime) ;
        $facture->setTotal(floatval($fact_enr_total_general)) ;
        $facture->setDevise($fact_enr_val_devise) ;
        $facture->setStatut(True) ;
        $facture->setCreatedAt(new \DateTimeImmutable) ;
        $facture->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($facture) ;
        $this->entityManager->flush() ;

        $histoPaiement = new FactHistoPaiement() ;

        /*
            Statut : 
                - Payee
                - En_cours
        */
        $statutPaiement = "" ; 
        switch ($type->getReference()) {
            case 'DF':
                $statutPaiement = "Payee" ;
                break;
            case 'PR':
                $statutPaiement = "En_cours" ;
            default:
                $statutPaiement = "En_attente" ;
                break;
        }

        $fact_libelle = empty($fact_libelle) ? null : $fact_libelle ;
        $fact_num = empty($fact_num) ? null : $fact_num ;
        
        $histoPaiement->setLibelle($fact_libelle) ;
        $histoPaiement->setNumero($fact_num) ;
        $histoPaiement->setPaiement($paiement) ;
        $histoPaiement->setFacture($facture) ;
        $histoPaiement->setStatutPaiement($statutPaiement) ;
        
        $this->entityManager->persist($histoPaiement) ;
        $this->entityManager->flush() ;

        $fact_enr_prod_designation = $request->request->get('fact_enr_prod_designation') ;
        $fact_enr_prod_quantite = $request->request->get('fact_enr_prod_quantite') ;
        $fact_enr_prod_prix = $request->request->get('fact_enr_prod_prix') ;
        $fact_enr_text_prix = $request->request->get('fact_enr_text_prix') ;
        $fact_enr_prod_remise_type = $request->request->get('fact_enr_prod_remise_type') ;
        $fact_enr_prod_remise = $request->request->get('fact_enr_prod_remise') ;
        $fact_enr_prod_tva_val = $request->request->get('fact_enr_prod_tva_val') ;

        foreach ($fact_enr_prod_type as $key => $value) {
            $factDetail = new FactDetails() ;
            $typeRemiseUnit = !empty($fact_enr_prod_remise_type[$key]) ? $this->entityManager->getRepository(FactRemiseType::class)->find($fact_enr_prod_remise_type[$key]) : null ;
            $remiseVal = 0 ;

            if(!is_null($typeRemiseUnit))
            {
                $remiseVal = !empty($fact_enr_prod_remise[$key]) ? $fact_enr_prod_remise[$key] : null ; 
            }
            else
                $remiseVal = null ;

            if($fact_enr_prod_type[$key] != "autre")
            {
                $factDetail->setActivite($fact_enr_prod_type[$key]) ;
                $factDetail->setEntite($fact_enr_prod_prix[$key]) ;
            }
            
            $dtlsTvaVal = empty($fact_enr_prod_tva_val[$key]) ? null : $fact_enr_prod_tva_val[$key] ;

            $factDetail->setFacture($facture) ; 
            $factDetail->setRemiseType($typeRemiseUnit) ;
            $factDetail->setRemiseVal($remiseVal) ;
            $factDetail->setDesignation($fact_enr_prod_designation[$key]) ;
            $factDetail->setQuantite($fact_enr_prod_quantite[$key]) ;
            $factDetail->setPrix($fact_enr_text_prix[$key]) ;
            $factDetail->setTvaVal($dtlsTvaVal) ;

            $this->entityManager->persist($factDetail) ;
            $this->entityManager->flush() ; 
        } 

        $filename = $this->filename."facture(agence)/".$this->nameAgence ;
        unlink($filename);
        if(!file_exists($filename))
            $this->appService->generateFacture($filename, $this->agence) ;
        return new JsonResponse($result) ;
    }

    #[Route('/facture/items/search', name: 'facture_search_items')]
    public function factureSearchItems(Request $request)
    {
        $filename = $this->filename."facture(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateFacture($filename, $this->agence) ;

        $factures = json_decode(file_get_contents($filename)) ;

        $idT = $request->request->get('idT') ;
        $idM = $request->request->get('idM') ;
        $id = $request->request->get('id') ;
        $idC = $request->request->get('idC') ;
        $currentDate = $request->request->get('currentDate') ;
        $dateFacture = $request->request->get('dateFacture') ;
        $dateDebut = $request->request->get('dateDebut') ;
        $dateFin = $request->request->get('dateFin') ;
        $annee = $request->request->get('annee') ;
        $mois = $request->request->get('mois') ;

        
        $search = [
            "idT" => $idT,
            "idM" => $idM,
            "id" => $id,
            "idC" => $idC,
            "currentDate" => $currentDate,
            "dateFacture" => $dateFacture,
            "dateDebut" => $dateDebut,
            "dateFin" => $dateFin,
            "annee" => $annee,
            "mois" => $mois,
        ] ;

        foreach ($search as $key => $value) {
            if($value == "undefined")
            {
                $search[$key] = "" ;
            }
        } 

        $factures = $this->appService->searchData($factures,$search) ;

        $response = $this->renderView("facture/searchFacture.html.twig", [
            "factures" => $factures
        ]) ;

        return new Response($response) ; 
    }
}
