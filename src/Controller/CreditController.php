<?php

namespace App\Controller;

use App\Entity\AgdAcompte;
use App\Entity\AgdCategorie;
use App\Entity\AgdEcheance;
use App\Entity\Agence;
use App\Entity\CltHistoClient;
use App\Entity\CrdDetails;
use App\Entity\CrdFinance;
use App\Entity\CrdStatut;
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

class CreditController extends AbstractController
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
        $this->filename = "files/systeme/credit/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"] 
        ]) ;
    }

    #[Route('/credit/consultation', name: 'crd_consultation_credit')]
    public function crdCreditConsultation(): Response
    {    
        $filename = "files/systeme/client/client(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateCltClient($filename, $this->agence) ;
            
        $clients = json_decode(file_get_contents($filename)) ; 

        $critereDates = $this->entityManager->getRepository(FactCritereDate::class)->findAll() ;
        
        $filename = $this->filename."credit(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateCredit($filename,$this->agence,"CR") ;
        
        $credits = json_decode(file_get_contents($filename)) ;

        $crdStatuts =  $this->entityManager->getRepository(CrdStatut::class)->findBy([
            "classement" => "CR"
        ],["rang" => "ASC"]) ;

        return $this->render('credit/consultationCredit.html.twig', [
            "filename" => "credit",
            "titlePage" => "Consultation Credit",
            "with_foot" => false,
            "clients" => $clients,
            "critereDates" => $critereDates,
            "credits" => $credits,
            "crdStatuts" => $crdStatuts,
            "refPaiement" => "CR"
        ]);
    }

    #[Route('/credit/details/{id}/{updated}', name: 'crd_details_credit', defaults: ["id" => null,"updated" => false] )]
    public function crdDetailsCredit($id,$updated)
    {
        $finance = $this->entityManager->getRepository(CrdFinance::class)->find($id) ;
        $this->appService->updateStatutFinance($finance) ;

        if($updated)
        {
            if($finance->getPaiement()->getReference() == "AC")
            $filename = "files/systeme/credit/acompte(agence)/".$this->nameAgence ;
            else
                $filename = "files/systeme/credit/credit(agence)/".$this->nameAgence ;
                
            if(file_exists($filename))
                unlink($filename);
        }

        $facture = $finance->getFacture() ;
        
        $infoFacture = [] ;

        $infoFacture["numFnc"] = $finance->getNumFnc() ;

        $infoFacture["id"] = $facture->getId() ;
        $infoFacture["idFinance"] = $id ;
        $infoFacture["statut"] = $finance->getStatut()->getNom() ;
        $infoFacture["refStatut"] = $finance->getStatut()->getReference() ;
        $infoFacture["numFact"] = $facture->getNumFact() ;
        $infoFacture["modele"] = $facture->getModele()->getNom() ;
        $infoFacture["type"] = $facture->getType()->getNom() ;
        $infoFacture["date"] = $facture->getDate()->format("d/m/Y") ;
        $infoFacture["lieu"] = $facture->getLieu() ;
        $infoFacture["description"] = $facture->getDescription() ;
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

            $remise = $this->appService->getFactureRemise($factureDetail,$total) ;

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
        $remiseG = $this->appService->getFactureRemise($facture,$totalHt) ;

        $infoFacture["remise"] = $remiseG ;
        $infoFacture["lettre"] = $this->appService->NumberToLetter($facture->getTotal()) ;
        
        $financeDetails = $this->entityManager->getRepository(CrdDetails::class)->findBy([
            "finance" => $finance
        ]) ;

        $refPaiement = $finance->getPaiement()->getReference() ; 
        
        $titlePage = $refPaiement == "CR" ? "Credit" : "Acompte" ;

        $refCategorie = $refPaiement == "CR" ? "CRD" : "ACP" ;

        $categorie = $this->entityManager->getRepository(AgdCategorie::class)->findOneBy([
            "reference" => $refCategorie
        ]) ;

        if($refPaiement == "AC")
        {
            $unAgdAcompte = $this->entityManager->getRepository(AgdAcompte::class)->findOneBy([
                "acompte" => $finance
            ]) ;

            $unAgdAcompte = is_null($unAgdAcompte) ? "" : $unAgdAcompte ;
            $echeances = "" ;
        }
        else
        {
            $echeances = $this->entityManager->getRepository(AgdEcheance::class)->findBy([
                "agence" => $this->agence,
                "categorie" => $categorie,
                "catTable" => $finance
            ]) ;
            
            $echeanceArray = [] ;
            foreach ($echeances as $echeance) {
                $item = [] ;

                $item["id"] = $echeance->getId() ;
                $item["date"] = $echeance->getDate()->format('d/m/Y') ;
                $item["montant"] = $echeance->getMontant() ;
                $item["statut"] = $echeance->isStatut() ? "OK" : (is_null($echeance->isStatut()) ? "NOT" : "DNONE") ;
                
                array_push($echeanceArray,$item) ;
            }
            
            $echeances = $echeanceArray ;
            $unAgdAcompte = "" ;
        }

        return $this->render('credit/detailsFinanceCredit.html.twig',[
            "filename" => "credit",
            "titlePage" => $titlePage,
            "with_foot" => true,
            "facture" => $infoFacture,
            "factureDetails" => $elements,
            "financeDetails" => $financeDetails,
            "refPaiement" => $refPaiement,
            "echeances" => $echeances,
            "unAgdAcompte" => $unAgdAcompte
        ]) ;

    }

    #[Route('/credit/echeance/imprimer/{idFinance}/{idModeleEntete}/{idModeleBas}', name: 'credit_echeance_imprimer', defaults: ["idModeleEntete" => null,"idFinance" => null, "idModeleBas" => null])]
    public function creditImprimerEcheance($idModeleEntete,$idModeleBas,$idFinance)
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

        $finance = $this->entityManager->getRepository(CrdFinance::class)->find($idFinance) ;

        $client = $finance->getFacture()->getClient() ;

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

        $financeDetails = $this->entityManager->getRepository(CrdDetails::class)->findBy([
            "finance" => $finance
        ]) ;

        $refPaiement = $finance->getPaiement()->getReference() ; 
        $infoFacture = [] ;
        $infoFacture["refStatut"] = $finance->getStatut()->getReference() ;
        $infoFacture["totalTtc"] = $finance->getFacture()->getTotal() ;

        $contentIMpression = $this->renderView("credit/impressionFactureCredit.html.twig",[
            "contentEntete" => $contentEntete,
            "contentBas" => $contentBas,
            "financeDetails" => $financeDetails,
            "client" => $dataClient,
            "finance" => $finance,
            "refPaiement" => $refPaiement,
            "facture" => $infoFacture,
            "date" => date("d/m/Y"),
        ]) ;

        $pdfGenService = new PdfGenService() ;

        $pdfGenService->generatePdf($contentIMpression,$this->nameUser) ;
        
        // Redirigez vers une autre page pour afficher le PDF
        return $this->redirectToRoute('display_pdf');
    }

    #[Route('/credit/paiement/credit/save', name: 'crd_paiement_credit_save')]
    public function crdSavePaiementCredit(Request $request)
    {
        $crd_paiement_date = $request->request->get('crd_paiement_date') ;
        $crd_paiement_montant = $request->request->get('crd_paiement_montant') ;
        $crd_id_finance = $request->request->get('crd_paiement_id') ;
        $crd_type = $request->request->get('crd_type') ;
        
        $finance = $this->entityManager->getRepository(CrdFinance::class)->find($crd_id_finance) ;

        $data = [
            $crd_paiement_date,
            $crd_paiement_montant,
            $crd_type
        ] ;

        $dataMessage = [
            "Date",
            "Montant",
            "Type"
        ] ;

        $result = $this->appService->verificationElement($data, $dataMessage) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;
        
        if ($crd_type == "ECHEANCE") {
            // GESTION AGENDA

            // GESTION DE DATE ULTERIEURE
            $dateActuelle = date('d/m/Y') ;
            $dateEcheance = $crd_paiement_date ;

            $dateInf = $this->appService->compareDates($dateEcheance,$dateActuelle,"P") ;
            if($dateInf)
            {
                return new JsonResponse([
                    "type" => "orange",
                    "message" => "Désolé. La date doit être supérieure à la date actuelle"
                    ]) ;
            }
            
            // debut d'enregistrement de l'échéance
            $paiement = $finance->getPaiement() ; 

            $refCategorie = $paiement->getReference() == "CR" ? "CRD" : "ACP" ;

            $categorie = $this->entityManager->getRepository(AgdCategorie::class)->findOneBy([
                "reference" => $refCategorie
                ]) ;

            $echeance = new AgdEcheance() ;

            $echeance->setAgence($this->agence) ;
            $echeance->setCategorie($categorie) ;
            $echeance->setCatTable($finance) ;
            $echeance->setDate(\DateTime::createFromFormat('j/m/Y',$crd_paiement_date)) ;
            $echeance->setMontant($crd_paiement_montant) ;
            $echeance->setStatut(True) ;
            $echeance->setCreatedAt(new \DateTimeImmutable) ; 
            $echeance->setUpdatedAt(new \DateTimeImmutable) ; 

            $this->entityManager->persist($echeance) ;
            $this->entityManager->flush() ; 
            
            $filename = "files/systeme/agenda/agenda(agence)/".$this->nameAgence ;
            if(file_exists($filename))
            {
                unlink($filename) ;
            }

            return new JsonResponse($result) ;
        }

        $totalFacture = $finance->getFacture()->getTotal() ; 
        $totalPayee = $this->entityManager->getRepository(CrdDetails::class)->getFinanceTotalPayee($crd_id_finance) ; 

        $ttcRestant = $totalFacture - $totalPayee["total"] ;
        $ttcRestant = $ttcRestant - $crd_paiement_montant ; 

        if($ttcRestant < 0)
        {
            $crd_paiement_montant = $crd_paiement_montant - abs($ttcRestant) ;
            $result["type"] = "green";
            $result["message"] = "Enregistrement effectué. Le montant dépasse de ".abs($ttcRestant) ; 
        }

        // DEBUT INSERTION

        $crdDetail = new CrdDetails() ;

        $crdDetail->setFinance($finance) ; 
        $crdDetail->setDate(\DateTime::createFromFormat('j/m/Y',$crd_paiement_date)) ;
        $crdDetail->setMontant(floatval($crd_paiement_montant)) ;
        $crdDetail->setAgence($this->agence) ;

        $this->entityManager->persist($crdDetail) ;
        $this->entityManager->flush() ; 
        $refPaiement = $finance->getPaiement()->getReference() ; 

        $this->appService->updateStatutFinance($finance) ;

        if($refPaiement == "AC")
            $filename = $this->filename."acompte(agence)/".$this->nameAgence ;
        else
            $filename = $this->filename."credit(agence)/".$this->nameAgence ;
            
        if(file_exists($filename))
            unlink($filename) ;
        if(!file_exists($filename))
        {
            $this->appService->generateCredit($filename,$this->agence,$refPaiement) ;
        }

        return new JsonResponse($result) ;
    }

    #[Route('/credit/acompte/consultation', name: 'crd_consultation_acompte')]
    public function crdAcompteConsultation(): Response
    {
        $filename = "files/systeme/client/client(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateCltClient($filename, $this->agence) ;
            
        $clients = json_decode(file_get_contents($filename)) ; 

        $critereDates = $this->entityManager->getRepository(FactCritereDate::class)->findAll() ;
        
        $filename = $this->filename."acompte(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateCredit($filename,$this->agence,"AC") ;
        
        $credits = json_decode(file_get_contents($filename)) ;
        
        $crdStatuts =  $this->entityManager->getRepository(CrdStatut::class)->findBy([
            "classement" => "AC"
        ],["rang" => "ASC"]) ;

        return $this->render('credit/consultationCredit.html.twig', [
            "filename" => "credit",
            "titlePage" => "Consultation Acompte",
            "with_foot" => false,
            "clients" => $clients,
            "critereDates" => $critereDates,
            "credits" => $credits,
            "crdStatuts" => $crdStatuts,
            "refPaiement" => "AC"
        ]);
    }

    #[Route('/credit/acompte/annuler', name: 'crd_acompte_annule')]
    public function crdAnnulerAcompte(Request $request)
    {
        $idFnc = $request->request->get('id') ;

        $finance = $this->entityManager->getRepository(CrdFinance::class)->find($idFnc);

        $crdStatut = $this->entityManager->getRepository(CrdStatut::class)->findOneBy([
                "reference" => "ANL"
            ]) ;

        $finance->setStatut($crdStatut) ;
        $this->entityManager->flush() ;

        if($finance->getPaiement()->getReference() == "AC")
        $filename = "files/systeme/credit/acompte(agence)/".$this->nameAgence ;
        else
            $filename = "files/systeme/credit/credit(agence)/".$this->nameAgence ;
            
        if(file_exists($filename))
            unlink($filename);
        
        return new JsonResponse([
            "type" => "green",
            "message" => "Annulation effectué"
            ]) ;
    }

    #[Route('/credit/finance/items/search', name: 'crd_acompte_credit_search_items')]
    public function crdSearchCreditAcompte(Request $request)
    {
        $refPaiement = $request->request->get('refPaiement') ;

        if($refPaiement == "AC")
            $filename = "files/systeme/credit/acompte(agence)/".$this->nameAgence ;
        else
            $filename = "files/systeme/credit/credit(agence)/".$this->nameAgence ;
        
        if(!file_exists($filename))
            $this->appService->generateCredit($filename,$this->agence,$refPaiement) ;
        
        $credits = json_decode(file_get_contents($filename)) ;

        $statut = $request->request->get('statut') ;
        $idC = $request->request->get('idC') ;
        $currentDate = $request->request->get('currentDate') ;
        $dateFacture = $request->request->get('dateFacture') ;
        $dateDebut = $request->request->get('dateDebut') ;
        $dateFin = $request->request->get('dateFin') ;
        $annee = $request->request->get('annee') ;
        $mois = $request->request->get('mois') ;

        $search = [
            "refPaiement" => $refPaiement,
            "idStatut" => $statut,
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

        $credits = $this->appService->searchData($credits,$search) ;
        $credits = array_values($credits) ; 
        $response = $this->renderView("credit/searchFinance.html.twig", [
            "credits" => $credits,
            "refPaiement" => $refPaiement
        ]) ;

        return new Response($response) ; 
    }

    #[Route('/credit/definitif/switch', name: 'crd_credit_basculer_definitif')]
    public function crdSwitchDefinitif($acompte)
    {    
        $finance = $this->entityManager->getRepository(CrdFinance::class)->find($acompte) ;
        $factureP = $finance->getFacture() ;

        $lastRecordFacture = $this->entityManager->getRepository(Facture::class)->findOneBy([], ['id' => 'DESC']);
        $numFacture = !is_null($lastRecordFacture) ? ($lastRecordFacture->getId()+1) : 1 ;
        $numFacture = str_pad($numFacture, 3, "0", STR_PAD_LEFT);
        $numFacture = "DF-".$numFacture."/".date('y') ; 

        $facture = new Facture() ;

        $facture->setAgence($this->agence) ;
        $facture->setUser($this->userObj) ;
        $facture->setClient($factureP->getClient()) ;
        $facture->setType($factureP->getType());
        $facture->setModele($factureP->getModele()) ;
        $facture->setRemiseType($factureP->getRemiseType()) ;
        $facture->setRemiseVal($factureP->getRemiseVal()) ;
        $facture->setNumFact($numFacture) ;
        $facture->setDescription($factureP->getDescription()) ;
        $facture->setTvaVal($factureP->getTvaVal()) ;
        $facture->setLieu($factureP->getLieu()) ;
        $facture->setDate($factureP->getDate()) ;
        $facture->setTotal($factureP->getTotal()) ;
        $facture->setDevise($factureP->getDevise()) ;
        $facture->setStatut(True) ;
        $facture->setFactureParent($factureP) ;
        $facture->setCreatedAt(new \DateTimeImmutable) ;
        $facture->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($facture) ;
        $this->entityManager->flush() ;

        $histoPaiementP = $this->entityManager->getRepository(FactHistoPaiement::class)->findOneBy([
            "facture" => $factureP
            ]) ;

        $histoPaiement = new FactHistoPaiement() ;

        $histoPaiement->setLibelle($histoPaiementP->getLibelle()) ;
        $histoPaiement->setNumero($histoPaiementP->getNumero()) ;
        $histoPaiement->setPaiement($histoPaiementP->getPaiement()) ;
        $histoPaiement->setFacture($histoPaiementP->getFacture()) ;
        $histoPaiement->setStatutPaiement("Payee") ;
        
        $this->entityManager->persist($histoPaiement) ;
        $this->entityManager->flush() ;

    }
}
