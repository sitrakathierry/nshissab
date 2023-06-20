<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\BtpCategorie;
use App\Entity\BtpElement;
use App\Entity\BtpEnoncee;
use App\Entity\BtpMesure;
use App\Entity\BtpPrix;
use App\Entity\Service;
use App\Entity\SrvDuree;
use App\Entity\SrvFormat;
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
        $durees = $this->entityManager->getRepository(SrvDuree::class)->findAll() ;

        return $this->render('prestations/detailsService.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Detail Service",
            "with_foot" => true,
            "service" => $service,
            "formats" => $formats,
            "durees" => $durees
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

    #[Route('/prestation/location/creation', name: 'prest_location_creation')]
    public function prestCreationLocation(): Response
    {
        return $this->render('prestations/location/creation.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Création prestation location",
            "with_foot" => true,
        ]);
    }

    #[Route('/prestation/location/consultation', name: 'prest_location_consultation')]
    public function prestConsultationLocation(): Response
    {

        return $this->render('prestations/location/consultation.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Consultation prestation location",
            "with_foot" => false
        ]);
    }


}
