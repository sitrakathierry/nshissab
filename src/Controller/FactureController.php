<?php

namespace App\Controller;

use App\Entity\AgdAcompte;
use App\Entity\AgdCategorie;
use App\Entity\AgdEcheance;
use App\Entity\Agence;
use App\Entity\BtpCategorie;
use App\Entity\BtpElement;
use App\Entity\BtpEnoncee;
use App\Entity\BtpPrix;
use App\Entity\CltHistoClient;
use App\Entity\CrdDetails;
use App\Entity\CrdFinance;
use App\Entity\CrdStatut;
use App\Entity\Devise;
use App\Entity\FactCritereDate;
use App\Entity\FactDetails;
use App\Entity\FactHistoPaiement;
use App\Entity\FactModele;
use App\Entity\FactPaiement;
use App\Entity\FactRemiseType;
use App\Entity\FactSupDetailsPbat;
use App\Entity\FactType;
use App\Entity\Facture;
use App\Entity\LctContrat;
use App\Entity\LctPaiement;
use App\Entity\LctRepartition;
use App\Entity\LctStatutLoyer;
use App\Entity\SavAnnulation;
use App\Entity\SavDetails;
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
        $modeles = $this->entityManager->getRepository(FactModele::class)->findBy([
            "parent" => NULL
        ],[
            "rang" => "ASC"
            ]) ; 
        $types = $this->entityManager->getRepository(FactType::class)->findAll() ; 
        $paiements = $this->entityManager->getRepository(FactPaiement::class)->findBy([],["rang" => "ASC"]) ; 

        $filename = "files/systeme/client/client(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateCltClient($filename, $this->agence) ;

        $clients = json_decode(file_get_contents($filename)) ;

        return $this->render('facture/creation.html.twig', [
            "filename" => "facture",
            "titlePage" => "Création Facture",
            "with_foot" => true,
            "modeles" => $modeles,
            "types" => $types,
            "paiements" => $paiements,
            "clients" => $clients,
        ]);
    }

    #[Route('/facture/modele/get', name: 'ftr_modele_get')]
    public function factureGetModele(Request $request)
    {
        $id = $request->request->get('id') ;
        $modele = $this->entityManager->getRepository(FactModele::class)->find($id) ;
        $modeles = $this->entityManager->getRepository(FactModele::class)->findBy([
            "parent" => $modele
        ],[
            "rang" => "ASC"
        ]) ; 

        $responses = $this->renderView("facture/factGetModele.html.twig", [
            "modeles" => $modeles
        ]) ;

        return new Response($responses) ; 
    }

    #[Route('/facture/creation/produit', name: 'ftr_creation_produit')]
    public function factureCreationProduit(): Response
    {
        $devises = $this->entityManager->getRepository(Devise::class)->findBy([
            "agence" => $this->agence,
            "statut" => True
        ]) ; 

        $typeRemises = $this->entityManager->getRepository(FactRemiseType::class)->findAll() ; 

        $filename = "files/systeme/stock/stock_general(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateProduitStockGeneral($filename, $this->agence) ;
        
        $stockGenerales = json_decode(file_get_contents($filename)) ;
        $agcDevise = $this->appService->getAgenceDevise($this->agence) ;

        $responses = $this->renderView("facture/produit.html.twig",[
            "stockGenerales" => $stockGenerales,
            "devises" => $devises,
            "agcDevise" => $agcDevise,
            "typeRemises" => $typeRemises,
        ]) ;

        return new Response($responses) ;
    }

    #[Route('/facture/creation/prest/batiment', name: 'ftr_creation_prest_batiment')]
    public function factureCreationPrestBatiment(): Response
    {
        $devises = $this->entityManager->getRepository(Devise::class)->findBy([
            "agence" => $this->agence,
            "statut" => True
        ]) ; 

        $filename = "files/systeme/prestations/batiment/enoncee(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateEnonceePrestBatiment($filename, $this->agence) ;
        
        $enoncees = json_decode(file_get_contents($filename)) ;

        $filename = "files/systeme/prestations/batiment/element(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generatePrestBatiment($filename, $this->agence) ;
        
        $elements = json_decode(file_get_contents($filename)) ;

        $agcDevise = $this->appService->getAgenceDevise($this->agence) ;

        $responses = $this->renderView("facture/prestBatiment.html.twig",[
            "elements" => $elements,
            "devises" => $devises,
            "agcDevise" => $agcDevise,
            "enoncees" => $enoncees,
            ]) ;

        return new Response($responses) ;
    }

    #[Route('/facture/creation/prest/service', name: 'ftr_creation_prest_service')]
    public function factureCreationPrestService(): Response
    {
        $devises = $this->entityManager->getRepository(Devise::class)->findBy([
            "agence" => $this->agence,
            "statut" => True
        ]) ; 

        $filename = "files/systeme/prestations/service(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generatePrestationService($filename, $this->agence) ;

        $services = json_decode(file_get_contents($filename)) ;

        $agcDevise = $this->appService->getAgenceDevise($this->agence) ;

        $responses = $this->renderView("facture/prestStandard.html.twig",[
            "devises" => $devises,
            "agcDevise" => $agcDevise,
            "services" => $services
        ]) ;

        return new Response($responses) ;
    }

    #[Route('/facture/batiment/categorie', name: 'ftr_batiment_categorie_get_opt')]
    public function ftrGetCategorieBatimentOpt(Request $request): Response
    {
        $idEnonce = $request->request->get('id') ;

        $enonce = $this->entityManager->getRepository(BtpEnoncee::class)->find($idEnonce) ;

        $categories = $this->entityManager->getRepository(BtpCategorie::class)->findBy(["enonce" => $enonce]) ; 
        
        $responses = $this->renderView('facture/factBtpCategorie.html.twig', [
            "categories" => $categories
            ]) ;

        return new Response($responses) ;
    }

    #[Route('/facture/batiment/element/prix', name: 'ftr_btm_element_prix_get_opt')]
    public function ftrBtpGetPrixElement(Request $request): Response
    {
        $idelement = $request->request->get('id') ;

        $element = $this->entityManager->getRepository(BtpElement::class)->find($idelement) ;

        $prixs = $this->entityManager->getRepository(BtpPrix::class)->findBy([
            "element" => $element, 
            "statut" => True
            ]) ; 
        
        $responses = $this->renderView('facture/factBtpPrixElement.html.twig', [
            "prixs" => $prixs,
            "element" => $element
        ]) ;

        return new Response($responses) ;
    }

    public static function comparaisonDates($a, $b) {
        $dateA = \DateTime::createFromFormat('d/m/Y', $a['date']);
        $dateB = \DateTime::createFromFormat('d/m/Y', $b['date']);
        return $dateB <=> $dateA;
    }


    #[Route('/facture/consultation', name: 'ftr_consultation')]
    public function factureConsultation(): Response
    { 

        $filename = $this->filename."facture(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateFacture($filename, $this->agence) ;

        $factures = json_decode(file_get_contents($filename)) ;

        $search = [
            "specification" => "NONE"
        ] ;
        
        $item1 = $this->appService->searchData($factures, $search) ;
        $item1 = $this->appService->objectToArray($item1) ;

        $filename = "files/systeme/sav/annulation(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateSavAnnulation($filename,$this->agence) ;
        
        $annulations = json_decode(file_get_contents($filename)) ;

        $search = [
            "refSpec" => "AVR"
        ] ;

        $item2 = $this->appService->searchData($annulations,$search) ;
        $item2 = $this->appService->formatAnnulationToFacture($item2) ;

        $item2 = $this->appService->objectToArray($item2) ;

        $search = [
            "refSpec" => "ACN"
        ] ;

        $item3 = $this->appService->searchData($annulations,$search) ;
        
        $item3 = $this->appService->formatAnnulationToFacture($item3) ;

        $factures = array_merge($item1, $item2, $item3);

        usort($factures, [self::class, 'comparaisonDates']);

        $modeles = $this->entityManager->getRepository(FactModele::class)->findAll() ; 

        $tabModeles = [] ;

        foreach ($modeles as $modele) {
            if(!is_null($modele->getReference()))
            {
                $item = [] ;

                $item["id"] = $modele->getId() ;
                $item["nom"] = $modele->getNom() ;
                $item["reference"] = $modele->getReference() ;

                array_push($tabModeles,$item) ;
            }
        }

        $modeles = $tabModeles ;

        $types = $this->entityManager->getRepository(FactType::class)->findAll() ; 

        // $clients = $this->entityManager->getRepository(CltHistoClient::class)->findBy([
        //     "agence" => $this->agence 
        // ]) ; 

        $filename = "files/systeme/client/client(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateCltClient($filename, $this->agence) ;
            
        $clients = json_decode(file_get_contents($filename)) ;

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

    #[Route('/facture/retenu/consultation', name: 'ftr_retenu_consultation')]
    public function factureRetenusConsultation(): Response
    { 
        $filename = $this->filename."facture(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateFacture($filename, $this->agence) ;

        $factures = json_decode(file_get_contents($filename)) ;

        $search = 
        [
            "specification" => "RMB"
            ] ;
        
        $item1 = $this->appService->searchData($factures, $search) ;

        $filename = "files/systeme/sav/annulation(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateSavAnnulation($filename,$this->agence) ;
        
        $annulations = json_decode(file_get_contents($filename)) ;

        $search = [
            "refSpec" => "RMB"
        ] ;

        $annulations = $this->appService->searchData($annulations,$search) ;
        
        $item2 = $this->appService->formatAnnulationToFacture($annulations) ;

        $factures = array_merge($item1, $item2);

        $modeles = $this->entityManager->getRepository(FactModele::class)->findAll() ; 
        $types = $this->entityManager->getRepository(FactType::class)->findAll() ; 

        $clients = $this->entityManager->getRepository(CltHistoClient::class)->findBy([
            "agence" => $this->agence 
        ]) ; 

        $critereDates = $this->entityManager->getRepository(FactCritereDate::class)->findAll() ;

        return $this->render('facture/consultationFactRetenu.html.twig', [
            "filename" => "facture",
            "titlePage" => "Facture Retenus",
            "with_foot" => false,
            "factures" => $factures,
            "modeles" => $modeles,
            "types" => $types,
            "clients" => $clients,
            "critereDates" => $critereDates
        ]);
    }

    #[Route('/facture/activite/details/{id}/{nature}', name: 'ftr_details_activite' , defaults : ["id" => null,"nature" => "FACTURE"])]
    public function factureDetailsActivites($id,$nature)
    {
        if ($nature == "ANL")
        {
            $annulation = $this->entityManager->getRepository(SavAnnulation::class)->find($id) ;
            $facture = $annulation->getFacture() ;
        }
        else
        {
            $facture = $this->entityManager->getRepository(Facture::class)->find($id) ;
        }
        

        $infoFacture = [] ;

        $infoFacture["numFact"] = $facture->getNumFact() ;
        $infoFacture["modele"] = $facture->getModele()->getNom() ;
        $infoFacture["type"] = $facture->getType()->getNom() ;
        $infoFacture["date"] = $facture->getDate()->format("d/m/Y") ;
        $infoFacture["lieu"] = $facture->getLieu() ;
        $infoFacture["refType"] = $facture->getType()->getReference() ;

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

        if($nature == "FACTURE")
        {
            $factureDetails = $this->entityManager->getRepository(FactDetails::class)->findBy([
                "facture" => $facture
            ],["statut" => "DESC"]) ;
        }
        else
        {
            $annulationDetails = $this->entityManager->getRepository(SavDetails::class)->findBy([
                "annulation" => $annulation
            ]) ;

            $factureDetails = [] ;

            foreach ($annulationDetails as $annulationDetail) {
               array_push($factureDetails,$annulationDetail->getFactureDetail()) ;
            }
        }
        
        
        $totalHt = 0 ;
        $totalTva = 0 ; 
        $elements = [] ;
        foreach ($factureDetails as $factureDetail) {
            $tva = (($factureDetail->getPrix() * $factureDetail->getTvaVal()) / 100) * $factureDetail->getQuantite();
            $total = $factureDetail->getPrix() * $factureDetail->getQuantite()  ;
            $remise = $this->appService->getFactureRemise($factureDetail,$totalHt) ; 
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
            $element["statut"] = $factureDetail->isStatut();
            $element["total"] = $total ;
            array_push($elements,$element) ;

            if($factureDetail->isStatut() && $nature == "FACTURE")
            {
                $totalHt += $total ;
            }
            else if($nature == "ANL")
            {
                $totalHt += $total ;
                $totalTva += $tva ;
            }
        } 

        $infoFacture["totalHt"] = $totalHt ;

        $remiseG = $this->appService->getFactureRemise($facture,$totalHt) ; 

        $infoFacture["remise"] = $remiseG ;
        $infoFacture["lettre"] = $this->appService->NumberToLetter($facture->getTotal()) ;
        

        if ($nature == "ANL") {
            $total = $annulation->getMontant() ;

            $retenu = 0 ;

            if($annulation->getPourcentage() == 0)
            {
                $retenu = "-" ;
                $signe = "" ;
                $avoir = $annulation->getMontant() ;
            }
            else
            {
                $retenu = ($annulation->getMontant() * $annulation->getPourcentage()) / 100 ;
                $signe = "(".$annulation->getPourcentage()."%)" ;
                $avoir = $annulation->getMontant() - $retenu ;
            }
            $infoFacture["numFact"] = $annulation->getNumFact() ;
            $infoFacture["totalTva"] = $totalTva ;
            $infoFacture["total"] = $total ;
            $infoFacture["retenu"] = $retenu ;
            $infoFacture["signe"] = $signe ;
            $infoFacture["specification"] = $annulation->getSpecification()->getNom() ;
            $infoFacture["motif"] = $annulation->getMotif()->getNom() ;
            $infoFacture["avoir"] = $avoir ;
            $infoFacture["specs"] = $annulation->getSpecification()->getReference() ;
            $infoFacture["lettre"] = $this->appService->NumberToLetter($total) ;
        }

        return $this->render('facture/detailsFacture.html.twig', [
            "filename" => "facture",
            "titlePage" => "Détails Facture",
            "with_foot" => true,
            "facture" => $infoFacture,
            "factureDetails" => $elements,
            "nature" => $nature
        ]) ;
    }

    #[Route('/facture/creation/save', name: 'fact_save_activites')]
    public function factSaveActivities(Request $request)
    {
        $fact_modele = $request->request->get('fact_modele') ; 
        $fact_type = $request->request->get('fact_type') ; 
        $fact_paiement = $request->request->get('fact_paiement') ; 
        $fact_client = $request->request->get('fact_client') ; 
        $facture_editor = $request->request->get('facture_editor') ; 
        $fact_lieu = $request->request->get('fact_lieu') ; 
        $fact_date = $request->request->get('fact_date') ; 
        $fact_num = $request->request->get('fact_num') ;
        $fact_libelle = $request->request->get('fact_libelle') ;

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
            if($paiement->getReference() != "CR" && $paiement->getReference() != "AC")
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
        }

        $result = $this->appService->verificationElement($data, $dataMessage) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $modele = $this->entityManager->getRepository(FactModele::class)->find($fact_modele) ; 

        if($modele->getReference() == "PROD" || $modele->getReference() == "PSTD")
        {
            $fact_enr_prod_type = (array)$request->request->get('fact_enr_prod_type') ;
            if(empty($fact_enr_prod_type))
            {
                $result["type"] = "orange" ;
                $result["message"] = "Veuiller insérer un élément" ;
                return new JsonResponse($result) ;
            }
        }else if($modele->getReference() == "PBAT")
        {
            $fact_enr_btp_enonce_id = (array)$request->request->get('fact_enr_btp_enonce_id') ;
            if(empty($fact_enr_btp_enonce_id))
            {
                $result["type"] = "orange" ;
                $result["message"] = "Veuiller insérer un élément" ;
                return new JsonResponse($result) ;
            }
        }

        

        $fact_libelle = empty($fact_libelle) ? null : $fact_libelle ;

        if(!is_null($fact_libelle) && ($paiement->getReference() == "CR" || $paiement->getReference() == "AC"))
        {
            if(!is_numeric($fact_libelle))
            {
                $result["type"] = "orange" ;
                $result["message"] = "Le montant payé n'est pas valide" ;
                return new JsonResponse($result) ;
            }
        } 

        // DEBUT D'INSERTION DE DONNEE

        $fact_type_remise_prod_general = !empty($request->request->get('fact_type_remise_prod_general')) ? $this->entityManager->getRepository(FactRemiseType::class)->find($request->request->get('fact_type_remise_prod_general')) : null ; 
        if(!is_null($fact_type_remise_prod_general))
            $fact_remise_prod_general = !empty($request->request->get('fact_remise_prod_general')) ? $request->request->get('fact_remise_prod_general') : null ; 
        else
            $fact_remise_prod_general = null ;
        
        $fact_enr_total_tva = $request->request->get('fact_enr_total_tva') ; 

        $client = $this->entityManager->getRepository(CltHistoClient::class)->find($fact_client) ; 
        
        
        
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
                break;
            default:
                $statutPaiement = "En_attente" ;
                break;
        }

        // $fact_libelle = empty($fact_libelle) ? null : $fact_libelle ;
        $fact_num = empty($fact_num) ? null : $fact_num ;
        
        $histoPaiement->setLibelle($fact_libelle) ;
        $histoPaiement->setNumero($fact_num) ;
        $histoPaiement->setPaiement($paiement) ;
        $histoPaiement->setFacture($facture) ;
        $histoPaiement->setStatutPaiement($statutPaiement) ;
        
        $this->entityManager->persist($histoPaiement) ;
        $this->entityManager->flush() ;

        // INSERTION DES DETAILS DE LA FACTURE 
        // PAR RAPPORT AU MODELE 
        // PORTANT DES REFERENCES SPECIFIQUES
        $modeleRef = $modele->getReference() ;
        if($modeleRef == "PROD" || $modeleRef == "PSTD") // Produit ou Prestation Standard
        {
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
                $factDetail->setStatut(True) ;
                $this->entityManager->persist($factDetail) ;
                $this->entityManager->flush() ; 
            }
        } 
        else if($modeleRef == "PBAT")
        {
            
            $fact_enr_btp_categorie_id = $request->request->get('fact_enr_btp_categorie_id') ;
            $fact_enr_btp_element_id = $request->request->get('fact_enr_btp_element_id') ;
            $fact_enr_btp_designation = $request->request->get('fact_enr_btp_designation') ;
            $fact_enr_btp_prix = $request->request->get('fact_enr_btp_prix') ;
            $fact_enr_btp_quantite = $request->request->get('fact_enr_btp_quantite') ;
            $fact_enr_btp_tva = $request->request->get('fact_enr_btp_tva') ;
            $fact_enr_btp_info_sup = $request->request->get('fact_enr_btp_info_sup') ;
            

            foreach ($fact_enr_btp_enonce_id as $key => $value) {
                $dtlsTvaVal = empty($fact_enr_btp_tva[$key]) ? null : $fact_enr_btp_tva[$key] ;

                $factDetail = new FactDetails() ;

                $factDetail->setActivite("BtpElement") ;
                $factDetail->setEntite($fact_enr_btp_element_id[$key]) ;
                $factDetail->setFacture($facture) ; 
                $factDetail->setDesignation($fact_enr_btp_designation[$key]) ;
                $factDetail->setQuantite($fact_enr_btp_quantite[$key]) ;
                $factDetail->setPrix($fact_enr_btp_prix[$key]) ;
                $factDetail->setTvaVal($dtlsTvaVal) ;
                $factDetail->setStatut(True) ;

                $this->entityManager->persist($factDetail) ;
                $this->entityManager->flush() ; 

                $enoncee = $this->entityManager->getRepository(BtpEnoncee::class)->find($fact_enr_btp_enonce_id[$key]) ;
                $categorie = $this->entityManager->getRepository(BtpCategorie::class)->find($fact_enr_btp_categorie_id[$key]) ;

                $factSupDetailsPbat = new FactSupDetailsPbat() ;

                $factSupDetailsPbat->setEnonce($enoncee) ;
                $factSupDetailsPbat->setCategorie($categorie) ;
                $factSupDetailsPbat->setDetail($factDetail) ; 
                $factSupDetailsPbat->setInfoSup($fact_enr_btp_info_sup[$key]) ;

                $this->entityManager->persist($factSupDetailsPbat) ;
                $this->entityManager->flush() ; 
            }
        }

        // INSERTION DE FINANCE : CREDIT et ACOMPTE (reference CR et AC)
        if(!is_null($paiement))
        {
            if($paiement->getReference() == "CR" || $paiement->getReference() == "AC")
            {
                $lastRecordFinance = $this->entityManager->getRepository(CrdFinance::class)->findOneBy([], ['id' => 'DESC']);
                $numFinance = !is_null($lastRecordFinance) ? ($lastRecordFinance->getId()+1) : 1 ;
                $numFinance = str_pad($numFinance, 5, "0", STR_PAD_LEFT);
                $refFncStatut = "ECR" ; 
                
                $crdStatut = $this->entityManager->getRepository(CrdStatut::class)->findOneBy([
                        "reference" => $refFncStatut
                    ]) ;

                $finance = new CrdFinance() ;

                $finance->setAgence($this->agence) ;
                $finance->setFacture($facture) ;
                $finance->setPaiement($paiement) ;
                $finance->setNumFnc($numFinance) ;
                $finance->setStatut($crdStatut) ; 
                $finance->setCreatedAt(new \DateTimeImmutable) ; 
                $finance->setUpdatedAt(new \DateTimeImmutable) ; 

                $this->entityManager->persist($finance) ;
                $this->entityManager->flush() ; 

                if(!is_null($fact_libelle))
                {
                    $crdDetail = new CrdDetails() ;

                    $crdDetail->setFinance($finance) ; 
                    $crdDetail->setDate(\DateTime::createFromFormat('j/m/Y',$fact_date)) ;
                    $crdDetail->setMontant(floatval($fact_libelle)) ;
                    $crdDetail->setAgence($this->agence) ;

                    $this->entityManager->persist($crdDetail) ;
                    $this->entityManager->flush() ; 
                }
                if ($paiement->getReference() == "CR") { 
                    // GESTION AGENDA
                    $agd_ech_enr_date = (array)$request->request->get('agd_ech_enr_date') ;
                    $agd_ech_enr_montant   = $request->request->get('agd_ech_enr_montant') ;

                    $refCategorie = $paiement->getReference() == "CR" ? "CRD" : "ACP" ;

                    $categorie = $this->entityManager->getRepository(AgdCategorie::class)->findOneBy([
                        "reference" => $refCategorie
                    ]) ;

                    foreach ($agd_ech_enr_date as $key => $value) {
                        // GESTION DE DATE ULTERIEURE
                        $dateActuelle = date('d/m/Y') ;
                        $dateEcheance = $value ;

                        $dateInf = $this->appService->compareDates($dateEcheance,$dateActuelle,"P") ;

                        if($dateInf)
                        {
                            return new JsonResponse([
                                "type" => "orange",
                                "message" => "Désolé. La date de l'échéance doit être supérieure à la date actuelle"
                                ]) ;
                        }
                        
                        $echeance = new AgdEcheance() ;

                        $echeance->setAgence($this->agence) ;
                        $echeance->setCategorie($categorie) ;
                        $echeance->setCatTable($finance) ;
                        $echeance->setDate(\DateTime::createFromFormat('j/m/Y',$value)) ;
                        $echeance->setMontant($agd_ech_enr_montant[$key]) ;
                        $echeance->setStatut(True) ;
                        $echeance->setCreatedAt(new \DateTimeImmutable) ; 
                        $echeance->setUpdatedAt(new \DateTimeImmutable) ; 

                        $this->entityManager->persist($echeance) ;
                        $this->entityManager->flush() ; 
                    }

                    if(!empty($agd_ech_enr_date))
                    {
                        $filename = "files/systeme/agenda/agenda(agence)/".$this->nameAgence ;
                        if(file_exists($filename))
                        {
                            unlink($filename) ;
                        }
                    }
                }
                else // Deuxième cas si le mode paiement est sous acompte : AC
                {
                    $agd_acp_date = $request->request->get('agd_acp_date') ;
                    $agd_acp_objet = $request->request->get('agd_acp_objet') ;

                    if(!empty($agd_acp_date))
                    {
                        $agd_acompte = new AgdAcompte() ;

                        $agd_acompte->setAcompte($finance) ;
                        $agd_acompte->setAgence($this->agence) ;
                        $agd_acompte->setObjet($agd_acp_objet) ;
                        $agd_acompte->setDate(\DateTime::createFromFormat('j/m/Y',$agd_acp_date)) ;
                        $agd_acompte->setStatut(True) ;
                        
                        $this->entityManager->persist($agd_acompte) ;
                        $this->entityManager->flush() ;

                        $filename = "files/systeme/agenda/agenda(agence)/".$this->nameAgence ;
                        if(file_exists($filename))
                        {
                            unlink($filename) ;
                        }
                    }
                }
            }
        }
        // gestion des fichiers 

        $filename = $this->filename."facture(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename);
        if(!file_exists($filename))
            $this->appService->generateFacture($filename, $this->agence) ;
        
        if(!is_null($paiement))
        {
            if($paiement->getReference() == "AC")
            {
                $filename = "files/systeme/credit/acompte(agence)/".$this->nameAgence ;
                if(file_exists($filename))
                unlink($filename);
            }
            else if($paiement->getReference() == "CR")
            {
                $filename = "files/systeme/credit/credit(agence)/".$this->nameAgence ;
                if(file_exists($filename))
                unlink($filename);
            }
        }

        
        
        return new JsonResponse($result) ;
    }

    #[Route('/facture/prestation/location/list/get', name: 'fact_list_prest_location_get')]
    public function factGetItemPrestLocation()
    {
        $filename = "files/systeme/prestations/location/contrat(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateLocationContrat($filename, $this->agence) ; 

        $contrats = json_decode(file_get_contents($filename)) ;

        $search = [
            "refStatut" => "ENCR",
        ] ;

        $contrats = $this->appService->searchData($contrats,$search) ;

        $filename = "files/systeme/prestations/location/bailleur(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateLocationBailleur($filename, $this->agence) ; 

        $bailleurs = json_decode(file_get_contents($filename)) ;

        $filename = "files/systeme/prestations/location/locataire(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateLocationLocataire($filename, $this->agence) ; 

        $locataires = json_decode(file_get_contents($filename)) ;

        $filename = "files/systeme/prestations/location/bail(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateLocationBails($filename, $this->agence) ; 

        $tabBails = json_decode(file_get_contents($filename)) ;

        $response = $this->renderView("facture/prestLocationGetList.html.twig",[
            "contrats" => $contrats,
            "bails" => $tabBails,
            "bailleurs" => $bailleurs,
            "locataires" => $locataires,  
        ]) ;

        return new Response($response) ;
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

    #[Route('/facture/creation/prest/location', name: 'fact_creation_prest_location')]
    public function factureCreationPrestLocation()
    {
        // $filename = $this->filename."location/locataire(agence)/".$this->nameAgence ;

        // if(!file_exists($filename))
        //     $this->appService->generateLocationLocataire($filename, $this->agence) ; 

        // $locataires = json_decode(file_get_contents($filename)) ;

        $filename = "files/systeme/prestations/location/contrat(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateLocationContrat($filename, $this->agence) ; 

        $contrats = json_decode(file_get_contents($filename)) ;

        return $this->render('facture/creationFactPrestLocation.html.twig', [
            "filename" => "facture",
            "titlePage" => "Création Facture : Prestation Location",
            "with_foot" => true,
            "contrats" => $contrats,
        ]);
    }

    #[Route('/facture/prest/location/contrat/get', name: 'fact_prest_loctr_get_contrat')]
    public function factureGetContratPrestLocation(Request $request)
    {
        $id = $request->request->get('id') ;
        $contrat = $this->entityManager->getRepository(LctContrat::class)->find($id) ;
        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        $item = [] ;

        if($contrat->getPeriode()->getReference() == "J")
        {
            $periode = "Jour(s)" ;
        }
        else if($contrat->getPeriode()->getReference() == "M")
        {
            $periode = "Mois" ;
        } 
        else if($contrat->getPeriode()->getReference() == "A")
        {
            $periode = "An(s)" ;
        }

        $item["id"] = $contrat->getId() ;
        $item["agence"] = $contrat->getAgence()->getId() ;
        $item["numContrat"] = $contrat->getNumContrat() ;
        $item["dateContrat"] = $contrat->getDateContrat()->format("d/m/Y") ;
        $item["bailleur"] = $contrat->getBailleur()->getNom() ;
        $item["bail"] = $contrat->getBail()->getNom() ;
        $item["locataire"] = $contrat->getLocataire()->getNom() ;
        $item["cycle"] = $contrat->getCycle()->getNom() ;
        $item["dateDebut"] = $contrat->getDateDebut()->format("d/m/Y") ;
        $item["dateFin"] = $contrat->getDateFin()->format("d/m/Y") ;
        $item["dureeContrat"] = $contrat->getDuree()." ".$periode ;
        $item["montantForfait"] = $contrat->getMontantForfait() ;
        $item["forfaitLibelle"] = $contrat->getForfait()->getLibelle() ;
        $item["refForfait"] = $contrat->getForfait()->getReference() ;
        $item["typePaiement"] = $contrat->getForfait()->getNom() ;
        $item["statut"] = $contrat->getStatut()->getNom() ;
        
        $tableauMois = [] ;

        $statutLoyerPaye = $this->entityManager->getRepository(LctStatutLoyer::class)->findOneBy([
            "reference" => "PAYE"
        ]) ;
        
        if($contrat->getCycle()->getReference() == "CMOIS")
        {
            $moisExist = null ;
            if($contrat->getForfait()->getReference() == "FMOIS")
            {
                $lastPaiement = $this->entityManager->getRepository(LctPaiement::class)->findOneBy([
                    "contrat" => $contrat
                ],["id" => "DESC"]) ;

                $elemExistant = [] ;
                    
                if(!is_null($lastPaiement))
                {
                    $moisEcoule = $this->entityManager->getRepository(LctRepartition::class)->findBy([
                        "contrat" => $contrat,
                        "statut" => $statutLoyerPaye
                    ]) ;
                    
                    $moisEcoule = !is_null($moisEcoule) ? count($moisEcoule) : 0 ;

                    $lastRepart = $this->entityManager->getRepository(LctRepartition::class)->findOneBy([
                        "paiement" => $lastPaiement
                    ],["id" => "DESC"]) ;

                    $statutLastRpt = $lastRepart->getStatut()->getReference() ;
                    
                    if($statutLastRpt == "ACOMPTE")
                    {
                        $elemExistant = [
                            "montant" => $lastRepart->getMontant(),
                            "statut" => '<span class="text-info font-weight-bold">'.$statutLastRpt.'</span>',
                        ] ;
                        $dateDebut = $lastRepart->getDateDebut()->format("d/m/Y") ;
                        $moisExist = $lastRepart->getMois() ;
                    }
                    else if($statutLastRpt == "PAYE")
                    {
                        $moisExist = $lastRepart->getMois() + 1 ;
                        $dateDebut = $this->appService->calculerDateApresNjours($lastRepart->getDateDebut()->format("d/m/Y"),30) ;
                    }
                    else
                    {
                        $moisEcoule = 0 ;
                        $dateDebut = $contrat->getDateDebut()->format("d/m/Y") ;
                    }
                }
                else
                {
                    $moisEcoule = 0 ;
                    $dateDebut = $contrat->getDateDebut()->format("d/m/Y") ;
                }

                $frequence = is_null($contrat->getFrequenceRenouv()) ? 1 : $contrat->getFrequenceRenouv() ;

                if($contrat->getPeriode()->getReference() == "M")
                    $duree = $contrat->getDuree() * $frequence;  
                else if($contrat->getPeriode()->getReference() == "A")
                    $duree = $contrat->getDuree() * 12 * $frequence; 
                
                $duree -= $moisEcoule ;

                $dateAvant = $this->appService->calculerDateAvantNjours($dateDebut,30) ;
                if($contrat->getModePaiement()->getReference() == "DEBUT")
                {
                    $dateGenere =  $dateAvant ;
                }
                else
                {
                    if(!is_null($lastPaiement))
                        $dateGenere = $this->appService->calculerDateAvantNjours($dateDebut,30) ;
                    else
                        $dateGenere = $dateDebut ;
                }
                
                $tableauMois = $this->appService->genererTableauMois($dateGenere,$duree, $contrat->getDateLimite(), $moisExist) ;

                $count = count($tableauMois);
                
                if(!empty($elemExistant))
                {
                    $tableauMois[0]["montantInitial"] = $elemExistant["montant"] ;
                    $tableauMois[0]["statut"] = $elemExistant["statut"] ;
                }
                else
                {
                    $tableauMois[0]["montantInitial"] = 0 ;
                    $tableauMois[0]["statut"] = "-" ;
                }

                for ($i=0; $i < count($tableauMois); $i++) { 
                    if($i == 0)
                    {
                        $premierAnnee = explode("/",$tableauMois[$i]["finLimite"])[2] ;
                        $tableauMois[$i]["annee"] = $premierAnnee ;
                    }
                    if ($i + 1 < $count && $tableauMois[$i]["indexMois"] > $tableauMois[$i + 1]["indexMois"]) {
                        $tableauMois[$i + 1]["annee"] = $tableauMois[$i]["annee"] + 1;
                    } 

                    $tableauMois[$i]["designation"] = "LOYER ".$contrat->getBail()->getNom()." | ".$contrat->getBail()->getLieux() ;
                    if($i != 0)
                    {
                        $tableauMois[$i]["montantInitial"] = 0 ;
                    }
                }
                
                $response = $this->renderView("facture/location/paiementMensuel.html.twig",[
                    "item" => $item,
                    "tableauMois" => $tableauMois,
                    "elemExistant" => $elemExistant,
                ]) ;
            } 
        }
        else if($contrat->getCycle()->getReference() == "CJOUR")
        { 
            if($contrat->getForfait()->getReference() == "FJOUR")
            {
                $lastPaiement = $this->entityManager->getRepository(LctPaiement::class)->findOneBy([
                    "contrat" => $contrat
                ],["id" => "DESC"]) ;

                $elemExistant = [] ;

                if(!is_null($lastPaiement))
                {
                    $jourEcoule = $this->entityManager->getRepository(LctRepartition::class)->findBy([
                        "contrat" => $contrat,
                        "statut" => $statutLoyerPaye
                    ]) ;
                    
                    $jourEcoule = !is_null($jourEcoule) ? count($jourEcoule) : 0 ;

                    $lastRepart = $this->entityManager->getRepository(LctRepartition::class)->findOneBy([
                        "paiement" => $lastPaiement
                    ],["id" => "DESC"]) ;

                    $statutLastRpt = $lastRepart->getStatut()->getReference() ;
                    if($statutLastRpt == "ACOMPTE")
                    {
                        $elemExistant = [
                            "montant" => $lastRepart->getMontant(),
                            "statut" => '<span class="text-info font-weight-bold">'.$statutLastRpt.'</span>',
                        ] ;
                        $dateDebut = $lastRepart->getDateDebut()->format("d/m/Y") ;
                    }
                    else if($statutLastRpt == "PAYE")
                    {
                        $dateDebut = $this->appService->calculerDateApresNjours($lastRepart->getDateDebut()->format("d/m/Y"),1) ;
                    }
                    else
                    {
                        $moisEcoule = 0 ;
                        $dateDebut = $contrat->getDateDebut()->format("d/m/Y") ;
                    }
                }
                else
                {
                    $jourEcoule = 0 ;
                    $dateDebut = $contrat->getDateDebut()->format("d/m/Y") ;
                }

                $dateAvant = $this->appService->calculerDateAvantNjours($dateDebut,1) ;
                $dateGenere = $dateAvant ;
                $duree = $contrat->getDuree() - $jourEcoule ;
                $tableauMois = $this->appService->genererTableauJour($dateGenere,$duree) ;
                
                if(!empty($elemExistant))
                {
                    $tableauMois[0]["montantInitial"] = $elemExistant["montant"] ;
                    $tableauMois[0]["statut"] = $elemExistant["statut"] ;
                }
                else
                {
                    $tableauMois[0]["montantInitial"] = 0 ;
                }

                for ($i=0; $i < count($tableauMois); $i++) { 
                    $tableauMois[$i]["designation"] = "LOYER ".$contrat->getBail()->getNom()." | ".$contrat->getBail()->getLieux() ;
                    if($i != 0)
                    {
                        $tableauMois[$i]["montantInitial"] = 0 ;
                    }
                }

                $response = $this->renderView("facture/location/paiementJournaliere.html.twig",[
                    "item" => $item,
                    "tableauMois" => $tableauMois,
                    "elemExistant" => $elemExistant,
                    // "dateLimite" => $contrat->getDateLimite(),
                    // "bail" => $contrat->getBail()->getNom(),
                    // "adresse" => $contrat->getBail()->getLieux()
                ]) ;
            }
        } 

        if($contrat->getForfait()->getReference() == "FORFAIT")
        {
            $lastPaiement = $this->entityManager->getRepository(LctPaiement::class)->findOneBy([
                "contrat" => $contrat
            ],["id" => "DESC"]) ;

            $elemExistant = [] ;

            if(!is_null($lastPaiement))
            {
                $jourEcoule = $this->entityManager->getRepository(LctRepartition::class)->findBy([
                    "contrat" => $contrat,
                    "statut" => $statutLoyerPaye
                ]) ;
                
                $jourEcoule = !is_null($jourEcoule) ? count($jourEcoule) : 0 ;

                $lastRepart = $this->entityManager->getRepository(LctRepartition::class)->findOneBy([
                    "paiement" => $lastPaiement
                ],["id" => "DESC"]) ;

                $statutLastRpt = $lastRepart->getStatut()->getReference() ;
                if($statutLastRpt == "ACOMPTE")
                {
                    $elemExistant = [
                        "montant" => $lastRepart->getMontant(),
                        "statut" => '<span class="text-info font-weight-bold">'.$statutLastRpt.'</span>',
                    ] ;
                }
            }

            $tableauMois = [[
                "annee" => date('Y'),
                "statut" =>'-',
            ]] ;
            if(!empty($elemExistant))
                {
                    $tableauMois[0]["montantInitial"] = $elemExistant["montant"] ;
                    $tableauMois[0]["statut"] = $elemExistant["statut"] ;
                }
                else
                {
                    $tableauMois[0]["montantInitial"] = 0 ;
                }

                for ($i=0; $i < count($tableauMois); $i++) { 
                    $tableauMois[$i]["designation"] = "LOYER ".$contrat->getBail()->getNom()." | ".$contrat->getBail()->getLieux() ;
                    if($i != 0)
                    {
                        $tableauMois[$i]["montantInitial"] = 0 ;
                    }
                } 
            $response = $this->renderView("facture/location/paiementForfaitaire.html.twig",[
                "item" => $item,
                "tableauMois" => $tableauMois,
                "elemExistant" => $elemExistant,
            ]) ;
        }

        return new Response($response) ;
    }

    #[Route('/facture/prest/location/save', name: 'fact_prest_location_save')]
    public function factureSavePrestLocation(Request $request)
    {
        $fact_prest_lct_numContrat = $request->request->get("fact_prest_lct_numContrat") ; 
        $location_paiement_editor = $request->request->get("location_paiement_editor") ; 
        // $fact_prest_lct_date_paiement = $request->request->get("fact_prest_lct_date_paiement") ; 
        $fact_prest_lct_mtn_a_payer = $request->request->get("fact_prest_lct_mtn_a_payer") ; 

        $fact_date = $request->request->get("fact_date") ; 
        $fact_lieu = $request->request->get("fact_lieu") ; 
        

        $result = $this->appService->verificationElement([
            $fact_prest_lct_numContrat,
            $fact_prest_lct_mtn_a_payer
        ], [
            "Numéro contrat",
            "Montant à payer"
            ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;
        
        // AJOUT PAIEMENT DE LOYER 

        $lastRecordPaiement = $this->entityManager->getRepository(LctPaiement::class)->findOneBy([], ['id' => 'DESC']);
        $numPaiement = !is_null($lastRecordPaiement) ? ($lastRecordPaiement->getId()+1) : 1 ;
        $numPaiement = str_pad($numPaiement, 4, "0", STR_PAD_LEFT)."/".date('y');
        
        $contrat = $this->entityManager->getRepository(LctContrat::class)->find($fact_prest_lct_numContrat) ;
        
        $paiement = new LctPaiement() ;
        
        $paiement->setAgence($this->agence) ;
        $paiement->setContrat($contrat) ;
        $paiement->setDate(\DateTime::createFromFormat('j/m/Y',$fact_date)) ;
        $paiement->setLieu($fact_lieu) ;
        $paiement->setMontant($fact_prest_lct_mtn_a_payer) ;
        $paiement->setNumReleve($numPaiement) ;
        $paiement->setIndication("LOYER") ;
        $paiement->setDescription($location_paiement_editor) ;

        $this->entityManager->persist($paiement) ;
        $this->entityManager->flush() ; 
        
        $partie_date_debut = $request->request->get("partie_date_debut") ;
        $partie_date_limite = $request->request->get("partie_date_limite") ;
        $partie_designation = $request->request->get("partie_designation") ;
        $partie_montant_payee = $request->request->get("partie_montant_payee") ;
        $partie_statut = (array)$request->request->get("partie_statut") ;
        $partie_mois = $request->request->get("partie_mois") ;
        $partie_annee = $request->request->get("partie_annee") ;
        
        foreach ($partie_statut as $key => $value) {
            # code...
            if($value == "")
                break ;
            $statutLoyer = $this->entityManager->getRepository(LctStatutLoyer::class)->findOneBy([
                "reference" => $partie_statut[$key]
            ]) ;
            
            $partie_debut = $partie_date_debut == NULL ? NULL : \DateTime::createFromFormat('j/m/Y',$partie_date_debut[$key]) ;
            $partie_limite = $partie_date_limite == NULL ? NULL : \DateTime::createFromFormat('j/m/Y',$partie_date_limite[$key]) ;
            $partie_mois[$key] = $partie_mois[$key] == "" ? NULL : $partie_mois[$key] ;

            $repartition = new LctRepartition() ;
    
            $repartition->setContrat($contrat) ;
            $repartition->setPaiement($paiement) ;
            $repartition->setMois($partie_mois[$key]) ;
            $repartition->setAnnee($partie_annee[$key]) ;
            $repartition->setMontant($partie_montant_payee[$key]) ;
            $repartition->setDateDebut($partie_debut) ;
            $repartition->setDateLimite($partie_limite) ;
            $repartition->setDesignation($partie_designation[$key]) ;
            $repartition->setStatut($statutLoyer) ;
            $repartition->setCreatedAt(new \DateTimeImmutable) ;
            $repartition->setUpdatedAt(new \DateTimeImmutable) ;
    
            $this->entityManager->persist($repartition) ;
            $this->entityManager->flush() ;
        }

        $filename = "files/systeme/prestations/location/releveloyer(agence)/relevePL_".$contrat->getId()."_".$this->nameAgence  ;

        if(file_exists($filename))
            unlink($filename) ;

        $filename = $this->filename."location/commission(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse($result) ;
    }
}
