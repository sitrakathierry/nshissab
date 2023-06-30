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
use App\Entity\LctCycle;
use App\Entity\LctPeriode;
use App\Entity\LctRenouvellement;
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
        $prest_lct_bail_montant = $request->request->get('prest_lct_bail_montant') ;
        $prest_lct_bail_caution = $request->request->get('prest_lct_bail_caution') ;
        $prest_lct_bail_lieu = $request->request->get('prest_lct_bail_lieu') ;
        
        $result = $this->appService->verificationElement([
            $prest_lct_bail_nom,
            $prest_lct_bail_lieu,
            $prest_lct_bail_dimension,
            $prest_lct_bail_montant,
            $prest_lct_bail_caution,
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
        $bail->setMontant(floatval($prest_lct_bail_montant)) ;
        $bail->setCaution(floatval($prest_lct_bail_caution)) ;
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

        return $this->render('prestations/location/contrat.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Nouveau contrat",
            "with_foot" => true,
            "bailleurs" => $bailleurs,
            "type_locs" => $type_locs,
            "periodes" => $periodes,
            "renouvs" => $renouvs,
            "cycles" => $cycles,
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
    
    #[Route('/prestation/location/bailleur/new', name: 'prest_new_location_bailleur')]
    public function prestNewLocationBailleur(){
        $response = $this->renderView("prestations/location/getNewBailleur.html.twig") ;
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
        
        return new JsonResponse([
            "type" => "orange",
            "message" => "Mise à jours en cours de finition",
            ]) ;
    }
}
