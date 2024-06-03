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
use App\Entity\FactPaiement;
use App\Entity\Facture;
use App\Entity\HistoHistorique;
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
            "username" => $this->user["username"],
            "agence" => $this->agence  
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

        $filename = "files/systeme/stock/entrepot(agence)/".$this->nameAgence ;
        
        if(!file_exists($filename))  
            $this->appService->generateStockEntrepot($filename,$this->agence) ;

        $entrepots = json_decode(file_get_contents($filename)) ; 

        // dd($credits) ;

        return $this->render('credit/consultationCredit.html.twig', [ 
            "filename" => "credit",
            "titlePage" => "Consultation Credit",
            "with_foot" => false,
            "clients" => $clients, 
            "critereDates" => $critereDates,
            "entrepots" => $entrepots,
            "credits" => $credits,
            "crdStatuts" => $crdStatuts,
            "refPaiement" => "CR"
        ]);
    }

    
    #[Route('/credit/suivi/general', name: 'crd_suivi_credit_general')]
    public function crdSuiviCreditGeneral(): Response
    {
        $finances = $this->entityManager->getRepository(CrdFinance::class)->generateSuiviGeneralCredit([
            "filename" => $this->filename."suiviCredit(agence)/".$this->nameAgence,
            "agence" => $this->agence,
            "appService" => $this->appService, 
        ]) ; 

        // dd($finances) ;

        $filename = "files/systeme/stock/entrepot(agence)/".$this->nameAgence ;
        
        if(!file_exists($filename))  
            $this->appService->generateStockEntrepot($filename,$this->agence) ; 

        $entrepots = json_decode(file_get_contents($filename)) ; 

        $filename = "files/systeme/client/client(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateCltClient($filename, $this->agence) ;

        $clients = json_decode(file_get_contents($filename)) ;

        $critereDates = $this->entityManager->getRepository(FactCritereDate::class)->findAll() ;
 
        return $this->render('credit/suiviCreditGeneral.html.twig', [ 
            "filename" => "credit",
            "titlePage" => "Suivi Crédit Général",
            "finances" => $finances,
            "entrepots" => $entrepots,
            "clients" => $clients,
            "critereDates" => $critereDates,
            "with_foot" => false
        ]);
    }

    #[Route('/credit/suivi/general/items/search', name: 'credit_search_suivi_items')]
    public function crdSearchItemsSuiviCreditGeneral(Request $request) 
    {
        $finances = $this->entityManager->getRepository(CrdFinance::class)->generateSuiviGeneralCredit([
            "filename" => $this->filename."suiviCredit(agence)/".$this->nameAgence,
            "agence" => $this->agence,
        ]) ; 

        $financeDetails = [] ; 

        foreach ($finances as $finance) 
        {
            $financeDetails = array_merge($financeDetails,$finance->details) ;
        }

        $idClient = $request->request->get('idClient') ;
        $idEntrepot = $request->request->get('idEntrepot') ;
        $idFinance = $request->request->get('idFinance') ;
        $currentDate = $request->request->get('currentDate') ;
        $dateSuivi = $request->request->get('dateSuivi') ;
        $dateDebut = $request->request->get('dateDebut') ;
        $dateFin = $request->request->get('dateFin') ;
        $annee = $request->request->get('annee') ;
        $mois = $request->request->get('mois') ;

        $search = [
            "idClient" => $idClient,
            "idEntrepot" => $idEntrepot,
            "idF" => $idFinance,
            "currentDate" => $currentDate,
            "dateSuivi" => $dateSuivi,
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

        $financeDetails = $this->appService->searchData($financeDetails,$search) ;

        // dd($financeDetails) ;

        if(!empty($financeDetails))
        {
            // $details = [] ;
            $finances = [] ;
            foreach ($financeDetails as $financeDetail) {
                $details[$financeDetail->idF][] = [
                    "id" => $financeDetail->id ,
                    "idF" => $financeDetail->idF,
                    "description" => $financeDetail->description,
                    "date" => $financeDetail->date ,
                    "currentDate" => $financeDetail->currentDate ,
                    "dateSuivi" => $financeDetail->dateSuivi ,
                    "dateDebut" => $financeDetail->dateDebut ,
                    "dateFin" => $financeDetail->dateFin ,
                    "annee" => $financeDetail->annee ,
                    "mois" => $financeDetail->mois ,
                    "montant" => $financeDetail->montant,
                    "num_credit" => $financeDetail->num_credit,
                    "client" => $financeDetail->client,
                    "idClient" => $financeDetail->idClient,
                    "entrepot" => $financeDetail->entrepot,
                    "idEntrepot" => $financeDetail->idEntrepot,
                    "type" => "PAIEMENT",
                    "statut" => "OK"
                ] ;

                $finances[$financeDetail->idF] = [
                    "id" => $financeDetail->idF,
                    "num_credit" => $financeDetail->num_credit,
                    "client" => $financeDetail->client,
                    "idClient" => $financeDetail->idClient,
                    "entrepot" => $financeDetail->entrepot,
                    "idEntrepot" => $financeDetail->idEntrepot,
                    "nbRow" => count($details[$financeDetail->idF]) + 2,
                    "details" => $details[$financeDetail->idF]
                ] ;
            }   
        }
        else
        {
            $finances = [] ;
        }

        $response = $this->renderView("credit/searchSuiviCredit.html.twig", [
            "finances" => $finances
        ]) ;

        return new Response($response) ; 
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
                $item["description"] = $echeance->getDescription() ;
                $item["date"] = $echeance->getDate()->format('d/m/Y') ;
                $item["montant"] = $echeance->getMontant() ;
                $item["paiement"] = is_null($echeance->getPaiement()) ? "-" : $echeance->getPaiement()->getNom() ;
                $item["statut"] = $echeance->isStatut() ? "OK" : (is_null($echeance->isStatut()) ? "NOT" : "DNONE") ;
                
                array_push($echeanceArray,$item) ;
            }
            
            $echeances = $echeanceArray ;
            $unAgdAcompte = "" ;
        }

        $csrf_token = $this->session->get("user")["csrf_token"] ;

        $dataAdmin = [
            "nameuser" => null ,
            "iduser" => null ,
        ] ;

        if(is_null($this->session->get($csrf_token."_admin")))
        {
            //  DEBUT GET INFO ADMIN

            $currentAdmin = null;

            $admins = $this->entityManager->getRepository(User::class)->findBy([
                "agence" => $this->agence,
            ]) ;

            foreach ($admins as $admin) {
                if($admin->getRoles()[0] == 'MANAGER')
                {
                    $currentAdmin = $admin ;
                    break ;
                }
            }

            if(!is_null($currentAdmin))
            { 
                $dataAdmin = [
                    "nameuser" => $currentAdmin->getUsername(),
                    "iduser" => base64_encode(urlencode($currentAdmin->getId())),
                ] ;
            }

            // FIN GET INFO ADMIN
        }

        $paiements = $this->entityManager->getRepository(FactPaiement::class)->findBy([],["rang" => "ASC"]) ; 

        return $this->render('credit/detailsFinanceCredit.html.twig',[
            "filename" => "credit",
            "titlePage" => $titlePage,
            "with_foot" => true,
            "facture" => $infoFacture,
            "factureDetails" => $elements,
            "financeDetails" => $financeDetails, 
            "paiements" => $paiements, 
            "refPaiement" => $refPaiement,
            "echeances" => $echeances,
            "unAgdAcompte" => $unAgdAcompte,
            "dataAdmin" => $dataAdmin  
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

        $filename = $this->filename."fileCheck(user)/liste_check_".$this->userObj->getId().".json"  ;

        $dataCheck = json_decode(file_get_contents($filename)) ;

        $financeDetails = [] ;

        $totalPayee = $this->entityManager->getRepository(CrdDetails::class)->getFinanceTotalPayee($finance->getId()) ;
        
        if(!empty($dataCheck))
        {
            foreach ($dataCheck as $dataCheck) {
                $financeDetail = $this->entityManager->getRepository(CrdDetails::class)->find($dataCheck) ;
                $financeDetails[] = $financeDetail ;
            }

        }
        else
        {
            $financeDetails = $this->entityManager->getRepository(CrdDetails::class)->findBy([
                "finance" => $finance
            ]) ;
        }

        $refPaiement = $finance->getPaiement()->getReference() ; 
        $infoFacture = [] ;
        $infoFacture["refStatut"] = $finance->getStatut()->getReference() ;
        $infoFacture["totalTtc"] = $finance->getFacture()->getTotal() ;

        $contentIMpression = $this->renderView("credit/impressionFactureCredit.html.twig",[
            "financeDetails" => $financeDetails,
            "contentEntete" => $contentEntete,
            "refPaiement" => $refPaiement,
            "totalPayee" => $totalPayee,
            "contentBas" => $contentBas,
            "facture" => $infoFacture,
            "client" => $dataClient,
            "date" => date("d/m/Y"),
            "finance" => $finance,
        ]) ;

        $pdfGenService = new PdfGenService() ;

        $pdfGenService->generatePdf($contentIMpression,$this->nameUser) ;
        
        if($refPaiement == "CR")
        {
            $typePaiement = "CRD" ;
            $nomPaiement = "CREDIT" ;
            $histoMessage = "Fiche de paiement credit N° : ".$finance->getNumFnc() ;
        }
        else
        {
            $typePaiement = "ACP" ;
            $nomPaiement = "ACOMPTE" ;
            $histoMessage = "Fiche de dépôt d'acompte N° : ".$finance->getNumFnc() ;
        }

        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => $typePaiement,
            "nomModule" => $nomPaiement,
            "refAction" => "IMP",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Impression ".$histoMessage,
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

        // Redirigez vers une autre page pour afficher le PDF
        return $this->redirectToRoute('display_pdf');
    }

    public static function comparaisonDates($a, $b) {
        $dateA = \DateTime::createFromFormat('d/m/Y', $a['date']);
        $dateB = \DateTime::createFromFormat('d/m/Y', $b['date']);
        return $dateB <=> $dateA;
    }
 
    #[Route('/credit/paiement/credit/save', name: 'crd_paiement_credit_save')]
    public function crdSavePaiementCredit(Request $request)
    {
        $crd_description = $request->request->get('crd_description') ;
        $crd_description = empty($crd_description) ? null : $crd_description ;
        $crd_paiement_date = $request->request->get('crd_paiement_date') ;
        $crd_paiement_montant = $request->request->get('crd_paiement_montant') ;
        $crd_id_finance = $request->request->get('crd_paiement_id') ;
        $crd_type = $request->request->get('crd_type') ;
        $crd_type_paiement = $request->request->get('crd_type_paiement') ;
        
        $finance = $this->entityManager->getRepository(CrdFinance::class)->find($crd_id_finance) ;

        $data = [
            $crd_paiement_date,
            $crd_paiement_montant,
            $crd_type,
            $crd_type_paiement
        ] ;

        $dataMessage = [
            "Date",
            "Montant",
            "Statut",
            "Type de paiement"
        ] ;

        $result = $this->appService->verificationElement($data, $dataMessage) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;
        
        $paiement = $this->entityManager->getRepository(FactPaiement::class)->find($crd_type_paiement) ; 
        
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
            $echeance->setPaiement($paiement) ;
            $echeance->setDescription($crd_description) ;
            $echeance->setDate(\DateTime::createFromFormat('j/m/Y',$crd_paiement_date)) ;
            $echeance->setMontant(floatval($crd_paiement_montant)) ;
            $echeance->setStatut(True) ;
            $echeance->setCreatedAt(new \DateTimeImmutable) ; 
            $echeance->setUpdatedAt(new \DateTimeImmutable) ; 

            $this->entityManager->persist($echeance) ;
            $this->entityManager->flush() ; 
             
            $filename = "files/systeme/agenda/agenda(agence)/".$this->nameAgence ;
            if(file_exists($filename))
                unlink($filename) ;

            $filename = $this->filename."suiviCredit(agence)/".$this->nameAgence ;
            if(file_exists($filename))
                unlink($filename) ;

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
        $crdDetail->setPaiement($paiement) ;
        $crdDetail->setDescription($crd_description) ; 
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

        if($refPaiement == "CR")
        {
            $typePaiement = "CRD" ;
            $nomPaiement = "CREDIT" ;
            $histoMessage = "Paiement credit N° : ".$finance->getNumFnc() ;
        }
        else
        {
            $typePaiement = "ACP" ;
            $nomPaiement = "ACOMPTE" ;
            $histoMessage = "Dépôt d'acompte N° : ".$finance->getNumFnc() ;
        }

        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => $typePaiement,
            "nomModule" => $nomPaiement,
            "refAction" => "CRT",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => $histoMessage,
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

        $this->appService->deleteRecette() ;

        return new JsonResponse($result) ;
    }

    #[Route('/credit/acompte/consultation', name: 'crd_consultation_acompte')]
    public function crdAcompteConsultation(): Response
    {
        $this->appService->updateAnneeData() ; 
        
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

        $filename = "files/systeme/stock/entrepot(agence)/".$this->nameAgence ;
        
        if(!file_exists($filename))  
            $this->appService->generateStockEntrepot($filename,$this->agence) ;

        $entrepots = json_decode(file_get_contents($filename)) ; 

        return $this->render('credit/consultationCredit.html.twig', [
            "filename" => "credit",
            "titlePage" => "Consultation Acompte",
            "with_foot" => false,
            "clients" => $clients,
            "critereDates" => $critereDates,
            "credits" => $credits,
            "entrepots" => $entrepots,
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
        
        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "ACP",
            "nomModule" => "ACOMPTE",
            "refAction" => "ANL",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Annulation Acompte N° : ".$finance->getNumFnc(),
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

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
        $idE = $request->request->get('idE') ;
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
            "idE" => $idE,
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

    #[Route('/credit/echeance/description/update', name: 'credit_echeance_update_description')]
    public function crdUpdateDescriptionEcheance(Request $request)
    {
        $id_echeance = $request->request->get('id_echeance') ;
        $echeance_descri = $request->request->get('echeance_descri') ;

        
        $echeance = $this->entityManager->getRepository(AgdEcheance::class)->find($id_echeance) ;

        $echeance->setDescription($echeance_descri) ;
        $this->entityManager->flush() ;

        return new JsonResponse([""]) ;
    }

    #[Route('/credit/echeance/unitaire/imprimer/{idEcheance}/{idModeleEntete}/{idModeleBas}', name: 'credit_echeance_unitaire_imprimer', defaults: ["idModeleEntete" => null,"idEcheance" => null, "idModeleBas" => null])]
    public function crdEcheanceUnitaireImprime($idEcheance,$idModeleEntete,$idModeleBas)
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

        $echeance = $this->entityManager->getRepository(AgdEcheance::class)->find($idEcheance) ;

        $client = $echeance->getCatTable()->getFacture()->getClient() ;

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

        $contentIMpression = $this->renderView("credit/impressionFactureEcheance.html.twig",[
            "contentEntete" => $contentEntete,
            "contentBas" => $contentBas,
            "echeanceDetails" => $echeance,
            "client" => $dataClient,
            "finance" => $echeance->getCatTable(),
            "date" => date("d/m/Y"),
        ]) ;

        $pdfGenService = new PdfGenService() ;

        $pdfGenService->generatePdf($contentIMpression,$this->nameUser) ;
        
        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "CRD",
            "nomModule" => "CREDIT",
            "refAction" => "IMP",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Impression Facture Echeance ; Credit N° ".$echeance->getCatTable()->getNumFnc()." ; Date : ".$echeance->getDate()->format("d/m/Y")." ; Montant : ".$echeance->getMontant(),
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

        // Redirigez vers une autre page pour afficher le PDF
        return $this->redirectToRoute('display_pdf');
    }

    #[Route('/credit/activity/authentification', name: 'credit_activity_authentification')]
    public function crdActivityAuthentification(Request $request)
    {
        $iduser = $request->request->get("id_user_admin") ;
        $pass_admin = $request->request->get("pass_admin") ;
        
        $iduser = urldecode(base64_decode($iduser));
        
        $response = $this->appService->authentificationAdministrateur($iduser, $pass_admin) ;

        if($response)
        {
            $csrf_token = $this->session->get("user")["csrf_token"] ;

            $this->session->set($csrf_token."_admin",[
                "authorised" => True
            ]) ;

            return new JsonResponse([
                "type" => "green",
                "message" => "Accès autorisée",
            ]) ;
        }

        return new JsonResponse([
            "type" => "red",
            "message" => "Accès refusée",
        ]) ;
    }

    #[Route('/credit/element/update', name: 'credit_element_update')]
    public function crdUpdateElementCredit(Request $request)
    {
        $crd_mod_description = $request->request->get("crd_mod_description") ;
        $crd_mod_date = $request->request->get("crd_mod_date") ;
        $crd_mod_montant = $request->request->get("crd_mod_montant") ;
        $idCreditDetail = $request->request->get("idCreditDetail") ;

        $result = $this->appService->verificationElement([
            $crd_mod_date,
            $crd_mod_montant
        ], [
            "Date",
            "Montant"
        ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $crdDetail = $this->entityManager->getRepository(CrdDetails::class)->find($idCreditDetail) ;

        $crdDetail->setDescription($crd_mod_description) ; 
        $crdDetail->setDate(\DateTime::createFromFormat('j/m/Y',$crd_mod_date)) ;
        $crdDetail->setMontant(floatval($crd_mod_montant)) ;

        $this->entityManager->flush() ; 

        $finance = $crdDetail->getFinance() ;

        $refPaiement = $finance->getPaiement()->getReference() ; 

        $this->appService->updateStatutFinance($finance) ;

        if($refPaiement == "AC")
            $filename = $this->filename."acompte(agence)/".$this->nameAgence ;
        else
            $filename = $this->filename."credit(agence)/".$this->nameAgence ;
            
        if(file_exists($filename))
            unlink($filename) ;

        if($refPaiement == "CR")
        {
            $typePaiement = "CRD" ;
            $nomPaiement = "CREDIT" ;
            $histoMessage = "Modification Ligne credit N° : ".$finance->getNumFnc() ;
        }
        else
        {
            $typePaiement = "ACP" ;
            $nomPaiement = "ACOMPTE" ;
            $histoMessage = "Modification Ligne acompte N° : ".$finance->getNumFnc() ;
        }

        $filename = $this->filename."suiviCredit(agence)/".$this->nameAgence ;
         
        if(file_exists($filename))
            unlink($filename) ;

        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => $typePaiement,
            "nomModule" => $nomPaiement,
            "refAction" => "MOD",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => $histoMessage,
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

        return new JsonResponse([
            "type" => "green",
            "message" => "Modification effectué"
        ]) ;
    }

    #[Route('/credit/element/delete', name: 'credit_element_detail_delete')]
    public function crdDeleteElementCredit(Request $request)
    {
        $idCrdDetail = $request->request->get("idCrdDetail") ;

        $crdDetail = $this->entityManager->getRepository(CrdDetails::class)->find($idCrdDetail) ;

        $this->entityManager->remove($crdDetail) ;
        $this->entityManager->flush() ;

        $finance = $crdDetail->getFinance() ;

        $refPaiement = $finance->getPaiement()->getReference() ; 

        $this->appService->updateStatutFinance($finance) ;

        if($refPaiement == "CR")
        {
            $typePaiement = "CRD" ;
            $nomPaiement = "CREDIT" ;
            $histoMessage = "Suppression Ligne credit N° : ".$finance->getNumFnc() ;
        }
        else
        {
            $typePaiement = "ACP" ;
            $nomPaiement = "ACOMPTE" ;
            $histoMessage = "Suppression Ligne acompte N° : ".$finance->getNumFnc() ;
        }

        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => $typePaiement,
            "nomModule" => $nomPaiement,
            "refAction" => "DEL",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => $histoMessage,
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

        if($refPaiement == "AC")
            $filename = $this->filename."acompte(agence)/".$this->nameAgence ;
        else
            $filename = $this->filename."credit(agence)/".$this->nameAgence ;
            
        if(file_exists($filename))
            unlink($filename) ;

        $filename = $this->filename."suiviCredit(agence)/".$this->nameAgence ;
        
        if(file_exists($filename)) 
            unlink($filename) ;
 
        return new JsonResponse([
            "type" => "green",
            "message" => "Suppression effectué"
        ]) ;
    }

    #[Route('/credit/data/check/save', name: 'credit_data_check_save')]
    public function crdSaveDataCheckCredit(Request $request)
    {
        $dataCheck = $request->request->get("dataCheck") ;
        
        $filename = $this->filename."fileCheck(user)/liste_check_".$this->userObj->getId().".json" ; 

        file_put_contents($filename,json_encode($dataCheck)) ;

        return new JsonResponse([]) ;
    }
}
