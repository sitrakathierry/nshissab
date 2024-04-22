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
use App\Entity\BtpSurface;
use App\Entity\CaisseCommande;
use App\Entity\CaissePanier;
use App\Entity\Client;
use App\Entity\CltHistoClient;
use App\Entity\CltSociete;
use App\Entity\CltTypes;
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
use App\Entity\HistoHistorique;
use App\Entity\LctContrat;
use App\Entity\LctPaiement;
use App\Entity\LctRepartition;
use App\Entity\LctStatutLoyer;
use App\Entity\ModModelePdf;
use App\Entity\PrdEntrepot;
use App\Entity\PrdVariationPrix;
use App\Entity\SavAnnulation;
use App\Entity\SavAvoirUse;
use App\Entity\SavDetails;
use App\Entity\User;
use App\Service\AppService;
use App\Service\ExcelGenService;
use App\Service\PdfGenService;
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
        $this->nameUser = strtolower($this->session->get("user")["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"],
            "agence" => $this->agence  
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

    #[Route('/facture/ticket/caisse/get', name: 'fact_get_ticket_de_caisse')]
    public function factureGetTicketCaisse()
    {
        $filename = "files/systeme/caisse/commande(agence)/".$this->nameAgence ; 
        if(!file_exists($filename))
            $this->appService->generateCaisseCommande($filename, $this->agence) ;

        $commandes = json_decode(file_get_contents($filename)) ;

        $response = $this->renderView("facture/caisse/getContentCaisse.html.twig",[
            "commandes" => $commandes
        ]) ;

        return new Response($response) ;
    }

    #[Route('/facture/ticket/caisse/display', name: 'fact_ticket_caisse_display')]
    public function factTicketCaisseDisplay(Request $request)
    {
        $idCs = $request->request->get("idCs") ;

        if(empty($idCs))
            return new Response("") ;

        $caisseCommande = $this->entityManager->getRepository(CaisseCommande::class)->find($idCs) ;

        $remise = 0 ;

        if(!is_null($caisseCommande->getRemiseType()))
        {
            if($caisseCommande->getRemiseType()->getCalcul() == 1)
            {
                $remise = $caisseCommande->getRemiseValeur() ;
            }
            else
            {
                $remise = ($caisseCommande->getRemiseValeur() * $caisseCommande->getMontantPayee()) / 100 ;
            }
        }

        $totalTtc = $caisseCommande->getTva() + $caisseCommande->getMontantPayee() - $remise ;

        $caisse = [
            "numCommande" => $caisseCommande->getNumCommande() ,
            "totalHt" => $caisseCommande->getMontantPayee() ,
            "remise" => $remise,
            "totalTva" => $caisseCommande->getTva() ,
            "totalTtc" => $totalTtc ,
            "lettre" => $this->appService->NumberToLetter($totalTtc) ,
        ] ;

        $caissePaniers = $this->entityManager->getRepository(CaissePanier::class)->findBy([
            "commande" => $caisseCommande,
            "statut" => True
        ]) ;  

        $elements = [] ;

        foreach ($caissePaniers as $caissePanier) {
            $item = [] ;
            
            $filtreNomProduit = is_null($caissePanier->getVariationPrix()->getProduit()->getType()) ? "NA" : $caissePanier->getVariationPrix()->getProduit()->getType()->getNom() ;

            $item["id"] = $caissePanier->getId();
            $item["designation"] = $filtreNomProduit." | ".$caissePanier->getVariationPrix()->getProduit()->getNom() ;
            $item["quantite"] = $caissePanier->getQuantite() ;
            $item["prix"] = $caissePanier->getPrix() ;
            $item["tva"] = (($caissePanier->getPrix() * $caissePanier->getTva()) / 100 ) * $caissePanier->getQuantite() ;
            $item["total"] = $caissePanier->getQuantite() * $caissePanier->getPrix() ;
            $item["statut"] = $caissePanier->isStatut() ;

            array_push($elements,$item) ;
        }

        $caissePaniers = $elements ;

        $response = $this->renderView('facture/caisse/factureTicketCaisseDetails.html.twig',[
            "caisse" => $caisse,
            "caissePaniers" => $caissePaniers
        ]) ;

        return new Response($response) ;
    }

    #[Route('/facture/client/get', name: 'ftr_client_information_get')]
    public function factureGetInformationClient(Request $request)
    {
        $type = $request->request->get("type") ;

        if($type == "NEW")
        {
            $types = $this->entityManager->getRepository(CltTypes::class)->findAll() ;
            
            $response = $this->renderView("facture/client/getNewClient.html.twig",[
                "types" => $types,
            ]) ;
        }
        else
        {
            $filename = "files/systeme/client/client(agence)/".$this->nameAgence ;

            if(!file_exists($filename))
                $this->appService->generateCltClient($filename, $this->agence) ;

            $clients = json_decode(file_get_contents($filename)) ;

            $response = $this->renderView("facture/client/getExistingClient.html.twig",[
                "clients" => $clients,
            ]) ;
        }

        return new Response($response) ;
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

        $surfaces = $this->entityManager->getRepository(BtpSurface::class)->findBy([
            "agence" => $this->agence,
            "statut" => True,
        ]) ;

        $responses = $this->renderView("facture/prestBatiment.html.twig",[
            "elements" => $elements,
            "devises" => $devises,
            "agcDevise" => $agcDevise,
            "enoncees" => $enoncees,
            "surfaces" => $surfaces,
        ]) ;

        return new Response($responses) ;
    }

    #[Route('/facture/creation/prest/service', name: 'ftr_creation_prest_service')]
    public function factureCreationPrestService(): Response
    {
        $typeRemises = $this->entityManager->getRepository(FactRemiseType::class)->findAll() ; 

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
            "services" => $services,
            "typeRemises" => $typeRemises,
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
        
        $dataPrixs = [] ;

        foreach ($prixs as $prix) {
            $dataPrixs[] = [
                "id" => $prix->getId(),
                "montant" => $prix->getMontant(),
                "pays" => $prix->getPays(),
            ] ;
        }

        $mesure = is_null($element->getMesure()) ? "-" : $element->getMesure()->getNotation() ;

        $responses = $this->renderView('facture/factBtpPrixElement.html.twig', [
            "prixs" => $dataPrixs,
            "mesure" => $mesure
        ]) ;

        return new Response($responses) ;
    }

    #[Route('/facture/batiment/designation/get', name: 'ftr_batiment_designation_get')]
    public function ftrBatimentGetDesignation(Request $request)
    {
        $type_designation = $request->request->get("type_designation") ;
    
        if($type_designation == "EXIST")
        {
            $filename = "files/systeme/prestations/batiment/element(agence)/".$this->nameAgence ;
            if(!file_exists($filename))
                $this->appService->generatePrestBatiment($filename, $this->agence) ;
            
            $elements = json_decode(file_get_contents($filename)) ;

            $response = $this->renderView("facture/batiment/getExistDesignation.html.twig",[
                "elements" => $elements
            ]) ;
        }
        else if($type_designation == "NEW")
        {
            $response = $this->renderView("facture/batiment/getNewDesignation.html.twig") ;
        }

        return new Response($response) ;
    }

    public static function comparaisonDates($a, $b) {
        $dateA = \DateTime::createFromFormat('d/m/Y', $a['date']);
        $dateB = \DateTime::createFromFormat('d/m/Y', $b['date']);
        return $dateB <=> $dateA;
    }

    #[Route('/facture/consultation', name: 'ftr_consultation')]
    public function factureConsultation(): Response
    { 
        $this->appService->updateAnneeData() ;
        $this->appService->synchronisationFacture($this->agence) ;
        $this->appService->synchronisationServiceApresVente(["FACTURE"]) ;

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

        $filename = "files/systeme/stock/entrepot(agence)/".$this->nameAgence ;

        if(!file_exists($filename))  
            $this->appService->generateStockEntrepot($filename,$this->agence) ;
        
        $entrepots = json_decode(file_get_contents($filename)) ;

        return $this->render('facture/consultation.html.twig', [ 
            "filename" => "facture",
            "titlePage" => "Consultation Facture",
            "with_foot" => false,
            "factures" => $factures,
            "modeles" => $modeles,
            "types" => $types,
            "clients" => $clients,
            "critereDates" => $critereDates ,
            "entrepots" => $entrepots 
        ]); 
    }

    #[Route('/facture/basculer/definitive', name: 'fact_basculer_vers_definitive')]
    public function factureBasculerVersDefinitive(Request $request)
    {
        $idFacture = $request->request->get("idFacture") ;
        $fact_type_paiement = $request->request->get("fact_type_paiement") ;

        $paiement = $this->entityManager->getRepository(FactPaiement::class)->find($fact_type_paiement) ; 

        $facture = $this->entityManager->getRepository(Facture::class)->find($idFacture) ;

        $numFact = explode("-",$facture->getNumFact()) ;
        array_shift($numFact);
        $numFact = implode("-",$numFact) ;
        $newNumFact = "DF-".$numFact ;

        $type = $this->entityManager->getRepository(FactType::class)->findOneBy([
            "reference" => "DF"    
        ]) ; 
        
        // $dateTime = \DateTimeImmutable::createFromFormat('d/m/Y', $fact_date);
        
        $newFacture = new Facture() ;

        $newFacture->setFactureParent($facture) ;
        $newFacture->setAgence($this->agence) ;
        $newFacture->setUser($this->userObj) ;
        $newFacture->setClient($facture->getClient()) ;
        $newFacture->setType($type);
        $newFacture->setModele($facture->getModele()) ;
        $newFacture->setRemiseType($facture->getRemiseType()) ;
        $newFacture->setRemiseVal($facture->getRemiseVal()) ;
        $newFacture->setNumFact($newNumFact) ;
        $newFacture->setDescription($facture->getDescription()) ;
        $newFacture->setTvaVal($facture->getTvaVal()) ;
        $newFacture->setLieu($facture->getLieu()) ;
        $newFacture->setDate($facture->getDate()) ;
        $newFacture->setTotal($facture->getTotal()) ;
        $newFacture->setDevise($facture->getDevise()) ;
        $newFacture->setStatut(True) ;
        $newFacture->setCreatedAt(new \DateTimeImmutable) ;
        $newFacture->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($newFacture) ;
        $this->entityManager->flush() ;

        $histoOldPaiement = $this->entityManager->getRepository(FactHistoPaiement::class)->findOneBy([
            "facture" => $facture  
        ]) ; 

        $histoPaiement = new FactHistoPaiement() ;

        $histoPaiement->setLibelle($histoOldPaiement->getLibelle()) ;
        $histoPaiement->setNumero($histoOldPaiement->getNumero()) ;
        $histoPaiement->setPaiement($paiement) ;
        $histoPaiement->setFacture($newFacture) ;
        $histoPaiement->setStatutPaiement("Payee") ;
        
        $this->entityManager->persist($histoPaiement) ;
        $this->entityManager->flush() ;

        $detailFactures = $this->entityManager->getRepository(FactDetails::class)->findBy([
            "facture" => $facture   
        ]) ; 

        foreach ($detailFactures as $detailFacture) {

            $factDetail = new FactDetails() ;

            $factDetail->setActivite($detailFacture->getActivite()) ;
            $factDetail->setEntite($detailFacture->getEntite()) ;
            $factDetail->setFacture($newFacture) ; 
            $factDetail->setRemiseType($detailFacture->getRemiseType()) ;
            $factDetail->setRemiseVal($detailFacture->getRemiseVal()) ;
            $factDetail->setDesignation($detailFacture->getDesignation()) ;
            $factDetail->setQuantite($detailFacture->getQuantite()) ;
            $factDetail->setPrix($detailFacture->getPrix()) ;
            $factDetail->setTvaVal($detailFacture->getTvaVal()) ;
            $factDetail->setStatut(True) ;

            $this->entityManager->persist($factDetail) ;
            $this->entityManager->flush() ; 

            $modeleRef = $facture->getModele()->getReference() ;

            if($modeleRef == "PBAT")
            {
                $supplDetail = $this->entityManager->getRepository(FactSupDetailsPbat::class)->findOneBy([
                    "detail" => $detailFacture   
                ]) ; 

                $factSupDetailsPbat = new FactSupDetailsPbat() ;
    
                $factSupDetailsPbat->setEnonce($supplDetail->getEnonce()) ;
                $factSupDetailsPbat->setCategorie($supplDetail->getCategorie()) ;
                $factSupDetailsPbat->setDetail($supplDetail->getDetail()) ; 
                $factSupDetailsPbat->setInfoSup($supplDetail->getInfoSup()) ;
    
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
            }
        }
        
        $filename = $this->filename."facture(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename);

        $filename = "files/systeme/credit/credit(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename);

        $filename = "files/systeme/credit/acompte(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename);

        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "FACT",
            "nomModule" => "FACTURE",
            "refAction" => "CRT",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Créaction Facture Définitive N° ".$newNumFact." à partir d'une facture Proforma/Devis N° ".$facture->getNumFact()." ; ".strtoupper($facture->getModele()->getNom()),
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

        return new JsonResponse([
            "type" => "green",    
            "message" => "Création facture definitive effectué",    
            "idNewFacture" => $newFacture->getId(),    
        ]) ;
        
    }

    #[Route('/facture/type/paiement/get', name: 'fact_type_paiement_get')]
    public function factureGetTypeDePaiement()
    {
        $paiements = $this->entityManager->getRepository(FactPaiement::class)->findAll() ; 

        $response = $this->renderView("facture/templateTypePaiement.html.twig",[
            "paiements" => $paiements
        ]) ;

        return new Response($response) ;
    }

    #[Route('/facture/retenu/consultation', name: 'ftr_retenu_consultation')]
    public function factureRetenusConsultation() : Response
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

    #[Route('/facture/contenu/facture/modif', name: 'fact_content_facture_modif')]
    public function factureContenuModifFacture(Request $request)
    {
        $this->appService->synchronisationFacture($this->agence) ;

        $idFacture = $request->request->get("idFacture") ;
        $facture = $this->entityManager->getRepository(Facture::class)->find($idFacture) ;

        $infoFacture = [] ;

        $infoFacture["id"] = $facture->getId() ;
        $infoFacture["numFact"] = $facture->getNumFact() ;
        $infoFacture["modele"] = $facture->getModele()->getNom() ;
        $infoFacture["refModele"] = $facture->getModele()->getReference() ;
        $infoFacture["type"] = $facture->getType()->getNom() ;
        $infoFacture["date"] = $facture->getDate()->format("d/m/Y") ;
        $infoFacture["lieu"] = $facture->getLieu() ;
        $infoFacture["refType"] = $facture->getType()->getReference() ;
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
        
        $infoFacture["totalTva"] = ($facture->getTvaVal() == 0) ?  0 : $facture->getTvaVal();
        $infoFacture["totalTtc"] = $facture->getTotal() ;

        $factureDetails = $this->entityManager->getRepository(FactDetails::class)->findBy([
            "facture" => $facture,
            "statut" => True,
        ],["statut" => "DESC"]) ;
        
        $totalHt = 0 ;
        $totalTva = 0 ; 
        $elements = [] ;

        foreach ($factureDetails as $factureDetail) {
            $tvaVal = (empty($factureDetail->getTvaVal()) || is_null($factureDetail->getTvaVal())) ? 0 : $factureDetail->getTvaVal() ;
            $tva = (($factureDetail->getPrix() * $tvaVal ) / 100) * $factureDetail->getQuantite();
            $total = $factureDetail->getPrix() * $factureDetail->getQuantite()  ;

            $infoSupDetail = $this->entityManager->getRepository(FactSupDetailsPbat::class)->findOneBy([
                "detail" => $factureDetail
            ]) ;

            $remise = $this->appService->getFactureRemise($factureDetail,$total) ; 
            
            $total = $total - $remise ;
            
            $element = [] ;
            $element["id"] = $factureDetail->getId() ;
            $element["type"] = $factureDetail->getActivite() ;
            $element["designation"] = $factureDetail->getDesignation() ;
            $element["quantite"] = $factureDetail->getQuantite() ;
            $element["format"] = "-" ;
            $element["prix"] = $factureDetail->getPrix() ;
            $element["tva"] = $tva ;
            $element["percentTva"] = $factureDetail->getTvaVal() ;
            $element["typeRemise"] = is_null($factureDetail->getRemiseType()) ? "-" : $factureDetail->getRemiseType()->getNotation() ;
            $element["valRemise"] = $factureDetail->getRemiseVal() ;
            $element["statut"] = $factureDetail->isStatut();
            $element["total"] = $total ;
            $element["idEnonce"] = is_null($infoSupDetail) ? "" : $infoSupDetail->getEnonce()->getId() ;
            $element["enonce"] = is_null($infoSupDetail) ? "" : $infoSupDetail->getEnonce()->getNom() ;
            $element["idCategorie"] = is_null($infoSupDetail) ? "" : $infoSupDetail->getCategorie()->getId() ;
            $element["categorie"] = is_null($infoSupDetail) ? "" : $infoSupDetail->getCategorie()->getNom() ;
            $element["infoSup"] = is_null($infoSupDetail) ? "" : (is_null($infoSupDetail->getInfoSup()) ? "" : $infoSupDetail->getInfoSup()) ;
            
            array_push($elements,$element) ;

            $totalHt += $total ;
        } 

        $infoFacture["totalHt"] = $totalHt ;

        $remiseG = $this->appService->getFactureRemise($facture,$totalHt) ; 

        $infoFacture["remise"] = $remiseG ;
        $infoFacture["lettre"] = $this->appService->NumberToLetter($facture->getTotal()) ;
        
        $templateEditFacture = "" ;

        $devises = $this->entityManager->getRepository(Devise::class)->findBy([
            "agence" => $this->agence,
            "statut" => True
        ]) ; 

        $typeRemises = $this->entityManager->getRepository(FactRemiseType::class)->findAll() ; 
        $agcDevise = $this->appService->getAgenceDevise($this->agence) ;

        if(!is_null($facture->getModele()->getReference()))
        {
            if($facture->getModele()->getReference() == "PROD")
            {
        
                $filename = "files/systeme/stock/stock_general(agence)/".$this->nameAgence ;
                if(!file_exists($filename))
                    $this->appService->generateProduitStockGeneral($filename, $this->agence) ;
                
                $stockGenerales = json_decode(file_get_contents($filename)) ;

                $templateEditFacture = $this->renderView("sav/editFacture/templateProduit.html.twig",[
                    "stockGenerales" => $stockGenerales,
                    "typeRemises" => $typeRemises,
                    "facture" => $infoFacture,
                    "factureDetails" => $elements,
                    "devises" => $devises,
                    "agcDevise" => $agcDevise,
                ]) ;
            }
            else if($facture->getModele()->getReference() == "PSTD")
            {
                $filename = "files/systeme/prestations/service(agence)/".$this->nameAgence ;

                if(!file_exists($filename))
                    $this->appService->generatePrestationService($filename, $this->agence) ;

                $services = json_decode(file_get_contents($filename)) ;

                $templateEditFacture = $this->renderView("sav/editFacture/templatePrestationStandard.html.twig",[
                    "services" => $services,
                    "typeRemises" => $typeRemises,
                    "facture" => $infoFacture,
                    "factureDetails" => $elements,
                    "devises" => $devises,
                    "agcDevise" => $agcDevise,
                ]) ;
            }

            if($facture->getModele()->getReference() == "PBAT")
            {
                $filename = "files/systeme/prestations/batiment/enoncee(agence)/".$this->nameAgence ;
                if(!file_exists($filename))
                    $this->appService->generateEnonceePrestBatiment($filename, $this->agence) ;
                
                $enoncees = json_decode(file_get_contents($filename)) ;

                $filename = "files/systeme/prestations/batiment/element(agence)/".$this->nameAgence ;
                if(!file_exists($filename))
                    $this->appService->generatePrestBatiment($filename, $this->agence) ;
                
                $ensembleElements = json_decode(file_get_contents($filename)) ;

                $newTabFactureDetls = [] ;

                foreach($elements as $element)
                {
                    if(!$element["statut"])
                        continue;

                    $key1 = $element["idEnonce"]."#|#".$element["enonce"] ;
                    $key2 = $element["idCategorie"]."#|#".$element["categorie"] ;

                    $newTabFactureDetls[$key1][$key2][] = $element ;
                }

                $templateEditFacture = $this->renderView("sav/editFacture/templatePrestationBatiment.html.twig",[
                    "enoncees" => $enoncees,
                    "elements" => $ensembleElements,
                    "facture" => $infoFacture,
                    "devises" => $devises, 
                    "agcDevise" => $agcDevise,
                    "detailFactures" => $newTabFactureDetls
                ]) ;
            }
        }

        return new Response($templateEditFacture) ;
    }
 
    #[Route('/facture/element/modif/valid', name: 'fact_valid_modif_facture')]
    public function factureValidModifModifFacture(Request $request)
    {
        $idFacture = $request->request->get("idFacture") ;
        $sav_elem_quantite = $request->request->get("sav_elem_quantite") ;
        $sav_elem_tva = $request->request->get("sav_elem_tva") ;

        $factureDetail = $this->entityManager->getRepository(FactDetails::class)->find($idFacture) ;

        $factureDetail->setQuantite($sav_elem_quantite) ;
        $factureDetail->setTvaVal(empty($sav_elem_tva) ? 0 : $sav_elem_tva ) ;
        $factureDetail->getFacture()->setSynchro(null) ;
        $this->entityManager->flush() ;

        $tva = (($factureDetail->getPrix() * $factureDetail->getTvaVal()) / 100) * $factureDetail->getQuantite() ;
        $total = $factureDetail->getPrix()  * $factureDetail->getQuantite();

        return new JsonResponse([
            "type" => "green",
            "message" => "Modification effectué, cliquer sur enregistrer une fois toutes les modifications terminé",
            "valQte" => $sav_elem_quantite,
            "valTva" => $tva,
            "percentTva" => $sav_elem_tva,
            "valMtnTotal" => $total,
            "percentTva" => $sav_elem_tva,
            ]) ;

    }

    #[Route('/facture/imprimer/{idFacture}/{idModeleEntete}/{idModeleBas}', name: 'fact_facture_detail_imprimer', defaults: ["idModeleEntete" => null,"idFacture" => null, "description" => null, "idModeleBas" => null])]
    public function factureImprimerFacture($idModeleEntete,$idModeleBas,$idFacture)
    {
        // $idModeleEntete = $request->request->get("idModeleEntete") ;
        // $idModeleBas = $request->request->get("idModeleBas") ;
        // $idFacture = $request->request->get("idFacture") ;

        $facture = $this->entityManager->getRepository(Facture::class)->find($idFacture) ;
        
        $crdFinance = $this->entityManager->getRepository(CrdFinance::class)->findOneBy([
            "facture" => $facture
        ]) ;

        $addsNum = "" ;
        if(!is_null($crdFinance))
        {
            $paiement = $crdFinance->getPaiement() ;

            if(($paiement->getReference() == "AC" || $paiement->getReference() == "CR") && $crdFinance->getStatut()->getReference() == "ECR")
            {
                $addsNum = "- ".strtoupper($paiement->getNom()) ;
            }
        }
        
        $dataFacture = [
            "numFact" => $facture->getNumFact() ,
            "type" => $facture->getType()->getReference() == "DF" ? "" : $facture->getType()->getNom() ,
            "lettre" => $this->appService->NumberToLetter($facture->getTotal()) ,
            "deviseLettre" => is_null($this->agence->getDevise()) ? "" : $this->agence->getDevise()->getLettre(), 
            "description" => $facture->getDescription(),
            "addsNum" => $addsNum ,
            "titreFacture" => $facture->getType()->getReference() == "DF" ? "FACT" : "DEVIS" ,
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
            $element["id"] = $detail->getId() ;
            $element["type"] = $detail->getActivite() ;
            $element["designation"] = $detail->getDesignation() ;
            $element["isForfait"] = $detail->isIsForfait() ;
            $element["quantite"] = $detail->getQuantite() ;
            $element["format"] = "-" ;
            $element["prix"] = $detail->getPrix() ; 
            $element["tva"] = $tva ;
            $element["typeRemise"] = is_null($detail->getRemiseType()) ? "-" : $detail->getRemiseType()->getNotation() ;
            $element["valRemise"] = $detail->getRemiseVal() ;
            $element["statut"] = $detail->isStatut();
            $element["total"] = $total ;

            $infoSupDetail = $this->entityManager->getRepository(FactSupDetailsPbat::class)->findOneBy([
                "detail" => $detail
            ]) ;

            $element["idEnonce"] = is_null($infoSupDetail) ? "" : $infoSupDetail->getEnonce()->getId() ;
            $element["enonce"] = is_null($infoSupDetail) ? "" : $infoSupDetail->getEnonce()->getNom() ;
            $element["idCategorie"] = is_null($infoSupDetail) ? "" : $infoSupDetail->getCategorie()->getId() ;
            $element["categorie"] = is_null($infoSupDetail) ? "" : $infoSupDetail->getCategorie()->getNom() ;
            $element["infoSup"] = is_null($infoSupDetail) ? "" : (is_null($infoSupDetail->getInfoSup()) ? "" : $infoSupDetail->getInfoSup()) ;
            
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

        // $devises = $this->entityManager->getRepository(Devise::class)->findBy([
        //     "agence" => $this->agence,
        //     "statut" => True
        // ]) ; 

        $agcDevise = $this->appService->getAgenceDevise($this->agence) ;

        if($facture->getModele()->getReference() == "PBAT")
        {
            // $filename = "files/systeme/prestations/batiment/enoncee(agence)/".$this->nameAgence ;
            // if(!file_exists($filename))
            //     $this->appService->generateEnonceePrestBatiment($filename, $this->agence) ;
            
            // $enoncees = json_decode(file_get_contents($filename)) ;

            $newTabFactureDetls = [] ;

            foreach($dataDetails as $dataDetail)
            {
                if(!$dataDetail["statut"])
                    continue;

                $key1 = $dataDetail["idEnonce"]."#|#".$dataDetail["enonce"] ;
                $key2 = $dataDetail["idCategorie"]."#|#".$dataDetail["categorie"] ;
                
                $detailBatiment = $this->entityManager->getRepository(BtpElement::class)->getInformationElement([
                    "idFactDetail" => $dataDetail["id"]
                ]) ;

                $dataDetail["designation"] = $detailBatiment["designation"] ;
                $dataDetail["mesure"] = $detailBatiment["mesure"] ;

                $newTabFactureDetls[$key1][$key2][] = $dataDetail ;
            }

            $contentIMpression = $this->renderView('facture/impression/impressionFactureBatiment.html.twig', [
                "agcDevise" => $agcDevise,
                "detailFactures" => $newTabFactureDetls,
                "contentEntete" => $contentEntete,
                "contentBas" => $contentBas,
                "facture" => $dataFacture,
                "client" => $dataClient,
            ]) ;
        }
        else if ($facture->getModele()->getReference() == "PROD" || $facture->getModele()->getReference() == "PSTD")
        {
            $contentIMpression = $this->renderView("facture/impression/impressionFacture.html.twig",[
                "contentEntete" => $contentEntete,
                "contentBas" => $contentBas,
                "facture" => $dataFacture,
                "client" => $dataClient,
                "details" => $dataDetails,
            ]) ;
        }

        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "FACT",
            "nomModule" => "FACTURE",
            "refAction" => "IMP",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Impression Facture ; ".strtoupper($facture->getModele()->getNom())." ; N° : ".$facture->getNumFact(),
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE


        $pdfGenService = new PdfGenService() ;

        $pdfGenService->generatePdf($contentIMpression,$this->nameUser) ;

        // Redirigez vers une autre page pour afficher le PDF
        return $this->redirectToRoute('display_pdf');
    }

    #[Route('/facture/avoir/imprimer/{idAvoir}/{idModeleEntete}/{idModeleBas}', name: 'fact_facture_avoir_imprimer', defaults: ["idModeleEntete" => null,"idAvoir" => null, "idModeleBas" => null])]
    public function factureImprimerFactureAvoir($idModeleEntete,$idModeleBas,$idAvoir)
    {
        $avoirUse = $this->entityManager->getRepository(SavAvoirUse::class)->find($idAvoir) ;

        $facture = $avoirUse->getFacture() ;
        
        $crdFinance = $this->entityManager->getRepository(CrdFinance::class)->findOneBy([
            "facture" => $facture
        ]) ;

        $addsNum = "" ;

        if(!is_null($crdFinance))
        {
            $paiement = $crdFinance->getPaiement() ;

            if(($paiement->getReference() == "AC" || $paiement->getReference() == "CR") && $crdFinance->getStatut()->getReference() == "ECR")
            {
                $addsNum = "- ".strtoupper($paiement->getNom()) ;
            }
        }
        
        $dataFacture = [
            "numFact" => $facture->getNumFact() ,
            "type" => $facture->getType()->getReference() == "DF" ? "" : $facture->getType()->getNom() ,
            "lettre" => $this->appService->NumberToLetter($avoirUse->getMontant()) ,
            "deviseLettre" => is_null($this->agence->getDevise()) ? "" : $this->agence->getDevise()->getLettre(), 
            "description" => $facture->getDescription(),
            "addsNum" => $addsNum ,
            "titreFacture" => "Fiche d'avoir" ,
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

        $dataDetails = [
            "designation" => "Avoir utilisé sur facture N° : ".$facture->getNumFact()."".$addsNum,
            "montant" => $avoirUse->getMontant(),
        ] ;
        
        $dataFacture["date"] = $facture->getDate()->format("d/m/Y") ;
        $dataFacture["lieu"] = $facture->getLieu() ;

        if(!is_null($facture->getDevise()))
        {
            $dataFacture["deviseCaption"] = $facture->getDevise()->getLettre() ;
            $dataFacture["deviseValue"] = number_format($facture->getTotal()/$facture->getDevise()->getMontantBase(),2,","," ")." ".$facture->getDevise()->getSymbole();
        }

        $agcDevise = $this->appService->getAgenceDevise($this->agence) ;

        $contentIMpression = $this->renderView("facture/impression/impressionFicheAvoir.html.twig",[
            "contentEntete" => $contentEntete,
            "contentBas" => $contentBas,
            "facture" => $dataFacture,
            "client" => $dataClient,
            "avoirUse" => $dataDetails,
        ]) ;
       
        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "FACT",
            "nomModule" => "FACTURE",
            "refAction" => "IMP",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Impression Fiche D'avoir Facture N° ".$facture->getNumFact(),
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE


        $pdfGenService = new PdfGenService() ;

        $pdfGenService->generatePdf($contentIMpression,$this->nameUser) ;

        // Redirigez vers une autre page pour afficher le PDF
        return $this->redirectToRoute('display_pdf');
    }

    #[Route('/facture/element/description/upadte', name: 'fact_element_description_update')]
    public function factSUpdateDescriptionElement(Request $request)
    {
        $idFacture = $request->request->get("idFacture") ;
        $facture_editor = $request->request->get("facture_editor") ;
        $cmd_lieu = $request->request->get("cmd_lieu") ;
        $cmd_date = $request->request->get("cmd_date") ;

        $facture = $this->entityManager->getRepository(Facture::class)->find($idFacture) ;

        $facture->setDescription($facture_editor) ;
        
        if(isset($cmd_lieu) && !empty($cmd_lieu))
            $facture->setLieu($cmd_lieu) ;

        if(isset($cmd_date) && !empty($cmd_date))
            $facture->setDate(\DateTime::createFromFormat("d/m/Y",$cmd_date)) ;

        $this->entityManager->flush() ;

        return new JsonResponse([""]) ;
    }

    public static function comparaisonFactureDetail($a, $b) {
        // Comparaison par entrepot
        $result = strcmp($a['idEnonce'], $b['idEnonce']);
        
        if ($result !== 0) {
            return $result;
        }
        
        // // Comparaison par categorie
        // $result = strcmp($a['categorie'], $b['categorie']);
        
        // if ($result !== 0) {
        //     return $result;
        // }
        
        // Comparaison par nomType
        return strcmp($a['idCategorie'], $b['idCategorie']);
    }
 
    #[Route('/facture/activite/details/{id}/{nature}', name: 'ftr_details_activite' , defaults : ["id" => null,"nature" => "FACTURE"])]
    public function factureDetailsActivites($id,$nature)
    {
        $this->appService->synchronisationFacture($this->agence) ;

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

        $infoFacture["id"] = $facture->getId() ;
        $infoFacture["numFact"] = $facture->getNumFact() ;
        $infoFacture["modele"] = $facture->getModele()->getNom() ;
        $infoFacture["refModele"] = $facture->getModele()->getReference() ;
        $infoFacture["type"] = $facture->getType()->getNom() ;
        $infoFacture["date"] = $facture->getDate()->format("d/m/Y") ;
        $infoFacture["lieu"] = $facture->getLieu() ;
        $infoFacture["refType"] = $facture->getType()->getReference() ;
        $infoFacture["ticketCaisse"] = is_null($facture->getTicketCaisse()) ? false : $facture->getTicketCaisse()->getNumCommande() ;
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
        
        $infoFacture["totalTva"] = ($facture->getTvaVal() == 0) ?  0 : $facture->getTvaVal();
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

            $infoSupDetail = $this->entityManager->getRepository(FactSupDetailsPbat::class)->findOneBy([
                "detail" => $factureDetail
            ]) ;

            $remise = $this->appService->getFactureRemise($factureDetail,$total) ; 
            
            $total = $total - $remise ;
            
            $element = [] ;
            $element["id"] = $factureDetail->getId() ;
            $element["type"] = $factureDetail->getActivite() ;
            $element["designation"] = $factureDetail->getDesignation() ;
            $element["isForfait"] = $factureDetail->isIsForfait() ;
            $element["quantite"] = $factureDetail->getQuantite() ;
            $element["format"] = "-" ;
            $element["prix"] = $factureDetail->getPrix() ;
            $element["tva"] = ($tva == 0) ? 0 : $tva ;
            $element["typeRemise"] = is_null($factureDetail->getRemiseType()) ? "-" : $factureDetail->getRemiseType()->getNotation() ;
            $element["valRemise"] = $factureDetail->getRemiseVal() ;
            $element["statut"] = $factureDetail->isStatut();
            $element["total"] = $total ;
            $element["idEnonce"] = is_null($infoSupDetail) ? "" : $infoSupDetail->getEnonce()->getId() ;
            $element["enonce"] = is_null($infoSupDetail) ? "" : $infoSupDetail->getEnonce()->getNom() ;
            $element["idCategorie"] = is_null($infoSupDetail) ? "" : $infoSupDetail->getCategorie()->getId() ;
            $element["categorie"] = is_null($infoSupDetail) ? "" : $infoSupDetail->getCategorie()->getNom() ;
            $element["idSurface"] = is_null($infoSupDetail) ? "-" : (is_null($infoSupDetail->getSurface()) ? "-" : $infoSupDetail->getSurface()->getId()) ;
            $element["surface"] = is_null($infoSupDetail) ? "" : (is_null($infoSupDetail->getSurface()) ? "-" : $infoSupDetail->getSurface()->getNom()) ;
            $element["infoSup"] = is_null($infoSupDetail) ? "" : (is_null($infoSupDetail->getInfoSup()) ? "" : $infoSupDetail->getInfoSup()) ;
            
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

        $templateEditFacture = "" ;

        $devises = $this->entityManager->getRepository(Devise::class)->findBy([
            "agence" => $this->agence,
            "statut" => True
        ]) ; 

        $typeRemises = $this->entityManager->getRepository(FactRemiseType::class)->findAll() ; 
        $agcDevise = $this->appService->getAgenceDevise($this->agence) ;

        $factureParent = "" ;
        $factureGenere = "" ;
        $factureCreer = true ;
        if($nature == "FACTURE" )
        {
            $factureCreer = true ;
            if($facture->getType()->getReference() == "DF")
            {
                if(!is_null($facture->getFactureParent()))
                {
                    $factureCreer = false ;
                    $factureParent = $facture->getFactureParent()->getId() ;
                    $factureGenere = $facture->getId() ;
                }
            }
            else if($facture->getType()->getReference() == "PR")
            {
                $isFactureParent = $this->entityManager->getRepository(Facture::class)->findOneBy([
                    "factureParent" => $facture
                ]) ;

                if(!is_null($isFactureParent))
                {
                    $factureCreer = false ;
                    $factureParent = $facture->getId() ;
                    $factureGenere = $isFactureParent->getId() ;
                }
            }
        }

        $paiements = $this->entityManager->getRepository(FactPaiement::class)->findBy([],["rang" => "ASC"]) ; 

        $deviseBase = $this->agence->getDevise() ;

        $baseSymbole = is_null($deviseBase) ? "" : $deviseBase->getSymbole() ;

        if(!is_null($facture->getModele()->getReference()))
        {
            // DEBUT DETAIL AVOIR 
            $dataAvoir = [
                "isTrue" => False ,
            ] ;
    
            $avoirInUse = $this->entityManager->getRepository(SavAvoirUse::class)->findOneBy([
                "facture" => $facture
            ]) ;
    
            if(!is_null($avoirInUse))
            {
                $dataAvoir = [ 
                    "isTrue" => True,
                    "idAvoir" => $avoirInUse->getId(),
                    "montant" => $avoirInUse->getMontant()." ".$baseSymbole,
                    "totalPayee" => ($facture->getTotal() - $avoirInUse->getMontant())." ".$baseSymbole,
                ] ;
            }
            // FIN DETAIL AVOIR 

            if($facture->getModele()->getReference() == "PROD")
            {
                $filename = "files/systeme/stock/stock_general(agence)/".$this->nameAgence ;
                if(!file_exists($filename))
                    $this->appService->generateProduitStockGeneral($filename, $this->agence) ;
                
                $stockGenerales = json_decode(file_get_contents($filename)) ;

                $templateEditFacture = $this->renderView("facture/editFacture/templateProduit.html.twig",[
                    "stockGenerales" => $stockGenerales,
                    "typeRemises" => $typeRemises,
                ]) ;
            }
            else if($facture->getModele()->getReference() == "PSTD")
            {
                $filename = "files/systeme/prestations/service(agence)/".$this->nameAgence ;

                if(!file_exists($filename))
                    $this->appService->generatePrestationService($filename, $this->agence) ;

                $services = json_decode(file_get_contents($filename)) ;

                $templateEditFacture = $this->renderView("facture/editFacture/templatePrestationStandard.html.twig",[
                    "services" => $services,
                    "typeRemises" => $typeRemises,
                ]) ;
            }

            if($facture->getModele()->getReference() == "PBAT") 
            {

                $filename = "files/systeme/prestations/batiment/enoncee(agence)/".$this->nameAgence ;
                if(!file_exists($filename))
                    $this->appService->generateEnonceePrestBatiment($filename, $this->agence) ;
                
                $enoncees = json_decode(file_get_contents($filename)) ;

                $filename = "files/systeme/prestations/batiment/element(agence)/".$this->nameAgence ;
                if(!file_exists($filename))
                    $this->appService->generatePrestBatiment($filename, $this->agence) ;
                
                $ensembleElements = json_decode(file_get_contents($filename)) ;

                $newTabFactureDetls = [] ;

                foreach($elements as $element)
                {
                    if(!$element["statut"])
                        continue;

                    $key1 = $element["idEnonce"]."#|#".$element["enonce"] ;
                    $key2 = $element["idCategorie"]."#|#".$element["categorie"] ;
                    $key3 = $element["idSurface"]."#|#".$element["surface"] ;

                    $detailBatiment = $this->entityManager->getRepository(BtpElement::class)->getInformationElement([
                        "idFactDetail" => $element["id"]
                    ]) ;
    
                    $element["designation"] = $detailBatiment["designation"] ;
                    $element["mesure"] = $detailBatiment["mesure"] ;

                    $newTabFactureDetls[$key1][$key2][$key3][] = $element ;
                }

                $surfaces = $this->entityManager->getRepository(BtpSurface::class)->findBy([
                    "agence" => $this->agence,
                    "statut" => True,
                ]) ;
                
                return $this->render('facture/batiment/detailsFactureBatiment.html.twig', [
                    "filename" => "facture",
                    "titlePage" => "Détails Facture Batiment",
                    "with_foot" => true,
                    "facture" => $infoFacture,
                    "elements" => $ensembleElements,
                    "nature" => $nature,
                    "typeRemises" => $typeRemises,
                    "devises" => $devises,
                    "agcDevise" => $agcDevise,
                    "enoncees" => $enoncees,
                    "detailFactures" => $newTabFactureDetls,
                    "factureParent" => $factureParent,
                    "factureGenere" => $factureGenere,
                    "factureCreer" => $factureCreer,
                    "paiements" => $paiements,
                    "dataAvoir" => $dataAvoir,
                    "surfaces" => $surfaces,
                ]) ;
            }
        }

        return $this->render('facture/detailsFacture.html.twig', [
            "filename" => "facture",
            "titlePage" => "Détails Facture",
            "templateEditFacture" => $templateEditFacture,
            "factureParent" => $factureParent,
            "factureGenere" => $factureGenere,
            "factureCreer" => $factureCreer,
            "typeRemises" => $typeRemises,
            "factureDetails" => $elements,
            "paiements" => $paiements,
            "agcDevise" => $agcDevise,
            "facture" => $infoFacture,
            "dataAvoir" => $dataAvoir,
            "devises" => $devises,
            "with_foot" => true,
            "nature" => $nature,
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
        $fact_ticket_caisse = $request->request->get("fact_ticket_caisse") ;
        
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

        $result = $this->appService->verificationElement($data, $dataMessage) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $type = $this->entityManager->getRepository(FactType::class)->find($fact_type) ;

        if($type->getReference() == "DF")
        {
            array_push($data,$fact_paiement) ;
            array_push($dataMessage,"Paiement") ;
        }
        
        $paiement = $this->entityManager->getRepository(FactPaiement::class)->find($fact_paiement) ; 

        $result = $this->appService->verificationElement($data, $dataMessage) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $modele = $this->entityManager->getRepository(FactModele::class)->find($fact_modele) ; 

        if($modele->getReference() == "PROD" || $modele->getReference() == "PSTD")
        {
            $fact_enr_prod_type = (array)$request->request->get('fact_enr_prod_type') ;
            if(isset($fact_ticket_caisse) && !empty($fact_ticket_caisse))
                $fact_enr_prod_type = ["donnée statique pour éliminer une condition si c'est bon de caisse"] ;

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

        $fact_nouveau_client = $request->request->get('fact_nouveau_client') ; 

        if($fact_nouveau_client == "OUI")
        {
            $clt_type = $request->request->get("clt_type") ;
            $fact_clt_adresse = $request->request->get("fact_clt_adresse") ;
            $fact_clt_telephone = $request->request->get("fact_clt_telephone") ;

            $result = $this->appService->verificationElement([
                $clt_type
            ], [
                "Statut"
                ]) ;

            if(!$result["allow"])
                return new JsonResponse($result) ;

            $typeClient = $this->entityManager->getRepository(CltTypes::class)->find($clt_type) ;

            if($typeClient->getReference() == "MORAL")
            {
                $societe = new CltSociete() ;

                $societe->setAgence($this->agence) ;
                $societe->setNom($fact_client) ;
                $societe->setAdresse($fact_clt_adresse) ;
                $societe->setTelFixe($fact_clt_telephone) ;

                $this->entityManager->persist($societe) ;
                $this->entityManager->flush() ;
                $clientP = null ;
            }
            else
            {
                $clientP = new Client() ;

                $clientP->setAgence($this->agence) ;
                $clientP->setNom($fact_client) ;
                $clientP->setAdresse($fact_clt_adresse) ;
                $clientP->setTelephone($fact_clt_telephone) ;
                
                $this->entityManager->persist($clientP) ;
                $this->entityManager->flush() ;
                $societe = null ;
            }

            $client = new CltHistoClient() ;

            $client->setAgence($this->agence) ;
            $client->setClient($clientP) ;
            $client->setSociete($societe) ;
            $client->setType($typeClient) ;
            $client->setUrgence(null) ;
            $client->setStatut(True) ;
            $client->setCreatedAt(new \DateTimeImmutable) ;
            $client->setUpdatedAt(new \DateTimeImmutable) ;

            $this->entityManager->persist($client) ;
            $this->entityManager->flush() ;

            $filename = "files/systeme/client/client(agence)/".$this->nameAgence ;

            if(file_exists($filename))
                unlink($filename) ;
 
        }
        else
        {
            $client = $this->entityManager->getRepository(CltHistoClient::class)->find($fact_client) ; 
        }
 
        $recordFacture = $this->entityManager->getRepository(Facture::class)->findBy([ 
            "agence" => $this->agence,
            "anneeData" => date('Y')
        ], ['id' => 'DESC']);
        $numFacture = !is_null($recordFacture) ? (count($recordFacture) + 1) : 1 ;
        $numFacture = str_pad($numFacture, 3, "0", STR_PAD_LEFT);
        $numFacture = $type->getReference()."-".$numFacture."/".date('y') ; 
        
        $fact_enr_val_devise = $request->request->get('fact_enr_val_devise') ; 
        $fact_enr_val_devise = empty($fact_enr_val_devise) ? null : $this->entityManager->getRepository(Devise::class)->find($fact_enr_val_devise) ;

        $facture = new Facture() ;
        
        
        if(isset($fact_ticket_caisse) && !empty($fact_ticket_caisse))
        {
            $caisseCommande = $this->entityManager->getRepository(CaisseCommande::class)->find($fact_ticket_caisse) ;
            $fact_type_remise_prod_general = $caisseCommande->getRemiseType() ; 
            if(!is_null($caisseCommande->getRemiseType()))
            {
                $factRemiseTypeCaisse = $this->entityManager->getRepository(FactRemiseType::class)->findOneBy([
                    "calcul" => $caisseCommande->getRemiseType()->getCalcul() 
                ]) ;

                $fact_type_remise_prod_general = $factRemiseTypeCaisse ;
            }

            $fact_remise_prod_general = $caisseCommande->getRemiseValeur() ; 
            $fact_enr_total_tva = $caisseCommande->getTva() ; 
            $remiseCaisse = $this->appService->getCaisseRemise($caisseCommande,$caisseCommande->getMontantPayee()) ;
            $fact_enr_total_general = $caisseCommande->getTva() + $caisseCommande->getMontantPayee() - $remiseCaisse ;

            $facture->setTicketCaisse($caisseCommande) ;
        }

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
        $facture->setIsUpdated(True) ;
        $facture->setAnneeData($dateTime->format('Y')) ;
        $facture->setCreatedAt(new \DateTimeImmutable) ;
        $facture->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($facture) ;
        $this->entityManager->flush() ;

        // DEBUT ENREGISTREMENT AVOIR DU CLIENT

        $fact_avoir_use = $request->request->get("fact_avoir_use") ;

        if(isset($fact_avoir_use) && !empty($fact_avoir_use))
        {
            if($fact_avoir_use > 0 && !empty($fact_avoir_use))
            {
                $savAvoirUse = new SavAvoirUse() ;
    
                $savAvoirUse->setFacture($facture) ;
                $savAvoirUse->setHistoClient($client) ;
                $savAvoirUse->setMontant($fact_avoir_use) ;
                $savAvoirUse->setIsNew(True) ;
                $savAvoirUse->setCreatedAt(new \DateTimeImmutable) ;
                $savAvoirUse->setUpdatedAt(new \DateTimeImmutable) ;
    
                $this->entityManager->persist($savAvoirUse) ;
                $this->entityManager->flush() ;

                $filename = "files/systeme/sav/avoirs(agence)/".$this->nameAgence ;

                if(file_exists($filename))
                    unlink($filename) ;
            }
        }

        // FIN ENREGISTREMENT AVOIR DU CLIENT

        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "FACT",
            "nomModule" => "FACTURE",
            "refAction" => "CRT",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Création Facture N° : ".$numFacture,
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

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
            if(isset($fact_ticket_caisse) && !empty($fact_ticket_caisse))
            {
                $caissePaniers = $this->entityManager->getRepository(CaissePanier::class)->findBy([
                    "commande" => $caisseCommande,
                    "statut" => True
                ]) ;  
        
                foreach ($caissePaniers as $caissePanier) {

                    $factDetail = new FactDetails() ;
        
                    $factDetail->setFacture($facture) ; 
                    $factDetail->setRemiseType(null) ;
                    $factDetail->setRemiseVal(null) ;
                    $factDetail->setActivite("Produit") ;
                    $factDetail->setEntite($caissePanier->getVariationPrix()->getId()) ;
                    $factDetail->setDesignation($caissePanier->getVariationPrix()->getProduit()->getNom()) ;
                    $factDetail->setQuantite($caissePanier->getQuantite()) ;
                    $factDetail->setPrix($caissePanier->getPrix()) ;
                    $factDetail->setTvaVal($caissePanier->getTva()) ;
                    $factDetail->setStatut(True) ;
    
                    $this->entityManager->persist($factDetail) ;
                    $this->entityManager->flush() ; 

                    $caissePanier->getVariationPrix()->getProduit()->setToUpdate(True) ;
                    $this->entityManager->flush() ;
                }
            }
            else
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
                        if($fact_enr_prod_type[$key] == "Produit")
                        {
                            $detailEntite = $this->entityManager->getRepository(PrdVariationPrix::class)->find($fact_enr_prod_prix[$key]) ;
                            $detailEntite->getProduit()->setToUpdate(True) ;
                            $this->entityManager->flush() ;
                        }
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
            $fact_enr_btp_forfait = $request->request->get('fact_enr_btp_forfait') ;
            $fact_enr_btp_surface_id = $request->request->get('fact_enr_btp_surface_id') ;
            
            foreach ($fact_enr_btp_enonce_id as $key => $value) {
                $dtlsTvaVal = empty($fact_enr_btp_tva[$key]) ? null : $fact_enr_btp_tva[$key] ;
                $forfait = $fact_enr_btp_forfait[$key] == "OUI" ? True : NULL ;

                $factDetail = new FactDetails() ;

                $factDetail->setActivite("BtpElement") ;
                $factDetail->setEntite($fact_enr_btp_element_id[$key]) ;
                $factDetail->setFacture($facture) ; 
                $factDetail->setIsForfait($forfait) ; 
                $factDetail->setDesignation($fact_enr_btp_designation[$key]) ;
                $factDetail->setQuantite($fact_enr_btp_quantite[$key]) ;
                $factDetail->setPrix($fact_enr_btp_prix[$key]) ;
                $factDetail->setTvaVal($dtlsTvaVal) ;
                $factDetail->setStatut(True) ;

                $this->entityManager->persist($factDetail) ;
                $this->entityManager->flush() ; 

                $enoncee = $this->entityManager->getRepository(BtpEnoncee::class)->find($fact_enr_btp_enonce_id[$key]) ;
                $categorie = $this->entityManager->getRepository(BtpCategorie::class)->find($fact_enr_btp_categorie_id[$key]) ;
                $surface = $this->entityManager->getRepository(BtpSurface::class)->find($fact_enr_btp_surface_id[$key]) ;

                $factSupDetailsPbat = new FactSupDetailsPbat() ;
                
                $factSupDetailsPbat->setEnonce($enoncee) ;
                $factSupDetailsPbat->setCategorie($categorie) ;
                $factSupDetailsPbat->setSurface($surface) ;
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

        // if(!file_exists($filename))
        //     $this->appService->generateFacture($filename, $this->agence) ;
        
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

    #[Route('/facture/activite/rajoute/element', name: 'fact_rajoute_element_activites')]
    public function factRajouteElementActivities(Request $request)
    {
        $fact_id_facture = $request->request->get("fact_id_facture") ;
        $fact_detail_modele = $request->request->get("fact_detail_modele") ;
        $facture_editor = $request->request->get("facture_editor") ;
        $cmd_lieu = $request->request->get("cmd_lieu") ;
        $cmd_date = $request->request->get("cmd_date") ;
        
        $fact_type_remise_prod_general = !empty($request->request->get('fact_type_remise_prod_general')) ? $this->entityManager->getRepository(FactRemiseType::class)->find($request->request->get('fact_type_remise_prod_general')) : null ; 
        if(!is_null($fact_type_remise_prod_general))
            $fact_remise_prod_general = !empty($request->request->get('fact_remise_prod_general')) ? $request->request->get('fact_remise_prod_general') : null ; 
        else
            $fact_remise_prod_general = null ;

        $facture = $this->entityManager->getRepository(Facture::class)->find($fact_id_facture) ;
        
        $facture->setRemiseType($fact_type_remise_prod_general) ;
        $facture->setRemiseVal($fact_remise_prod_general) ;
        $facture->setDescription($facture_editor) ; 
        $facture->setLieu($cmd_lieu) ; 
        $facture->setIsUpdated(True) ; 
        $facture->setDate(\DateTime::createFromFormat("d/m/Y",$cmd_date)) ; 

        $this->entityManager->flush() ; 

        if($fact_detail_modele == "PROD" || $fact_detail_modele == "PSTD") // Produit ou Prestation Standard
        {
            $fact_enr_prod_type = (array)$request->request->get('fact_enr_prod_type') ;
            $fact_enr_prod_designation = $request->request->get('fact_enr_prod_designation') ;
            $fact_enr_prod_quantite = $request->request->get('fact_enr_prod_quantite') ;
            $fact_enr_prod_prix = $request->request->get('fact_enr_prod_prix') ;
            $fact_enr_text_prix = $request->request->get('fact_enr_text_prix') ;
            $fact_enr_prod_remise_type = $request->request->get('fact_enr_prod_remise_type') ;
            $fact_enr_prod_remise = $request->request->get('fact_enr_prod_remise') ;
            $fact_enr_prod_tva_val = $request->request->get('fact_enr_prod_tva_val') ;

            foreach ($fact_enr_prod_type as $key => $value) {

                if($value == "-")
                    continue ;

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
        else if($fact_detail_modele == "PBAT" )
        {
            $fact_enr_btp_enonce_id = (array)$request->request->get('fact_enr_btp_enonce_id') ;
            $fact_enr_btp_categorie_id = $request->request->get('fact_enr_btp_categorie_id') ;
            $fact_enr_btp_element_id = $request->request->get('fact_enr_btp_element_id') ;
            $fact_enr_btp_designation = $request->request->get('fact_enr_btp_designation') ;
            $fact_enr_btp_prix = $request->request->get('fact_enr_btp_prix') ;
            $fact_enr_btp_quantite = $request->request->get('fact_enr_btp_quantite') ;
            $fact_enr_btp_tva = $request->request->get('fact_enr_btp_tva') ;
            $fact_enr_btp_info_sup = $request->request->get('fact_enr_btp_info_sup') ;
            $fact_enr_btp_surface_id = $request->request->get('fact_enr_btp_surface_id') ;
            
            foreach ($fact_enr_btp_enonce_id as $key => $value) {

                if($fact_enr_btp_enonce_id[$key] == "-")
                    continue ;

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
                $surface = $this->entityManager->getRepository(BtpSurface::class)->find($fact_enr_btp_surface_id[$key]) ;

                $factSupDetailsPbat = new FactSupDetailsPbat() ;

                $factSupDetailsPbat->setEnonce($enoncee) ;
                $factSupDetailsPbat->setCategorie($categorie) ;
                $factSupDetailsPbat->setSurface($surface) ;
                $factSupDetailsPbat->setDetail($factDetail) ; 
                $factSupDetailsPbat->setInfoSup($fact_enr_btp_info_sup[$key]) ;

                $this->entityManager->persist($factSupDetailsPbat) ;
                $this->entityManager->flush() ; 
            }
        }

        $facture->setSynchro(null) ;
        $this->entityManager->flush() ; 

        $filename = $this->filename."facture(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename);
        
        $filename = "files/systeme/credit/acompte(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename);

        $filename = "files/systeme/credit/credit(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename);

        $modeleFacture = $this->entityManager->getRepository(FactModele::class)->findOneBy([
            "reference" => $fact_detail_modele
        ]) ; 

        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "FACT",
            "nomModule" => "FACTURE",
            "refAction" => "MOD",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Modification Facture ; ".strtoupper($modeleFacture->getNom())." ; N° : ".$facture->getNumFact(),
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

        return new JsonResponse([
            "type" => "green",
            "message" => "Mise à jour effectué",
        ]) ;
    }

    #[Route('/facture/activite/supprime', name: 'fact_activites_supprime')]
    public function factSupprimeActivities(Request $request)
    {
        $idFacture = $request->request->get("idFacture") ;

        $factureDetail = $this->entityManager->getRepository(FactDetails::class)->find($idFacture) ;
        
        $factureDetail->setStatut(False) ;
        $factureDetail->getFacture()->setSynchro(null) ;
        $this->entityManager->flush() ; 

        $filename = $this->filename."facture(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename);
        
        return new JsonResponse([
            "type" => "green",
            "message" => "Suppression élément effectué",
        ]) ;
    }

    #[Route('/facture/activite/principal/supprime', name: 'stock_delete_facture_activity')]
    public function factSupprimePricipalActivitie(Request $request)
    {
        $idFacture = $request->request->get("idFacture") ;

        $facture = $this->entityManager->getRepository(Facture::class)->find($idFacture) ;
        
        $facture->setStatut(False) ;
        $facture->setSynchro(null) ;
        $this->entityManager->flush() ; 

        $filename = $this->filename."facture(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename);
        
        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "FACT",
            "nomModule" => "FACTURE",
            "refAction" => "DEL",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Suppression Facture ; ".strtoupper($facture->getModele()->getNom())." ; N° : ".$facture->getNumFact(),
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

        return new JsonResponse([
            "type" => "green",
            "message" => "Suppression effectué",
        ]) ;
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
        $idEntrepot = $request->request->get('idEntrepot') ;

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
            "idEntrepot" => $idEntrepot,
        ] ;

        foreach ($search as $key => $value) {
            if($value == "undefined")
            {
                $search[$key] = "" ;
            }
        } 

        $factures = $this->appService->searchData($factures,$search) ;

        if(!empty($idEntrepot))
        {
            foreach ($factures as $key => $value) {
                if($value->idEntrepot != "-")
                {
                    if(!empty($value->idEntrepot))
                    {
                        $entrepot = $this->entityManager->getRepository(PrdEntrepot::class)->find($idEntrepot) ;
                        $factures[$key]->entrepot = $entrepot->getNom() ;
                    }
                }
            }
        }

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
        
        if($contrat->getPeriode()->getReference() == "J")
            $periode = "Jour(s)" ;
        else if($contrat->getPeriode()->getReference() == "M")
            $periode = "Mois" ;
        else if($contrat->getPeriode()->getReference() == "A")
            $periode = "An(s)" ;

        $item = [
            "id" => $contrat->getId(),
            "agence" => $contrat->getAgence()->getId(),
            "numContrat" =>  $contrat->getNumContrat(),
            "dateContrat" => $contrat->getDateContrat()->format("d/m/Y"),
            "bailleur" => $contrat->getBailleur()->getNom(),
            "bail" => $contrat->getBail()->getNom(),
            "locataire" =>  $contrat->getLocataire()->getNom(),
            "cycle" => $contrat->getCycle()->getNom(),
            "dateDebut" => $contrat->getDateDebut()->format("d/m/Y") ,
            "dateFin" => $contrat->getDateFin()->format("d/m/Y") ,
            "dureeContrat" => $contrat->getDuree()." ".$periode ,
            "montantForfait" =>  $contrat->getMontantForfait(),
            "forfaitLibelle" =>  $contrat->getForfait()->getLibelle(),
            "refForfait" => $contrat->getForfait()->getReference(),
            "typePaiement" => $contrat->getForfait()->getNom(),
            "statut" =>  $contrat->getStatut()->getNom(),
        ] ;

        $filename = "files/systeme/prestations/location/releveloyer(agence)/relevePL_".$id."_".$this->nameAgence  ;

        $this->appService->generateLctRelevePaiementLoyer($filename,$id) ;

        $relevePaiements = json_decode(file_get_contents($filename)) ;

        $statutLoyerPaye = $this->entityManager->getRepository(LctStatutLoyer::class)->findOneBy([
            "reference" => "PAYE"
        ]) ;

        $repartitions = $this->entityManager->getRepository(LctRepartition::class)->findBy([
            "contrat" => $contrat,
            "statut" => $statutLoyerPaye
        ]) ;

        $tableauMois = [] ;
        foreach ($relevePaiements as $relevePaiement) {
            $existPaiement = False ;
            foreach ($repartitions as $repartition) {
                $debutLimite = is_null($repartition->getDateDebut()) ? "" : $repartition->getDateDebut()->format("d/m/Y")  ;
                $resultCompare = $this->appService->compareDates($debutLimite, $relevePaiement->debutLimite, "E") ;
                if($resultCompare)
                {
                    $existPaiement = True ;
                    break;
                }
            }
            if($existPaiement)
                continue ;

            array_push($tableauMois,$relevePaiement) ;
        }

        $tableauMois = $this->appService->objectToArray($tableauMois) ;

        if($contrat->getCycle()->getReference() == "CMOIS")
        {
            if($contrat->getForfait()->getReference() == "FMOIS")
            {
                $response = $this->renderView("facture/location/paiementMensuel.html.twig",[
                    "item" => $item,
                    "tableauMois" => $tableauMois
                ]) ;
            }
        }
        else if($contrat->getCycle()->getReference() == "CJOUR")
        { 
            if($contrat->getForfait()->getReference() == "FJOUR")
            {
                $response = $this->renderView("facture/location/paiementJournaliere.html.twig",[
                    "item" => $item,
                    "tableauMois" => $tableauMois
                ]) ;
            }
        } 

        if($contrat->getForfait()->getReference() == "FORFAIT")
        {
            $response = $this->renderView("facture/location/paiementForfaitaire.html.twig",[
                "item" => $item,
                "tableauMois" => $tableauMois,
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

    #[Route('/facture/formulaire/avoir/get', name: 'facture_form_avoir_get')]
    public function factureGetFormulaireAvoir(Request $request)
    {
        $idClient = $request->request->get("idClient") ;

        if(empty($idClient))
            return new Response("") ;
        
        $filename = "files/systeme/sav/avoirs(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateListeAvoir($filename) ;
    
        $avoirs = json_decode(file_get_contents($filename)) ;

        $avoirs = $this->appService->objectToArray($avoirs) ;

        $oneAvoir = [] ;
    
        foreach ($avoirs as $avoir) 
        {
            if($avoir["idC"] == $idClient)
            {
                $oneAvoir = $avoir ;
                break ;
            }
        }

        if(empty($oneAvoir) || (isset($oneAvoir["remboursee"]) && $oneAvoir["remboursee"] <= 0))
            return new Response("") ;

        $devise = $this->agence->getDevise() ;
        if(!is_null($devise))
        {
            $lettreDevise = $devise->getLettre() ;
            $symboleDevise = $devise->getSymbole() ;
        }
        else
        {
            $lettreDevise = "" ;
            $symboleDevise = "" ;
        }

        $response = $this->renderView('facture/getFormulaireAvoir.html.twig',[
            "dataAvoir" => $oneAvoir,
            "lettreDevise" => $lettreDevise,
            "symboleDevise" => $symboleDevise,
        ]) ;

        return new Response($response) ;
    }

}
