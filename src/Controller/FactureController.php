<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\CltHistoClient;
use App\Entity\Devise;
use App\Entity\FactDetails;
use App\Entity\FactHistoPaiement;
use App\Entity\FactModele;
use App\Entity\FactPaiement;
use App\Entity\FactRemiseType;
use App\Entity\FactType;
use App\Entity\Facture;
use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
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

        return $this->render('facture/consultation.html.twig', [
            "filename" => "facture",
            "titlePage" => "Consultation Facture",
            "with_foot" => false,
            "factures" => $factures
        ]);
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
        $facture->setDate(new \DateTime($fact_date)) ;
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
}
