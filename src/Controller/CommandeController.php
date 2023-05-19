<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\CmdBonCommande;
use App\Entity\CmdStatut;
use App\Entity\FactCritereDate;
use App\Entity\FactDetails;
use App\Entity\FactHistoPaiement;
use App\Entity\Facture;
use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
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
        $this->filename = "files/systeme/commande/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"] 
        ]) ;
    }

    #[Route('/commande/creation', name: 'cmd_creation')]
    public function index(): Response
    {
        $filename = "files/systeme/facture/facture(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateFacture($filename, $this->agence) ;

        $factures = json_decode(file_get_contents($filename)) ;

        $lastRecordBonCommande = $this->entityManager->getRepository(CmdBonCommande::class)->findOneBy([], ['id' => 'DESC']);
        $numBonCommande = !is_null($lastRecordBonCommande) ? ($lastRecordBonCommande->getId()+1) : 1 ;
        $numBonCommande = str_pad($numBonCommande, 5, "0", STR_PAD_LEFT);

        return $this->render('commande/creation.html.twig', [
            "filename" => "commande",
            "titlePage" => "Création bon de commande",
            "with_foot" => true,
            "factures" => $factures,
            "numBonCommande" => $numBonCommande
        ]);
    }
    
    #[Route('/commande/facture/display', name: 'cmd_facture_display')]
    public function commandeDisplayFacture(Request $request)
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

        $response = $this->renderView('commande/commandeFactureDetails.html.twig',[
            "facture" => $infoFacture,
            "factureDetails" => $elements
        ]) ;

        return new Response($response) ;
    }

    #[Route('/commande/boncommande/save', name: 'cmd_save_bon_commande')]
    public function commandeSaveBonCommande(Request $request)
    {
        $cmd_lieu = $request->request->get('cmd_lieu') ;
        $cmd_date = $request->request->get('cmd_date') ;
        $cmd_facture = $request->request->get('cmd_facture') ;
        $cmd_creation_description = $request->request->get('cmd_creation_description') ;

        $data = [
            $cmd_facture,
            $cmd_lieu,
            $cmd_date
        ];

        $dataMessage = [
            "Facture",
            "Lieu",
            "Date"
        ] ;

        $result = $this->appService->verificationElement($data,$dataMessage) ;
        
        if(!$result["allow"])
            return new JsonResponse($result) ;
        
        $facture = $this->entityManager->getRepository(Facture::class)->find($cmd_facture) ;

        $lastRecordBonCommande = $this->entityManager->getRepository(CmdBonCommande::class)->findOneBy([], ['id' => 'DESC']);
        $numBonCommande = !is_null($lastRecordBonCommande) ? ($lastRecordBonCommande->getId()+1) : 1 ;
        $numBonCommande = str_pad($numBonCommande, 5, "0", STR_PAD_LEFT);

        $cmdStatut = $this->entityManager->getRepository(CmdStatut::class)->findOneBy([
            "reference" => "ECR"
        ]) ;

        $bonCommande = new CmdBonCommande() ;

        $bonCommande->setAgence($this->agence) ;
        $bonCommande->setFacture($facture) ;
        $bonCommande->setNumBonCmd($numBonCommande) ;
        $bonCommande->setDate(\DateTime::createFromFormat('j/m/Y',$cmd_date)) ;
        $bonCommande->setLieu($cmd_lieu) ;
        $bonCommande->setDescription($cmd_creation_description) ;
        $bonCommande->setStatut($cmdStatut) ;
        $bonCommande->setCreatedAt(new \DateTimeImmutable) ;
        $bonCommande->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($bonCommande) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."bonCommande(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;
        if(!file_exists($filename))
            $this->appService->generateBonCommande($filename,$this->agence) ;
        
        return new JsonResponse($result) ;
    }

    #[Route('/commande/boncommande/consultation', name: 'cmd_bon_commande_consultation')]
    public function commandeConsultationBonCommande()
    {
        $critereDates = $this->entityManager->getRepository(FactCritereDate::class)->findAll() ;

        $filename = $this->filename."bonCommande(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateBonCommande($filename,$this->agence) ;

        $bonCommandes = json_decode(file_get_contents($filename)) ; 
        return $this->render('commande/consultation.html.twig', [
            "filename" => "commande",
            "titlePage" => "Consultation bon de commande",
            "with_foot" => false,
            "critereDates" => $critereDates,
            "bonCommandes" => $bonCommandes
        ]);
    }

    #[Route('/commande/boncommande/check', name: 'cmd_check_bon_commande')]
    public function commandeCheckBonCommande(Request $request)
    {
        $id = $request->request->get('id') ;
        $bonCommande = $this->entityManager->getRepository(CmdBonCommande::class)->find($id) ;
        $cmdStatut = $this->entityManager->getRepository(CmdStatut::class)->findOneBy([
            "reference" => "VLD"
        ]) ;

        $bonCommande->setStatut($cmdStatut) ;

        $this->entityManager->flush() ;

        $filename = $this->filename."bonCommande(agence)/".$this->nameAgence ;
        
        if(file_exists($filename))
            unlink($filename) ;
        
        return new JsonResponse([
            "type" => "green",
            "message" => "Bon de commande validé avec succès"
        ]) ;
    }

    #[Route('/commande/boncommande/details/{id}', name: 'cmd_details_bon_commande', defaults: ["id" => null])]
    public function commandeDetailsBonCommande($id)
    {
        $bonCommande = $this->entityManager->getRepository(CmdBonCommande::class)->find($id) ;
        
        $facture = $bonCommande->getFacture() ;
        
        $infoFacture = [] ;

        $infoFacture["numBonCommande"] = $bonCommande->getNumBonCmd() ;

        $infoFacture["numFact"] = $facture->getNumFact() ;
        $infoFacture["modele"] = $facture->getModele()->getNom() ;
        $infoFacture["type"] = $facture->getType()->getNom() ;
        $infoFacture["date"] = $bonCommande->getDate()->format("d/m/Y") ;
        $infoFacture["lieu"] = $bonCommande->getLieu() ;

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

        // $response = $this->renderView('commande/commandeFactureDetails.html.twig',[
        //     "facture" => $infoFacture,
        //     "factureDetails" => $elements
        // ]) ;
        
        return $this->render('commande/detailsBonCommande.html.twig', [
            "filename" => "commande",
            "titlePage" => "Détails Bon de Commande",
            "with_foot" => true,
            "facture" => $infoFacture,
            "factureDetails" => $elements
        ]) ;

    }
}
