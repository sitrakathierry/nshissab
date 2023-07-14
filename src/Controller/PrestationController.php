<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\BtpCategorie;
use App\Entity\BtpElement;
use App\Entity\BtpEnoncee;
use App\Entity\BtpMesure;
use App\Entity\BtpPrix;
use App\Entity\LctBail;
use App\Entity\LctBailleur;
use App\Entity\LctContrat;
use App\Entity\LctCycle;
use App\Entity\LctForfait;
use App\Entity\LctLocataire;
use App\Entity\LctModePaiement;
use App\Entity\LctPaiement;
use App\Entity\LctPeriode;
use App\Entity\LctRenouvellement;
use App\Entity\LctRepartition;
use App\Entity\LctStatut;
use App\Entity\LctStatutLoyer;
use App\Entity\LctTypeLocation;
use App\Entity\Service;
use App\Entity\SrvDuree;
use App\Entity\SrvFormat;
use App\Entity\SrvTarif;
use App\Entity\User;
use App\Service\AppService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class PrestationController extends AbstractController
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
        $this->filename = "files/systeme/prestations/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"] 
        ]) ;
    }

    #[Route('/prestation/service/creation', name: 'prest_creation_prestation')]
    public function prestCreationPrestation(): Response
    {
        return $this->render('prestations/creation.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Création prestation",
            "with_foot" => true,
        ]);
    }

    #[Route('/prestation/service/consultation', name: 'prest_consultation_prestation')]
    public function prestConsultationPrestation(): Response
    {
        $filename = $this->filename."service(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generatePrestationService($filename,$this->agence) ;

        $services = json_decode(file_get_contents($filename)) ;

        return $this->render('prestations/consultation.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Consultation prestation",
            "with_foot" => false,
            "services" => $services
        ]);
    }

    #[Route('/prestation/service/save', name: 'prest_service_save')]
    public function prestSaveService(Request $request)
    {
        $srv_nom = $request->request->get('srv_nom') ; 
        $srv_description = $request->request->get('srv_description') ; 

        $data = [
            $srv_nom
        ] ;
        
        $dataMessage = [
            "Nom"
        ] ;

        $result = $this->appService->verificationElement($data, $dataMessage) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $service = new Service() ;

        $service->setNom($srv_nom) ;
        $service->setDescription($srv_description) ;
        $service->setAgence($this->agence) ;
        $service->setStatut(True) ;
        $service->setCreateAt(new \DateTimeImmutable) ;
        $service->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($service) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."service(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse($result) ;
    }

    #[Route('/prestation/service/details/{id}', name: 'prest_details_service')]
    public function prestDetailsService($id): Response
    {

        $service = $this->entityManager->getRepository(Service::class)->find($id) ;
        $formats = $this->entityManager->getRepository(SrvFormat::class)->findAll() ;

        $tarifs = $this->entityManager->getRepository(SrvTarif::class)->findBy([
            "service" => $service,
            "statut" => True,
            ]) ;

        return $this->render('prestations/detailsService.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Detail Service",
            "with_foot" => true,
            "service" => $service,
            "formats" => $formats,
            "tarifs" => $tarifs
        ]);
    }

    #[Route('/prestation/batiment/creation', name: 'prest_batiment_creation')]
    public function prestCreationBatiment(): Response
    {
        $mesures = $this->entityManager->getRepository(BtpMesure::class)->findBy([
            "agence" => $this->agence,
            "statut" => True
        ]) ;

        $elements = $this->entityManager->getRepository(BtpElement::class)->findBy([
            "agence" => $this->agence,
            "statut" => True
        ]) ;

        return $this->render('prestations/batiment/creation.html.twig', [
            "filename" => "prestations",
            "titlePage" => "élément Batiment",
            "with_foot" => false,
            "mesures" => $mesures,
            "elements" => $elements
        ]);
    }

    #[Route('/prestation/batiment/element/save', name: 'prest_batiment_element_save')]
    public function prestSaveElementBatiment(Request $request)
    {
        $btp_elem_nom = $request->request->get('btp_elem_nom') ; 
        $btp_elem_mesure = $request->request->get('btp_elem_mesure') ; 

        $result = $this->appService->verificationElement([
            $btp_elem_nom,
            $btp_elem_mesure,
        ], [
            "Désignation",
            "Mésure"
        ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $mesure = $this->entityManager->getRepository(BtpMesure::class)->find($btp_elem_mesure) ;

        $element = new BtpElement() ;

        $element->setAgence($this->agence) ;
        $element->setNom($btp_elem_nom) ;
        $element->setMesure($mesure) ;
        $element->setStatut(True) ;

        $this->entityManager->persist($element) ;
        $this->entityManager->flush() ;

        $filename = "files/systeme/prestations/batiment/element(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse($result) ;
    }

    #[Route('/prestation/batiment/element/detail/{id}', name: 'prest_bat_detail_element')]
    public function prestDetailElementBatiment($id): Response
    {
        $element = $this->entityManager->getRepository(BtpElement::class)->find($id) ;

        $elemPrixs = $this->entityManager->getRepository(BtpPrix::class)->findBy([
            "element" => $element
        ]) ;

        // $filename = $this->filename."batimentPrix(agence)/".$this->nameAgence ;
        // if(file_exists($filename))
        // {
        //     $this->appService->generateBatimentPrix($filename, $this->agence) ;
        // }

        return $this->render('prestations/batiment/detailsElement.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Détails élément ",
            "with_foot" => false,
            "element" => $element,
            "elemPrixs" => $elemPrixs
        ]);
    }

    #[Route('/prestation/batiment/prix/save', name: 'prest_bat_prix_save')]
    public function prestSavePrixBatiment(Request $request)
    {
        $btp_elem_id = $request->request->get('btp_elem_id') ; 
        $btp_prix_pays = $request->request->get('btp_prix_pays') ; 
        $btp_prix_montant = $request->request->get('btp_prix_montant') ; 

        $result = $this->appService->verificationElement([
            $btp_prix_pays,
            $btp_prix_montant,
        ], [
            "Pays",
            "Prix Unitaire"
        ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $element = $this->entityManager->getRepository(BtpElement::class)->find($btp_elem_id) ;

        $prix = new BtpPrix() ;

        $prix->setElement($element) ;
        $prix->setPays($btp_prix_pays) ;
        $prix->setMontant(floatval($btp_prix_montant)) ;
        $prix->setStatut(True) ;
        $prix->setCreatedAt(new \DateTimeImmutable) ;
        $prix->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($prix) ;
        $this->entityManager->flush() ;

        return new JsonResponse($result) ;
    }
    
    #[Route('/prestation/batiment/enoncee', name: 'prest_batiment_enoncee')]
    public function prestEnonceeBatiment(): Response
    {
        $enoncees = $this->entityManager->getRepository(BtpEnoncee::class)->findBy([
            "agence" => $this->agence
            ]) ;
        
        return $this->render('prestations/batiment/enoncee.html.twig', [
            "filename" => "prestations",
            "titlePage" => "énoncé",
            "with_foot" => false,
            "enoncees" => $enoncees,
        ]);
    }

    #[Route('/prestation/batiment/enoncee/save', name: 'prest_batiment_enoncee_save')]
    public function prestSaveEnonceeBatiment(Request $request)
    {
        $btn_enc_nom = $request->request->get('btn_enc_nom') ; 

        $result = $this->appService->verificationElement([
            $btn_enc_nom,
        ], [
            "Nom Enoncé",
        ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $enoncee = new BtpEnoncee() ;

        $enoncee->setAgence($this->agence) ;
        $enoncee->setNom($btn_enc_nom) ;
        $enoncee->setStatut(True) ;

        $this->entityManager->persist($enoncee) ;
        $this->entityManager->flush() ;
        
        $filename = "files/systeme/prestations/batiment/enoncee(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse($result) ;
    }

    
    #[Route('/prestation/batiment/enoncee/detail/{id}', name: 'prest_bat_detail_enoncee')]
    public function prestDetailEnonceeBatiment($id)
    {
        $enoncee = $this->entityManager->getRepository(BtpEnoncee::class)->find($id) ;
        $mesures = $this->entityManager->getRepository(BtpMesure::class)->findBy([
            "agence" => $this->agence,
            "statut" => True
        ]) ;
        $categories = $this->entityManager->getRepository(BtpCategorie::class)->findBy([
            "enonce" => $enoncee
            ]) ;

        return $this->render('prestations/batiment/detailsEnoncee.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Détails énoncé",
            "with_foot" => false,
            "enoncee" => $enoncee,
            "categories" => $categories,
            "mesures" => $mesures
        ]);
    }

    #[Route('/prestation/batiment/categorie/save', name: 'prest_batiment_categorie_save')]
    public function prestSaveCategorieBatiment(Request $request)
    {
        $btn_cat_enonce = $request->request->get('btn_cat_enonce') ; 
        $btn_cat_nom = $request->request->get('btn_cat_nom') ; 
        $btn_cat_mesure = $request->request->get('btn_cat_mesure') ; 

        $result = $this->appService->verificationElement([
            $btn_cat_nom,
            $btn_cat_mesure,
        ], [
            "Nom Catégorie",
            "Mésure",
        ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $enonce = $this->entityManager->getRepository(BtpEnoncee::class)->find($btn_cat_enonce) ;
        $mesure = $this->entityManager->getRepository(BtpMesure::class)->find($btn_cat_mesure) ;

        $categorie = new BtpCategorie() ;

        $categorie->setEnonce($enonce) ;
        $categorie->setMesure($mesure) ;
        $categorie->setNom($btn_cat_nom) ;
        $categorie->setStatut(True) ;

        $this->entityManager->persist($categorie) ;
        $this->entityManager->flush() ;
        
        return new JsonResponse($result) ;
    }

    #[Route('/prestation/batiment/mesure', name: 'prest_batiment_mesure')]
    public function prestMesureBatiment(): Response
    {

        $mesures = $this->entityManager->getRepository(BtpMesure::class)->findBy([
            "agence" => $this->agence
            ]) ;

        return $this->render('prestations/batiment/mesure.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Mésure",
            "with_foot" => false,
            "mesures" => $mesures
        ]);
    }

    #[Route('/prestation/batiment/mesure/save', name: 'prest_batiment_mesure_save')]
    public function prestSaveMesureBatiment(Request $request)
    {
        $btp_mes_nom = $request->request->get('btp_mes_nom') ; 
        $btp_mes_notation = $request->request->get('btp_mes_notation') ; 

        $result = $this->appService->verificationElement([
            $btp_mes_nom,
            $btp_mes_notation,
        ], [
            "Nom",
            "Notation"
        ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $mesure = new BtpMesure() ;

        $mesure->setAgence($this->agence) ;
        $mesure->setNom($btp_mes_nom) ;
        $mesure->setNotation($btp_mes_notation) ;
        $mesure->setStatut(True) ;
        
        $this->entityManager->persist($mesure) ;
        $this->entityManager->flush() ;

        return new JsonResponse($result) ;
    }

    #[Route('/prestation/batiment/mesure/update', name: 'prest_btp_update_mesure')]
    public function prestUpdateMesureBatiment(Request $request)
    {
        $id = $request->request->get('id') ;
        $btp_mes_nom = $request->request->get('btp_mes_nom') ;
        $btp_mes_notation = $request->request->get('btp_mes_notation') ;

        $mesure = $this->entityManager->getRepository(BtpMesure::class)->find($id) ;
        
        $mesure->setNom($btp_mes_nom) ;
        $mesure->setNotation($btp_mes_notation) ;

        $this->entityManager->flush() ;

        return new JsonResponse([
            "type" => "green",
            "message" => "Modification effectué",
        ]) ;
    }

    #[Route('/prestation/service/duree/get', name: 'prest_service_duree_get')]
    public function prestGetServiceDuree()
    {
        $durees = $this->entityManager->getRepository(SrvDuree::class)->findAll() ;

        $responses = $this->renderView('prestations/getDuree.html.twig',[
            "durees" => $durees
            ]) ;
        
        return new Response($responses) ;
    }


    #[Route('/prestation/service/prix/save', name: 'prest_service_prix_save')]
    public function prestSaveServicePrix(Request $request)
    {
        $srv_service_id = $request->request->get('srv_service_id') ;
        $srv_tarif_format = $request->request->get('srv_tarif_format') ;
        $srv_tarif_duree = $request->request->get('srv_tarif_duree') ;
        $srv_tarif_prix = $request->request->get('srv_tarif_prix') ;

        $result = $this->appService->verificationElement([
            $srv_tarif_format,
            $srv_tarif_prix,
        ],[
            "Format",
            "Prix"
        ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;
        
        $service = $this->entityManager->getRepository(Service::class)->find($srv_service_id) ;
        $format = $this->entityManager->getRepository(SrvFormat::class)->find($srv_tarif_format) ;
        $duree = NULL ; 
        if($format->getReference() == "DRE")
        {
            $result = $this->appService->verificationElement([
                $srv_tarif_duree
            ],[
                "Durée",
            ]) ;
    
            if(!$result["allow"])
                return new JsonResponse($result) ;

            
            $duree = $this->entityManager->getRepository(SrvDuree::class)->find($srv_tarif_duree) ;
        }
        $nom = $duree == NULL ? $format->getNom() : $duree->getNom() ;

        $tarif = new SrvTarif() ;

        $tarif->setService($service) ;
        $tarif->setFormat($format) ;
        $tarif->setDuree($duree) ;
        $tarif->setNom($nom) ;
        $tarif->setPrix(floatval($srv_tarif_prix)) ;
        $tarif->setStatut(True) ;

        $this->entityManager->persist($tarif) ;
        $this->entityManager->flush() ;

        return new JsonResponse($result) ;
    }

    #[Route('/prestation/service/prix/get', name: 'prest_get_service_prix')]
    public function prestGetServicePrix(Request $request){
        $id = $request->request->get('idP') ;

        $tarifs = $this->entityManager->getRepository(SrvTarif::class)->findBy([
            "service" => $id,
            "statut" => True,
            ]) ;

        $response = $this->renderView("prestations/getPrix.html.twig",[
            "tarifs" => $tarifs
        ]) ;

        return new Response($response) ;
    }

    #[Route('/prestation/location/bailleur', name: 'prest_location_bailleur')]
    public function prestLocationBailleur(){

        $filename = $this->filename."location/bailleur(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateLocationBailleur($filename, $this->agence) ; 

        $bailleurs = json_decode(file_get_contents($filename)) ;
        
        return $this->render('prestations/location/bailleur.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Bailleur",
            "with_foot" => true,
            "bailleurs" => $bailleurs
        ]);
    }

    #[Route('/prestation/location/detail/{id}', name: 'prest_location_bailleur_detail')]
    public function prestDetailLocationBailleur($id){
        $bailleur = $this->entityManager->getRepository(LctBailleur::class)->find($id) ;

        $bails = $this->entityManager->getRepository(LctBail::class)->findBy([
            "statut" => True
        ]) ;

        return $this->render('prestations/location/detailBailleur.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Detail Bailleur",
            "with_foot" => true,
            "bailleur" => $bailleur,
            "bails" => $bails
        ]);
    }

    #[Route('/prestation/location/bailleur/save', name: 'prest_location_bailleur_save')]
    public function prestSaveLocationBailleur(Request $request){
        $prest_lct_prop_nom = $request->request->get('prest_lct_prop_nom') ;
        $prest_lct_prop_tel = $request->request->get('prest_lct_prop_tel') ;
        $prest_lct_prop_adresse = $request->request->get('prest_lct_prop_adresse') ;

        $result = $this->appService->verificationElement([
            $prest_lct_prop_nom,
            $prest_lct_prop_tel,
            $prest_lct_prop_adresse,
        ],[
            "Nom Propriétaire",
            "Téléphone",
            "Adresse"
        ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $bailleur = new LctBailleur() ;

        $bailleur->setAgence($this->agence) ;
        $bailleur->setNom($prest_lct_prop_nom) ;
        $bailleur->setTelephone($prest_lct_prop_tel) ;
        $bailleur->setAdresse($prest_lct_prop_adresse) ;
        $bailleur->setStatut(True) ;

        $this->entityManager->persist($bailleur) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."location/bailleur(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse($result) ;
    }

    #[Route('/prestation/location/bail/save', name: 'prest_location_bail_save')]
    public function prestSaveLocationBail(Request $request){
        $prest_lct_bailleur_id = $request->request->get('prest_lct_bailleur_id') ;
        $prest_lct_bail_nom = $request->request->get('prest_lct_bail_nom') ;
        $prest_lct_bail_dimension = $request->request->get('prest_lct_bail_dimension') ;
        $prest_lct_bail_lieu = $request->request->get('prest_lct_bail_lieu') ;
        
        $result = $this->appService->verificationElement([
            $prest_lct_bail_nom,
            $prest_lct_bail_lieu,
            $prest_lct_bail_dimension,
        ],[
            "Nom Location",
            "Adresse",
            "Dimension",
            "Montant",
            "Caution"
        ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $bailleur = $this->entityManager->getRepository(LctBailleur::class)->find($prest_lct_bailleur_id) ;
        $bail = new LctBail() ;

        $bail->setBailleur($bailleur) ;
        $bail->setNom($prest_lct_bail_nom) ;
        $bail->setLieux($prest_lct_bail_lieu) ;
        $bail->setDimension($prest_lct_bail_dimension) ;
        $bail->setStatut(True) ;

        $this->entityManager->persist($bail) ;
        $this->entityManager->flush() ;

        return new JsonResponse($result) ;
    }

    #[Route('/prestation/location/contrat', name: 'prest_location_contrat')]
    public function prestLocationContrat(){
        $filename = $this->filename."location/bailleur(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateLocationBailleur($filename, $this->agence) ; 

        $bailleurs = json_decode(file_get_contents($filename)) ;

        $type_locs = $this->entityManager->getRepository(LctTypeLocation::class)->findAll() ;
        $cycles = $this->entityManager->getRepository(LctCycle::class)->findAll() ;
        $renouvs = $this->entityManager->getRepository(LctRenouvellement::class)->findAll() ;
        $periodes = $this->entityManager->getRepository(LctPeriode::class)->findBy([],["rang" => "ASC"]) ;
        $forfaits = $this->entityManager->getRepository(LctForfait::class)->findAll() ;
        $modePaiements = $this->entityManager->getRepository(LctModePaiement::class)->findAll() ;

        return $this->render('prestations/location/contrat.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Nouveau contrat",
            "with_foot" => true,
            "bailleurs" => $bailleurs,
            "type_locs" => $type_locs,
            "periodes" => $periodes,
            "renouvs" => $renouvs,
            "cycles" => $cycles,
            "forfaits" => $forfaits,
            "modePaiements" => $modePaiements,

        ]);
    }

    #[Route('/prestation/location/contrat/liste', name: 'prest_location_contrat_liste')]
    public function prestLocationContratListe(){
        $filename = $this->filename."location/contrat(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateLocationContrat($filename, $this->agence) ; 

        $contrats = json_decode(file_get_contents($filename)) ;

        return $this->render('prestations/location/listeContrat.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Liste des contrats",
            "with_foot" => false,
            "contrats" => $contrats
        ]);
    }
    
    #[Route('/prestation/location/contrat/commission', name: 'prest_location_contrat_commissions')]
    public function prestLocationCommissionContrat(){
        // $filename = $this->filename."location/contrat(agence)/".$this->nameAgence ;

        // if(!file_exists($filename))
        //     $this->appService->generateLocationContrat($filename, $this->agence) ; 

        // $contrats = json_decode(file_get_contents($filename)) ;

        $contrats = $this->entityManager->getRepository(LctContrat::class)->findAll() ;

        $tabContrats = [] ;

            foreach ($contrats as $contrat) {
                if((is_null($contrat->getPourcentage()) || empty($contrat->getPourcentage())))
                    continue ;

                if($contrat->getForfait()->getReference() == "FORFAIT")
                {
                    if($contrat->getPeriode()->getReference() == "J")
                {
                    $periode = " Jour(s)" ;
                }
                if($contrat->getPeriode()->getReference() == "M")
                {
                    $periode = " Mois" ;
                }
                else if($contrat->getPeriode()->getReference() == "A")
                {
                    $periode = " An(s)" ;
                }

                $lastRecordPaiement = $this->entityManager->getRepository(LctPaiement::class)->findOneBy(["contrat" => $contrat], ['id' => 'DESC']);
                $datePaiement = !is_null($lastRecordPaiement) ? ($lastRecordPaiement->getDate()->format("d/m/Y")) : "-" ;
                

                $elemC = [] ;

                $elemC["numContrat"] = $contrat->getNumContrat() ;
                $elemC["bail"] = $contrat->getBail()->getNom()." | ".$contrat->getBail()->getLieux() ;
                $elemC["locataire"] = $contrat->getLocataire()->getNom() ;
                $elemC["cycle"] = $contrat->getCycle()->getNom() ;
                $elemC["duree"] = $contrat->getDuree().$periode ;
                $elemC["datePaiement"] = $datePaiement ;
                $elemC["commission"] = ($contrat->getMontantContrat() * $contrat->getPourcentage()) / 100;

                array_push($tabContrats,$elemC) ;

                continue ;
            }

            $repartitions = $this->entityManager->getRepository(LctRepartition::class)->findBy([
                "contrat" => $contrat 
            ]) ;
    
            $childs = [] ;
            
            $totalReleve = 0 ;
    
            foreach ($repartitions as $repartition) {
                $item = [] ;
    
                $statutRepart = $repartition->getStatut()->getReference() ; 
                if($statutRepart == "CAUTION")
                    continue ;
    
                $item["dateDebut"] = is_null($repartition->getDateDebut()) ? "NONE" : $repartition->getDateDebut()->format("d/m/Y") ;
                $item["montant"] = $repartition->getMontant() ;
                $item["statut"] = $repartition->getStatut()->getReference() ;

                $totalReleve += $repartition->getMontant() ; 
                array_push($childs,$item) ;
            }
    
            $resultat = array_reduce($childs, function($carry, $contenu) {
                $dateDebut = $contenu['dateDebut'];
                
                if (!isset($carry[$dateDebut])) {
                    $carry[$dateDebut] = $contenu;
                } else {
                    $carry[$dateDebut]['montant'] += $contenu['montant'];
                }
                
                return $carry;
            }, []);
            
            $childs = array_values($resultat); 
            $newChilds = [] ;
            
            foreach ($childs as $child) {
                $elem = [] ;
                
                $elem["dateDebut"] = $child["dateDebut"] ;
                $elem["montant"] = $child["montant"] ;
                
                if($child["montant"] == $contrat->getMontantForfait())
                {
                    array_push($newChilds,$elem) ;
                }
                
            }

            $commission = 0 ;
            foreach ($newChilds as $newChild) {
                $commission += ($newChild["montant"] * $contrat->getPourcentage()) / 100 ;
            }

            if($contrat->getPeriode()->getReference() == "J")
            {
                $periode = " Jour(s)" ;
            }
            if($contrat->getPeriode()->getReference() == "M")
            {
                $periode = " Mois" ;
            }
            else if($contrat->getPeriode()->getReference() == "A")
            {
                $periode = " An(s)" ;
            }

            $lastRecordPaiement = $this->entityManager->getRepository(LctPaiement::class)->findOneBy(["contrat" => $contrat], ['id' => 'DESC']);
            $datePaiement = !is_null($lastRecordPaiement) ? ($lastRecordPaiement->getDate()->format("d/m/Y")) : "-" ;
            

            $elemC = [] ;

            $elemC["numContrat"] = $contrat->getNumContrat() ;
            $elemC["bail"] = $contrat->getBail()->getNom()." | ".$contrat->getBail()->getLieux() ;
            $elemC["locataire"] = $contrat->getLocataire()->getNom() ;
            $elemC["cycle"] = $contrat->getCycle()->getNom() ;
            $elemC["duree"] = $contrat->getDuree().$periode ;
            $elemC["datePaiement"] = $datePaiement ;
            $elemC["commission"] = $commission ;

            array_push($tabContrats,$elemC) ;
        }

        return $this->render('prestations/location/listeCommissionContrat.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Liste des commissions",
            "with_foot" => false,
            "contrats" => $tabContrats
        ]);
    }

    #[Route('/prestation/location/bailleur/get', name: 'prest_location_bailleur_get')]
    public function prestGetBailleurLocation(Request $request){
        $id = $request->request->get('id') ;

        $bailleur = $this->entityManager->getRepository(LctBailleur::class)->find($id) ;

        $response = [] ;
        if(!is_null($bailleur))
        {
            $bails = $this->entityManager->getRepository(LctBail::class)->findBy([
                "bailleur" => $bailleur
            ]) ; 

            $response["telephone"] = $bailleur->getTelephone() ;
            $response["adresse"] = $bailleur->getAdresse() ;
            $response["bails"] = [] ;

            foreach ($bails as $bail) {
                array_push($response["bails"],[
                    "id" => $bail->getId(),
                    "nom" => $bail->getNom(),
                    "lieu" => $bail->getLieux()
                ]) ;
            }

        }
        else
        {
            $response["telephone"] = "" ;
            $response["adresse"] = "" ;
            $response["bails"] = [] ;
        }

        return new JsonResponse($response) ;
    }

    #[Route('/prestation/location/locataire/get', name: 'prest_location_locataire_get')]
    public function prestGetLocataireLocation(Request $request){
        $id = $request->request->get('id') ;

        $locataire = $this->entityManager->getRepository(LctLocataire::class)->find($id) ;

        $response = [] ;
        if(!is_null($locataire))
        {
            $response["telephone"] = $locataire->getTelephone() ;
            $response["adresse"] = $locataire->getAdresse() ;
            $response["email"] = $locataire->getEmail() ;
        }
        else
        {
            $response["telephone"] = "" ;
            $response["adresse"] = "" ;
            $response["email"] = "" ;
        }

        return new JsonResponse($response) ;
    }

    #[Route('/prestation/location/cycle/get', name: 'prest_get_cycle_rules')]
    public function prestGetCycleRules(Request $request){
        $id = $request->request->get('id') ;
        if($id == "")
        {
            $response = '<option value="" data-target="#forfaitRef" data-libelle="" data-reference="">-</option>@##@<option value="" data-target="#periodeRef" data-reference="" data-libelle="" >-</option>@##@<option value="" data-reference="">-</option>';
            return new Response($response) ;
        }

        $cycle = $this->entityManager->getRepository(LctCycle::class)->find($id) ;

        $typePaiement = [
            "CJOUR" => ["FJOUR","FORFAIT"],
            "CMOIS" => ["FMOIS","FORFAIT"]
        ] ;
        
        $optionForfait = '<option value="" data-target="#forfaitRef" data-libelle="" data-reference="">-</option>' ;
        foreach ($typePaiement[$cycle->getReference()] as $forfait) {
            $forfaitObj = $this->entityManager->getRepository(LctForfait::class)->findOneBy([
                "reference" => $forfait
            ]) ;
            
            $optionForfait .= '<option value="'.$forfaitObj->getId().'" data-target="#forfaitRef" data-reference="'.$forfaitObj->getReference().'" data-libelle="'.$forfaitObj->getLibelle().'" >'.strtoupper($forfaitObj->getNom()).'</option>' ;
        }

        $periode = [
            "CJOUR" => ["J"],
            "CMOIS" => ["M","A"]
        ] ;

        $optionPeriode = '<option value="" data-target="#periodeRef" data-reference="" data-libelle="" >-</option>' ;
        foreach ($periode[$cycle->getReference()] as $periode) {
            $periodeObj = $this->entityManager->getRepository(LctPeriode::class)->findOneBy([
                "reference" => $periode
            ]) ;

            $optionPeriode .= '<option value="'.$periodeObj->getId().'" data-target="#periodeRef" data-reference="'.$periodeObj->getReference().'" data-libelle="'.$periodeObj->getNom().'" >'.strtoupper($periodeObj->getNom()).'</option>' ;
        }

        $renouv = [
            "CJOUR" => ["RVL","AUTRE"],
            "CMOIS" => ["TCT","RVL","AUTRE"]
        ] ;

        $optionRenouv = '<option value="" data-reference="">-</option>' ;
        foreach ($renouv[$cycle->getReference()] as $renouvl) {
            $renouvObj = $this->entityManager->getRepository(LctRenouvellement::class)->findOneBy([
                "reference" => $renouvl
            ]) ;

            $optionRenouv .= '<option value="'.$renouvObj->getId().'" data-reference="'.$renouvObj->getReference().'">'.strtoupper($renouvObj->getNom()).'</option>' ;
        }

        $response = $optionForfait."@##@".$optionPeriode."@##@".$optionRenouv ;


        return new Response($response) ;
    }
    
    #[Route('/prestation/location/bailleur/new', name: 'prest_new_location_bailleur')]
    public function prestNewLocationBailleur(){
        $response = $this->renderView("prestations/location/getNewBailleur.html.twig") ;
        return new Response($response) ;
    }

    #[Route('/prestation/location/locataire/new', name: 'prest_new_location_locataire')]
    public function prestNewLocationLocataire(){
        $response = $this->renderView("prestations/location/getNewLocataire.html.twig") ;
        return new Response($response) ;
    }

    #[Route('/prestation/location/bailleur/existing', name: 'prest_existing_location_bailleur')]
    public function prestExistingLocationBailleur(){
        $filename = $this->filename."location/bailleur(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateLocationBailleur($filename, $this->agence) ; 

        $bailleurs = json_decode(file_get_contents($filename)) ;

        $response = $this->renderView("prestations/location/getExistingBailleur.html.twig",[
            "bailleurs" => $bailleurs
            ]) ;
        return new Response($response) ;
    }

    #[Route('/prestation/location/locataire/existing', name: 'prest_existing_locataire')]
    public function prestExistingLocataire(){
        $filename = $this->filename."location/locataire(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateLocationLocataire($filename, $this->agence) ; 

        $locataires = json_decode(file_get_contents($filename)) ;

        $response = $this->renderView("prestations/location/getExistingLocataire.html.twig",[
            "locataires" => $locataires
            ]) ;

        if(empty($locataires))
        {
            $response = "" ;
        }
        return new Response($response) ;
    }

    #[Route('/prestation/location/bail/new', name: 'prest_new_location_bail')]
    public function prestNewLocationBail()
    {
        $type_locs = $this->entityManager->getRepository(LctTypeLocation::class)->findAll() ;
        $response = $this->renderView("prestations/location/getNewBail.html.twig",[
            "type_locs" => $type_locs
        ]) ;
        return new Response($response) ;
    }

    #[Route('/prestation/location/bail/existing', name: 'prest_existing_location_bail')]
    public function prestExistingLocationBail(Request $request){

        $id = $request->request->get('id') ;

        $bailleur = $this->entityManager->getRepository(LctBailleur::class)->find($id) ;

        $bails = $this->entityManager->getRepository(LctBail::class)->findBy([
            "bailleur" => $bailleur
        ]) ;
        if(empty($bails))
        {
            return new Response("") ;
        }
        $items = [] ;
        foreach ($bails as $bail) {
            array_push($items,[
                "id" => $bail->getId(),
                "nom" => $bail->getNom(),
                "lieu" => $bail->getLieux()
            ]) ;
        }
        $type_locs = $this->entityManager->getRepository(LctTypeLocation::class)->findAll() ;
        $response = $this->renderView("prestations/location/getExistingBail.html.twig",[
            "bails" => $items,
            "type_locs" => $type_locs,
        ]) ;
        return new Response($response) ;
    }

    
    #[Route('/prestation/location/bail/get', name: 'prest_location_bail_get')]
    public function prestGetBailLocation(Request $request){
        $id = $request->request->get('id') ;
        $bail = $this->entityManager->getRepository(LctBail::class)->find($id) ;
        $response = [] ;
        if(!is_null($bail))
        {
            $response["dimension"] = $bail->getDimension() ;
            $response["adresse"] = $bail->getLieux() ;
        }
        else
        {
            $response["dimension"] = "" ;
            $response["adresse"] = "" ;
        }

        return new JsonResponse($response) ;
    }

    
    #[Route('/prestation/location/contrat/save', name: 'prest_location_contrat_save')]
    public function prestSaveContratLocation(Request $request)
    {
        // GESTION D'ERREUR 
        $prest_ctr_cycle = $request->request->get("prest_ctr_cycle") ;
        $prest_ctr_forfait = $request->request->get("prest_ctr_forfait") ;
        $prest_ctr_renouvellement = $request->request->get("prest_ctr_renouvellement") ;
        $prest_ctr_mode = $request->request->get("prest_ctr_mode") ;
        $prest_ctr_delai_mode = $request->request->get("prest_ctr_delai_mode") ;
        $prest_ctr_delai_change = $request->request->get("prest_ctr_delai_change") ;

        $forfait = $this->entityManager->getRepository(LctForfait::class)->find($prest_ctr_forfait) ;
        $cycle = $this->entityManager->getRepository(LctCycle::class)->find($prest_ctr_cycle) ;
        
        if($cycle->getReference() == "CMOIS" && $forfait->getReference() == "FMOIS")
        {
            $result = $this->appService->verificationElement([
                $prest_ctr_renouvellement,
                $prest_ctr_mode,
                $prest_ctr_delai_mode,
                $prest_ctr_delai_change,
            ],[
                "Renouvellement",
                "Mode de paiement Loyer",
                "Date Limite de Paiement",
                "Changement avant fin du contrat"
            ]) ;
    
            if(!$result["allow"])
            {
                $result["caution"] = "SANS" ;
                return new JsonResponse($result) ;
            }
        }
        else
        {
            $prest_ctr_delai_mode = $prest_ctr_delai_mode == "" ? NULL : $prest_ctr_delai_mode ;
            $prest_ctr_delai_change = $prest_ctr_delai_change == "" ? NULL : $prest_ctr_delai_change ;
        }

        // VERIFICATION DU BAILLEUR
        $prest_ctr_prop_nom = $request->request->get("prest_ctr_prop_nom") ;
        $prest_ctr_prop_phone = $request->request->get("prest_ctr_prop_phone") ;
        $prest_ctr_prop_adresse = $request->request->get("prest_ctr_prop_adresse") ;
        $prest_ctr_prop_nouveau = $request->request->get("prest_ctr_prop_nouveau") ;

        if($prest_ctr_prop_nouveau == "OUI")
        {
            $bailleur = new LctBailleur() ;

            $bailleur->setAgence($this->agence) ;
            $bailleur->setNom($prest_ctr_prop_nom) ;
            $bailleur->setTelephone($prest_ctr_prop_phone) ;
            $bailleur->setAdresse($prest_ctr_prop_adresse) ;
            $bailleur->setStatut(True) ;
    
            $this->entityManager->persist($bailleur) ;
            $this->entityManager->flush() ;
    
            $filename = $this->filename."location/bailleur(agence)/".$this->nameAgence ;
            if(file_exists($filename))
                unlink($filename) ;
        }
        else
        {
            $bailleur = $this->entityManager->getRepository(LctBailleur::class)->find($prest_ctr_prop_nom) ;
        }

        // VERIFICATION DU LOCATAIRE
        $prest_ctr_clt_nom = $request->request->get("prest_ctr_clt_nom") ;
        $prest_ctr_clt_nouveau = $request->request->get("prest_ctr_clt_nouveau") ;
        $prest_ctr_clt_telephone = $request->request->get("prest_ctr_clt_telephone") ;
        $prest_ctr_clt_adresse = $request->request->get("prest_ctr_clt_adresse") ;
        $prest_ctr_clt_email = $request->request->get("prest_ctr_clt_email") ;

        if($prest_ctr_clt_nouveau == "OUI")
        {
            $locataire = new LctLocataire() ;

            $locataire->setAgence($this->agence) ;
            $locataire->setNom($prest_ctr_clt_nom) ;
            $locataire->setTelephone($prest_ctr_clt_telephone) ;
            $locataire->setAdresse($prest_ctr_clt_adresse) ;
            $locataire->setEmail($prest_ctr_clt_email) ;
            $locataire->setStatut(True) ;
    
            $this->entityManager->persist($locataire) ;
            $this->entityManager->flush() ;
        }
        else
        {
            $locataire = $this->entityManager->getRepository(LctLocataire::class)->find($prest_ctr_clt_nom) ;
        }

        // VERIFICATION DU BAIL
        $prest_ctr_bail_type_location = $request->request->get("prest_ctr_bail_type_location") ;
        $prest_ctr_bail_location = $request->request->get("prest_ctr_bail_location") ;
        $prest_ctr_bail_nouveau = $request->request->get("prest_ctr_bail_nouveau") ;
        $prest_ctr_bail_adresse = $request->request->get("prest_ctr_bail_adresse") ;
        $prest_ctr_bail_dimension = $request->request->get("prest_ctr_bail_dimension") ;

        if($prest_ctr_bail_nouveau == "OUI")
        {
            $bail = new LctBail() ;

            $bail->setBailleur($bailleur) ;
            $bail->setNom($prest_ctr_bail_location) ;
            $bail->setLieux($prest_ctr_bail_adresse) ;
            $bail->setDimension($prest_ctr_bail_dimension) ;
            $bail->setStatut(True) ;

            $this->entityManager->persist($bail) ;
            $this->entityManager->flush() ;
        }
        else
        {
            $bail = $this->entityManager->getRepository(LctBail::class)->find($prest_ctr_bail_location) ;
        }

        // ENREGISTREMENT DU CONTRAT

        $lastRecordFContrat = $this->entityManager->getRepository(LctContrat::class)->findOneBy([], ['id' => 'DESC']);
        $numContrat = !is_null($lastRecordFContrat) ? ($lastRecordFContrat->getId()+1) : 1 ;
        $numContrat = str_pad($numContrat, 5, "0", STR_PAD_LEFT);
        $numContrat = "CTR-".$numContrat."/".date('y') ; 

        
        $prest_ctr_montant_forfait = $request->request->get("prest_ctr_montant_forfait") ;
        $prest_ctr_duree = $request->request->get("prest_ctr_duree") ;
        $prest_ctr_periode = $request->request->get("prest_ctr_periode") ;
        $prest_ctr_date_debut = $request->request->get("prest_ctr_date_debut") ;
        $prest_ctr_date_fin = $request->request->get("prest_ctr_date_fin") ;
        $prest_ctr_retenu = $request->request->get("prest_ctr_retenu") ;
        $prest_ctr_retenu = $prest_ctr_retenu == "" ? NULL : $prest_ctr_retenu ;
        $prest_ctr_bail_caution = $request->request->get("prest_ctr_bail_caution") ;
        $prest_ctr_bail_caution = $prest_ctr_bail_caution == "" ? NULL : $prest_ctr_bail_caution ;
        $prest_ctr_montant_contrat = $request->request->get("prest_ctr_montant_contrat") ;
        $prest_ctr_delai_change = $prest_ctr_delai_change == "AUTRE" ? ($request->request->get("prest_ctr_autre_valeur")  == "" ? NULL : $request->request->get("prest_ctr_autre_valeur")) : $prest_ctr_delai_change;
        $prest_ctr_renouvellement_autre = $request->request->get("prest_ctr_renouvellement_autre") ;
        $contrat_editor = $request->request->get("contrat_editor") ;
        $ctr_lieu = $request->request->get("ctr_lieu") ;
        $ctr_date = $request->request->get("ctr_date") ;
        
        $type_loc = $this->entityManager->getRepository(LctTypeLocation::class)->find($prest_ctr_bail_type_location) ;
        $renouv = $this->entityManager->getRepository(LctRenouvellement::class)->find($prest_ctr_renouvellement) ;
        $periode = $this->entityManager->getRepository(LctPeriode::class)->find($prest_ctr_periode) ;
        $modePaiement = $this->entityManager->getRepository(LctModePaiement::class)->find($prest_ctr_mode) ;
        $statut = $this->entityManager->getRepository(LctStatut::class)->findOneBy([
            "reference" => "ENCR"
        ]) ;

        $contrat = new LctContrat() ;

        $contrat->setAgence($this->agence) ;
        $contrat->setBailleur($bailleur) ;
        $contrat->setBail($bail) ;
        $contrat->setLocataire($locataire) ;
        $contrat->setNumContrat($numContrat) ;
        $contrat->setMontantContrat($prest_ctr_montant_contrat) ;
        $contrat->setCycle($cycle) ;
        $contrat->setForfait($forfait) ;
        $contrat->setMontantForfait($prest_ctr_montant_forfait) ;
        $contrat->setDuree($prest_ctr_duree) ;
        $contrat->setPeriode($periode) ;
        $contrat->setPourcentage($prest_ctr_retenu) ;
        $contrat->setRenouvellement($renouv) ;
        $contrat->setCaptionRenouv($prest_ctr_renouvellement_autre) ;
        $contrat->setTypeLocation($type_loc) ;
        $contrat->setDateDebut(\DateTime::createFromFormat('j/m/Y',$prest_ctr_date_debut)) ;
        $contrat->setDateFin(\DateTime::createFromFormat('j/m/Y',$prest_ctr_date_fin)) ;
        $contrat->setModePaiement($modePaiement) ;
        $contrat->setDateLimite($prest_ctr_delai_mode) ;
        $contrat->setCaution($prest_ctr_bail_caution) ;
        $contrat->setDelaiChgFin($prest_ctr_delai_change) ;
        $contrat->setNote($contrat_editor) ;
        $contrat->setLieuContrat($ctr_lieu) ;
        $contrat->setDateContrat(\DateTime::createFromFormat('j/m/Y',$ctr_date)) ;
        $contrat->setStatut($statut) ;
        $contrat->setCreatedAt(new \DateTimeImmutable) ; 
        $contrat->setUpdatedAt(new \DateTimeImmutable) ; 

        $this->entityManager->persist($contrat) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."location/contrat(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;

        $indcCaution = !is_null($prest_ctr_bail_caution) ? "AVEC" : "SANS" ;
        return new JsonResponse([
            "type" => "green",
            "message" => "Contrat enregistré",
            "caution" => $indcCaution,
            "contrat" => $contrat->getId(),
            "montantCtn" => $prest_ctr_bail_caution
            ]) ;
    }

    #[Route('/prestation/location/contrat/detail/{id}', name: 'prest_location_contrat_detail')]
    public function prestDetailContratLocation($id)
    {
        $contrat = $this->entityManager->getRepository(LctContrat::class)->find($id) ;
        
        $bailleur = [
            "nom" => $contrat->getBailleur()->getNom(),
            "telephone" => $contrat->getBailleur()->getTelephone(),
            "adresse" => $contrat->getBailleur()->getAdresse(),
        ] ;

        $locataire = [
            "nom" => $contrat->getLocataire()->getNom(),
            "telephone" =>$contrat->getLocataire()->getTelephone(),
            "adresse" => $contrat->getLocataire()->getAdresse(),
            "email" => $contrat->getLocataire()->getEmail(),
        ] ;

        $bail = [
            "nom" => $contrat->getBail()->getNom(),
            "adresse" => $contrat->getBail()->getLieux(),
            "dimension" => $contrat->getBail()->getDimension(),
        ] ;

        $parent = [
            "typeLocation" => $contrat->getTypeLocation()->getNom(),
            "cycle" => $contrat->getCycle()->getNom(),
            "typePaiement" => $contrat->getForfait()->getNom(),
            "montantForfait" => $contrat->getMontantForfait(),
            "numContrat" => $contrat->getNumContrat(),
            "duree" => $contrat->getDuree(),
            "date" => $contrat->getDateContrat()->format("d/m/Y"),
            "lieu" => $contrat->getLieuContrat(),
            "periode" => $contrat->getPeriode()->getNom(),
            "dateDebut" => $contrat->getDateDebut()->format("d/m/Y") ,
            "dateFin" => $contrat->getDateFin()->format("d/m/Y") ,
            "retenu" => is_null($contrat->getPourcentage()) ? "" : $contrat->getPourcentage(),
            "renouveau" => empty($contrat->getRenouvellement()) ? "" : $contrat->getRenouvellement()->getNom(),
            "modePaiement" => is_null($contrat->getModePaiement()) ? "" : $contrat->getModePaiement()->getNom(),
            "isModeP" => !is_null($contrat->getModePaiement()),
            "dateLimite" => is_null($contrat->getDateLimite()) ? "" : "Jusqu'au ".$contrat->getDateLimite()." du mois",
            "caution" => empty($contrat->getCaution()) ? "" : $contrat->getCaution(),
            "isCaution" => !empty($contrat->getCaution()),
            "montantContrat" => $contrat->getMontantContrat(),
            "changement" => empty($contrat->getDelaiChgFin()) ? "" : $contrat->getDelaiChgFin()." Jours avant la fin du contrat"
        ] ;

        return $this->render('prestations/location/detailsContrat.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Detail contrat",
            "with_foot" => true,
            "contrat" => $parent,
            "bailleur" => $bailleur,
            "locataire" => $locataire,
            "bail" => $bail,
        ]);
        
        
    }

    #[Route('/prestation/location/contrat/releve/loyer/{id}', name: 'prest_location_contrat_releve_loyer')]
    public function prestReleveLoyerContratLocation($id)
    {
        $contrat = $this->entityManager->getRepository(LctContrat::class)->find($id) ;
        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        $repartitions = $this->entityManager->getRepository(LctRepartition::class)->findBy([
            "contrat" => $contrat 
        ]) ;

        $childs = [] ;
        
        $totalReleve = 0 ;

        foreach ($repartitions as $repartition) {
            $item = [] ;

            $statutRepart = $repartition->getStatut()->getReference() ; 
            if($statutRepart == "CAUTION")
                continue ;

            // $refForfait =  $contrat->getForfait()->getReference() ;
            // if($refForfait == "FJOUR")
            //     $moment = $repartition->getDateDebut()->format("d/m/Y") ;
            // else 
            //     $moment = $tabMois[$repartition->getMois() - 1] ." ". $repartition->getAnnee() ;

            $item["designation"] = $repartition->getDesignation() ;
            $item["debutLimite"] = is_null($repartition->getDateDebut()) ? "" : $repartition->getDateDebut()->format("d/m/Y") ;
            $item["dateLimite"] = is_null($repartition->getDateLimite()) ? "" : $repartition->getDateLimite()->format("d/m/Y") ;
            $item["datePaiement"] = $repartition->getPaiement()->getDate()->format("d/m/Y") ;
            $item["mois"] = is_null($repartition->getMois()) ? "" : $tabMois[$repartition->getMois() - 1]  ;
            $item["annee"] = $repartition->getAnnee() ;
            // $item["moment"] = $moment ;
            $item["dateDebut"] = is_null($repartition->getDateDebut()) ? "NONE" : $repartition->getDateDebut()->format("d/m/Y") ;
            $item["montant"] = $repartition->getMontant() ;
            $item["statut"] = $repartition->getStatut()->getReference() ;

            $totalReleve += $repartition->getMontant() ; 
            array_push($childs,$item) ;
        }
        
        $listeForfait = $childs ;

        $resultat = array_reduce($childs, function($carry, $contenu) {
            $dateDebut = $contenu['dateDebut'];
            
            if (!isset($carry[$dateDebut])) {
                $carry[$dateDebut] = $contenu;
            } else {
                $carry[$dateDebut]['montant'] += $contenu['montant'];
            }
            
            return $carry;
        }, []);
        
        $childs = array_values($resultat); 
        $newChilds = [] ;

        foreach ($childs as $child) {
            $elem = [] ;
            
            // $elem["moment"] = $child["moment"] ;
            $elem["debutLimite"] = $child["debutLimite"] ;
            $elem["dateLimite"] = $child["dateLimite"] ;
            $elem["datePaiement"] = $child["datePaiement"] ;
            $elem["mois"] = $child["mois"] ;
            $elem["annee"] = $child["annee"] ;
            $elem["dateDebut"] = $child["dateDebut"] ;
            $elem["montant"] = $child["montant"] ;

            if($child["montant"] == $contrat->getMontantForfait())
            {
                $elem["designation"] = "Paiement. ".$child["designation"] ;
                $elem["statut"] = "Payée" ;
            }
            else
            {
                $elem["designation"] = "Acompte. ".$child["designation"] ;
                $elem["statut"] = "Acompte" ;
            }
            array_push($newChilds,$elem) ;
        }

        $lettreReleve = $this->appService->NumberToLetter($totalReleve) ;

        $parent = [
            "numContrat" => $contrat->getNumContrat(),
            "montantContrat" => $contrat->getMontantContrat(),
            "bailleur" => $contrat->getBailleur()->getNom(),
            "locataire" => $contrat->getLocataire()->getNom(),
            "bail" => $contrat->getBail()->getNom(),
            "lieu" => $contrat->getLieuContrat(),
            "isCaution" => !empty($contrat->getCaution()),
        ] ;

        $statutLoyerPaye = $this->entityManager->getRepository(LctStatutLoyer::class)->findOneBy([
            "reference" => "PAYE"
        ]) ;

        if($contrat->getCycle()->getReference() == "CMOIS")
        {
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
                    }
                    else if($statutLastRpt == "PAYE")
                    {
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

                if($contrat->getPeriode()->getReference() == "M")
                    $duree = $contrat->getDuree() ;  
                else if($contrat->getPeriode()->getReference() == "A")
                    $duree = $contrat->getDuree() * 12 ; 
                
                $duree -= $moisEcoule ;

                $dateAvant = $this->appService->calculerDateAvantNjours($dateDebut,30) ;
                $dateGenere = $contrat->getModePaiement()->getReference() == "DEBUT" ? $dateAvant : $dateDebut ;
                $tableauMois = $this->appService->genererTableauMois($dateGenere,$duree, $contrat->getDateLimite(),$contrat->getModePaiement()->getReference()) ;
                
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
                $nouveauTableau = empty($newChilds) ? $tableauMois : array_slice($tableauMois, 1);
                $response = $this->renderView("prestations/location/loyer/paiementMensuel.html.twig",[
                    // "item" => $item,
                    "tableauMois" => $nouveauTableau,
                    "elemExistant" => $elemExistant,
                    "repartitions" => $newChilds,
                    "lettreReleve" => $lettreReleve,
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
                $nouveauTableau = empty($newChilds) ? $tableauMois :  array_slice($tableauMois, 1);
                $response = $this->renderView("prestations/location/loyer/paiementJournaliere.html.twig",[
                    // "item" => $item,
                    "tableauMois" => $nouveauTableau,
                    "elemExistant" => $elemExistant,
                    "repartitions" => $newChilds,
                    "lettreReleve" => $lettreReleve,
                ]) ;
            }
        } 

        if($contrat->getForfait()->getReference() == "FORFAIT")
        {
            $response = $this->renderView("prestations/location/loyer/paiementForfaitaire.html.twig",[
                // "item" => $item,
                // "tableauMois" => $tableauMois,
                // "elemExistant" => $elemExistant,
                "repartitions" => $listeForfait,
                "lettreReleve" => $lettreReleve,
                "parentContrat" => $parent,
            ]) ;
        }

        return $this->render('prestations/location/detailsReleveContrat.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Relevé de paiement",
            "with_foot" => true,
            "contrat" => $parent,
            "template" => $response,
        ]);
    }

    #[Route('/prestation/location/loyer/liste', name: 'prest_location_liste_loyer')]
    public function prestListeLoyerLocation()
    {
        $paiements = $this->entityManager->getRepository(LctPaiement::class)->findBy([
            "agence" => $this->agence
            ]) ;
        
        return $this->render('prestations/location/listeLocationLoyer.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Relevé de loyer",
            "with_foot" => true,
            "paiements" => $paiements,
        ]);
    }

    #[Route('/prestation/location/loyer/details/{id}', name: 'prest_location_details_loyer')]
    public function prestDetailsLoyerLocation($id)
    {
        $paiement = $this->entityManager->getRepository(LctPaiement::class)->find($id) ;
        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        $parent = [
            "numReleve" => $paiement->getNumReleve(),
            "numContrat" => $paiement->getContrat()->getNumContrat(),
            "bailleur" => $paiement->getContrat()->getBailleur()->getNom(),
            "bail" => $paiement->getContrat()->getBail()->getNom(),
            "locataire" => $paiement->getContrat()->getLocataire()->getNom(),
            "lettre" => $this->appService->NumberToLetter($paiement->getMontant()),
            "lieu" => $paiement->getLieu(),
            "date" => $paiement->getDate()->format("d/m/Y"),
        ] ;

        $repartitions = $this->entityManager->getRepository(LctRepartition::class)->findBy([
            "paiement" => $paiement 
        ]) ;
        
        $childs = [] ;

        foreach ($repartitions as $repartition) {
            $item = [] ;

            $statutRepart = $repartition->getStatut()->getReference() ; 
            if( $statutRepart == "PAYE")
                $labelDsg = "Paiement" ;
            else if( $statutRepart == "ACOMPTE")
                $labelDsg = "Acompte" ;
            else
                $labelDsg = "" ;

            $refForfait =  $paiement->getContrat()->getForfait()->getReference() ;
            if($refForfait == "FJOUR")
                $moment = $repartition->getDateDebut()->format("d/m/Y") ;
            else 
                $moment = $tabMois[$repartition->getMois() - 1] ." ". $repartition->getAnnee() ;

            $item["designation"] = $labelDsg." ".$repartition->getDesignation() ;
            $item["moment"] = $moment ;
            $item["montant"] = $repartition->getMontant() ;

            array_push($childs,$item) ;
        }

        return $this->render('prestations/location/detailsLocationLoyer.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Relevé de paiement ",
            "with_foot" => true,
            "repartitions" => $childs,
            "paiement" => $parent,
        ]);
    }

    
    #[Route('/prestation/location/caution/save', name: 'prest_save_caution_location')]
    public function prestSaveCautionLocation(Request $request)
    {
        $idContrat = $request->request->get("contrat") ;
        $montantCtn = $request->request->get("montantCtn") ;
        
        $lastRecordPaiement = $this->entityManager->getRepository(LctPaiement::class)->findOneBy([], ['id' => 'DESC']);
        $numPaiement = !is_null($lastRecordPaiement) ? ($lastRecordPaiement->getId()+1) : 1 ;
        $numPaiement = str_pad($numPaiement, 4, "0", STR_PAD_LEFT)."/".date('y');
        
        $contrat = $this->entityManager->getRepository(LctContrat::class)->find($idContrat) ;
        
        $paiement = new LctPaiement() ;
        
        $paiement->setAgence($this->agence) ;
        $paiement->setContrat($contrat) ;
        $paiement->setDate(\DateTime::createFromFormat('j/m/Y',date('d/m/Y'))) ;
        $paiement->setMontant(floatval($montantCtn)) ;
        $paiement->setNumReleve($numPaiement) ;
        $paiement->setIndication("CAUTION") ;
        $paiement->setDescription("Paiement Caution à la création du contrat le ".date('d/m/Y')) ;

        $this->entityManager->persist($paiement) ;
        $this->entityManager->flush() ; 

        $statutLoyer = $this->entityManager->getRepository(LctStatutLoyer::class)->findOneBy([
            "reference" => "CAUTION"
        ]) ;

        $repartition = new LctRepartition() ;

        $repartition->setPaiement($paiement) ;
        $repartition->setMois(intval(date('m'))) ;
        $repartition->setAnnee(date('Y')) ;
        $repartition->setMontant(floatval($montantCtn)) ;
        $repartition->setDateDebut(NULL) ;
        $repartition->setDateLimite(NULL) ;
        $repartition->setDesignation("PAIEMENT CAUTION ".$contrat->getBail()->getNom()) ;
        $repartition->setStatut($statutLoyer) ;
        $repartition->setCreatedAt(new \DateTimeImmutable) ;
        $repartition->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($repartition) ;
        $this->entityManager->flush() ;

        return new JsonResponse([
            "type" => "green",
            "message" => "La caution a été enregistré"
        ]) ;
    }
}
