<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\CmdBonCommande;
use App\Entity\CmdStatut;
use App\Entity\FactCritereDate;
use App\Entity\FactDetails;
use App\Entity\FactHistoPaiement;
use App\Entity\Facture;
use App\Entity\LvrDetails;
use App\Entity\LvrLivraison;
use App\Entity\User;
use App\Service\AppService;
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
            "username" => $this->user["username"] 
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
                "typeSource" => "BonCommande"
            ]) ;
            
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
        
        if($source == "BonCommande") // Bon de commande
        {
            $filename = "files/systeme/commande/commande(agence)/".$this->nameAgence ;
            if(file_exists($filename))
                unlink($filename) ;
            if(!file_exists($filename))
                $this->appService->generateCommande($filename,$this->agence) ;

            $bonCommandes = json_decode(file_get_contents($filename)) ;

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
        
        return new JsonResponse($result) ;
    }

    
    #[Route('/livraison/activities/consultation', name: 'lvr_consultation_livraison')]
    public function lvrConsultationLivraison()
    {
        $filename = $this->filename."bonLivraison(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateBonLivraison($filename,$this->agence) ;
        $livraisons = json_decode(file_get_contents($filename)) ;

        $critereDates = $this->entityManager->getRepository(FactCritereDate::class)->findAll() ;

        return $this->render('livraison/consultation.html.twig', [
            "filename" => "livraison",
            "titlePage" => "Consultation bon de livraison",
            "with_foot" => false,
            "critereDates" => $critereDates,
            "livraisons" => $livraisons
        ]); 
    }
}
