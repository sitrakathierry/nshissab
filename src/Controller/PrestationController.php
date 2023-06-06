<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\Service;
use App\Entity\SrvDuree;
use App\Entity\SrvFormat;
use App\Entity\User;
use App\Service\AppService;
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
        return $this->render('prestations/batiment/creation.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Création prestation Batiment",
            "with_foot" => true,
        ]);
    }

    #[Route('/prestation/batiment/consultation', name: 'prest_batiment_consultation')]
    public function prestConsultationBatiment(): Response
    {

        return $this->render('prestations/batiment/consultation.html.twig', [
            "filename" => "prestations",
            "titlePage" => "Consultation prestation batiment",
            "with_foot" => false
        ]);
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
