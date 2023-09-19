<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\CmdBonCommande;
use App\Entity\CmdStatut;
use App\Entity\FactCritereDate;
use App\Entity\FactDetails;
use App\Entity\FactHistoPaiement;
use App\Entity\Facture;
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

        $filename = "files/systeme/commande/commande(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;
        if(!file_exists($filename))
            $this->appService->generateCommande($filename,$this->agence) ;

        $bonCommandes = json_decode(file_get_contents($filename)) ;

        $dataFactures = [] ;

        foreach($factures as $facture)
        {
            $passe = False ;
            foreach($bonCommandes as $bonCommande)
            {
                if($bonCommande->facture == $facture->id)
                {
                    $passe = True ;
                    break;
                }
            }

            if(!$passe)
            {
                array_push($dataFactures,$facture) ;
            }
        }

        return $this->render('commande/creation.html.twig', [
            "filename" => "commande",
            "titlePage" => "Création bon de commande",
            "with_foot" => true,
            "factures" => $dataFactures,
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

    #[Route('/commande/boncommande/imprimer/{idBonCommande}/{idModeleEntete}/{idModeleBas}', name: 'cmd_commande_detail_imprimer', 
    defaults: [
        "idBonCommande" => null,
        "idModeleEntete" => null,
        "idModeleBas" => null]
    )]
    public function commandeImprimerBonCommande($idBonCommande,$idModeleEntete,$idModeleBas)
    {
        // $idModeleEntete = $request->request->get("idModeleEntete") ;
        // $idModeleBas = $request->request->get("idModeleBas") ;
        // $idFacture = $request->request->get("idFacture") ;
        $bonCommande = $this->entityManager->getRepository(CmdBonCommande::class)->find($idBonCommande) ;
        
        $facture = $bonCommande->getFacture() ;

        // $facture = $this->entityManager->getRepository(Facture::class)->find($idFacture) ;
        
        $dataFacture = [
            "numBonCommande" => $bonCommande->getNumBonCmd() ,
            "numFact" => $facture->getNumFact() ,
            "type" => $facture->getType()->getReference() == "DF" ? "" : $facture->getType()->getNom() ,
            "lettre" => $this->appService->NumberToLetter($facture->getTotal()) ,
            "deviseLettre" => is_null($this->agence->getDevise()) ? "" : $this->agence->getDevise()->getLettre() 
        ] ;

        $client = $facture->getClient() ;

        $dataClient = [
            "nom" => "",   
            "adresse" => "",   
            "telephone" => "",   
        ] ;

        if(!is_null($client))
        {
            if(!is_null($client->getSociete()))
            {
                $dataClient = [
                    "nom" => $client->getSociete()->getNom(),   
                    "adresse" => $client->getSociete()->getAdresse(),   
                    "telephone" => $client->getSociete()->getTelFixe(),   
                ] ;
            }
            else
            {
                $dataClient = [
                    "nom" => $client->getClient()->getNom(),   
                    "adresse" => $client->getClient()->getAdresse(),   
                    "telephone" => $client->getClient()->getTelephone(),   
                ] ;
            }
        }

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
            // $contentEntete = $imageLeft." ".$modeleEntete->getContenu();
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
            // $contentBas = $modeleBas->getContenu() ;
        }

        $details = $this->entityManager->getRepository(FactDetails::class)->findBy([
            "facture" => $facture,
            "statut" => True,
        ]) ;

        $dataDetails = [] ;
        $totalHt = 0 ;
        $totalTva = 0 ;

        foreach ($details as $detail) {
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
            array_push($dataDetails,$element) ;

            $totalHt += $total ;
            $totalTva += $tva ;
        } 

        $dataFacture["totalHt"] = $totalHt ;
        $dataFacture["totalTva"] = $totalTva ;
        $dataFacture["remise"] = $this->appService->getFactureRemise($facture,$totalHt) ; 
        $dataFacture["devise"] = !is_null($facture->getDevise()) ;
        $dataFacture["date"] = $facture->getDate()->format("d/m/Y") ;
        $dataFacture["lieu"] = $facture->getLieu() ;

        if(!is_null($facture->getDevise()))
        {
            $dataFacture["deviseCaption"] = $facture->getDevise()->getLettre() ;
            $dataFacture["deviseValue"] = number_format($facture->getTotal()/$facture->getDevise()->getMontantBase(),2,","," ")." ".$facture->getDevise()->getSymbole();
        }

        $contentIMpression = $this->renderView("commande/impressionBonCommande.html.twig",[
            "contentEntete" => $contentEntete,
            "contentBas" => $contentBas,
            "facture" => $dataFacture,
            "client" => $dataClient,
            "details" => $dataDetails,
        ]) ;

        $pdfGenService = new PdfGenService() ;

        $pdfGenService->generatePdf($contentIMpression,$this->nameUser) ;
        
        // Redirigez vers une autre page pour afficher le PDF
        return $this->redirectToRoute('display_pdf');
    }
    

    #[Route('/commande/boncommande/details/{id}', name: 'cmd_details_bon_commande', defaults: ["id" => null])]
    public function commandeDetailsBonCommande($id)
    {
        $bonCommande = $this->entityManager->getRepository(CmdBonCommande::class)->find($id) ;
        
        $facture = $bonCommande->getFacture() ;
        
        $infoFacture = [] ;

        $infoFacture["numBonCommande"] = $bonCommande->getNumBonCmd() ;

        $infoFacture["id"] = $id ;
        $infoFacture["statut"] = $bonCommande->getStatut()->getNom() ;
        $infoFacture["refStatut"] = $bonCommande->getStatut()->getReference() ;
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

        // $response = $this->renderView('commande/commandeFactureDetails.html.twig',[
        //     "facture" => $infoFacture,
        //     "factureDetails" => $elements
        // ]) ;
        
        return $this->render('commande/detailsBonCommande.html.twig', [
            "filename" => "commande",
            "titlePage" => "Bon de Commande",
            "with_foot" => true,
            "facture" => $infoFacture,
            "factureDetails" => $elements
        ]) ;

    }
}
