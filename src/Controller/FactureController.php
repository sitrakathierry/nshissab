<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\CltHistoClient;
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
        $this->filename = "files/systeme/caisse/" ;
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
        
        $filename = "files/systeme/stock/stock_general(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateProduitStockGeneral($filename, $this->agence) ;
        
        $stockGenerales = json_decode(file_get_contents($filename)) ;

        return $this->render('facture/creation.html.twig', [
            "filename" => "facture",
            "titlePage" => "Création Facture",
            "with_foot" => true,
            "modeles" => $modeles,
            "types" => $types,
            "paiements" => $paiements,
            "clients" => $clients,
            "stockGenerales" => $stockGenerales
        ]);
    }

    #[Route('/facture/consultation', name: 'ftr_consultation')]
    public function factureConsultation(): Response
    { 
        $factures = $this->entityManager->getRepository(Facture::class)->findBy([
            "agence" => $this->agence
        ]) ; 



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
        $data = [
            $fact_modele,
            $fact_type,
            $fact_paiement,
            $fact_client,
        ] ;
        
        $dataMessage = [
            "Modele",
            "Type",
            "Paiement",
            "Client"
        ];

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
        
        $fact_prod_tva = $request->request->get('fact_prod_tva') ; 

        $client = $this->entityManager->getRepository(CltHistoClient::class)->find($fact_client) ; 
        $type = $this->entityManager->getRepository(FactType::class)->find($fact_type) ; 
        $modele = $this->entityManager->getRepository(FactModele::class)->find($fact_modele) ; 
        
        $lastRecordFacture = $this->entityManager->getRepository(Facture::class)->findOneBy([], ['id' => 'DESC']);
        $numFacture = !is_null($lastRecordFacture) ? ($lastRecordFacture->getId()+1) : 1 ;
        $numFacture = str_pad($numFacture, 3, "0", STR_PAD_LEFT);
        $numFacture = $type->getReference()."-".$numFacture."/".date('y') ; 
        
        $facture = new Facture() ;

        $facture->setAgence($this->agence) ;
        $facture->setClient($client) ;
        $facture->setType($type);
        $facture->setModele($modele) ;
        $facture->setRemiseType($fact_type_remise_prod_general) ;
        $facture->setRemiseVal($fact_remise_prod_general) ;
        $facture->setNumFact($numFacture) ;
        $facture->setDescription($facture_editor) ;
        $facture->setTvaVal(intval($fact_prod_tva)) ;
        $facture->setLieu($fact_lieu) ;
        $facture->setDate(new \DateTime($fact_date)) ;
        $facture->setStatut(True) ;
        $facture->setCreatedAt(new \DateTimeImmutable) ;
        $facture->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($facture) ;
        $this->entityManager->flush() ;

        $histoPaiement = new FactHistoPaiement() ;

        $paiement = $this->entityManager->getRepository(FactPaiement::class)->find($fact_paiement) ; 
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

        $histoPaiement->setPaiement($paiement) ;
        $histoPaiement->setFacture($facture) ;
        $histoPaiement->setStatutPaiement($statutPaiement) ;
        
        $this->entityManager->persist($histoPaiement) ;
        $this->entityManager->flush() ;

        $fact_enr_prod_designation = $request->request->get('fact_enr_prod_designation') ;
        $fact_enr_prod_quantite = $request->request->get('fact_enr_prod_quantite') ;
        $fact_enr_prod_prix = $request->request->get('fact_enr_prod_prix') ;
        $fact_enr_text_prix = $request->request->get('fact_enr_prod_prix') ;
        $fact_enr_prod_remise_type = $request->request->get('fact_enr_prod_remise_type') ;
        $fact_enr_prod_remise = $request->request->get('fact_enr_prod_remise') ;

        foreach ($fact_enr_prod_type as $key => $value) {
            $factDetail = new FactDetails() ;
            $typeRemiseUnit = !empty($fact_enr_prod_remise_type[$key]) ? $this->entityManager->getRepository(FactRemiseType::class)->find($fact_enr_prod_remise_type[$key]) : null ;
            $remiseVal = 0 ;

            if(!is_null($typeRemiseUnit))
                $remiseVal = !empty($fact_enr_prod_remise[$key]) ? $fact_enr_prod_remise[$key] : null ; 
            else
                $remiseVal = null ;

            if($fact_enr_prod_type[$key] != "autre")
            {
                $factDetail->setActivite($fact_enr_prod_type[$key]) ;
                $factDetail->setEntite($fact_enr_prod_prix[$key]) ;
            }

            $factDetail->setRemiseType($typeRemiseUnit) ;
            $factDetail->setRemiseVal($remiseVal) ;
            $factDetail->setDesignation($fact_enr_prod_designation[$key]) ;
            $factDetail->setQuantite($fact_enr_prod_quantite[$key]) ;
            $factDetail->setPrix($fact_enr_text_prix[$key]) ;

            $this->entityManager->persist($factDetail) ;
            $this->entityManager->flush() ; 
        } 

        return new JsonResponse($result) ;
    }
}
