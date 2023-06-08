<?php

namespace App\Controller;

use App\Entity\AgdTypes;
use App\Entity\Agence;
use App\Entity\Agenda;
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

class AgendaController extends AbstractController
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
        $this->filename = "files/systeme/agenda/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"] 
        ]) ;
    }
    
    #[Route('/agenda/creation', name: 'agd_agenda_creation')]
    public function agdCreationAgenda(): Response
    {
        $agdTypes = $this->entityManager->getRepository(AgdTypes::class)->findAll() ;


        return $this->render('agenda/creation.html.twig', [
            "filename" => "agenda",
            "titlePage" => "Creation agenda",
            "with_foot" => true,
            "agdTypes" => $agdTypes
        ]);
    }

    #[Route('/agenda/consultation', name: 'agd_agenda_consultation')]
    public function agdConsultationAgenda(): Response
    {
        $filename = $this->filename."agenda(agence)/".$this->nameAgence ;
        if (!file_exists($filename)) {
            $this->appService->generateAgenda($filename, $this->agence) ;
        }
        // $agendas = json_decode(file_get_contents($filename)) ;

        return $this->render('agenda/consultation.html.twig', [
            "filename" => "agenda",
            "titlePage" => "Consultation agenda",
            "with_foot" => false,
            "calendarFile" => $filename
        ]);
    }

    #[Route('/agenda/activite/save', name: 'agd_activites_save')]
    public function agdSaveActivite(Request $request)
    {
        // dd($request->request) ;

        $agd_type = $request->request->get("agd_type") ;
        $agd_client = $request->request->get("agd_client") ;
        $agd_date = $request->request->get("agd_date") ;
        $agd_heure = $request->request->get("agd_heure") ;
        $agd_lieu = $request->request->get("agd_lieu") ;
        $agenda_editor = $request->request->get("agenda_editor") ;
        $agd_nom = $request->request->get("agd_nom") ;

        $data = [
            $agd_type,
            $agd_client,
            $agd_date,
            $agd_heure,
            $agd_lieu,
            ] ;

        $dataMessage = [
            "Type Agenda",
            $agd_nom,
            "Date",
            "Heure",
            "Lieu",
            ] ;

        $result = $this->appService->verificationElement($data, $dataMessage) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $type = $this->entityManager->getRepository(AgdTypes::class)->find($agd_type) ;

        $agenda = new Agenda() ;

        $agenda->setAgence($this->agence) ;
        $agenda->setClientNom($agd_client) ;
        $agenda->setDate(\DateTime::createFromFormat('j/m/Y',$agd_date)) ;
        $agenda->setHeure($agd_heure) ;
        $agenda->setLieu($agd_lieu) ; 
        $agenda->setType($type) ;
        $agenda->setDescription($agenda_editor) ;
        $agenda->setStatut(True) ;
        $agenda->setCreatedAt(new \DateTimeImmutable) ;
        $agenda->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($agenda) ;
        $this->entityManager->flush() ;
        
        $filename = $this->filename."agenda(agence)/".$this->nameAgence ;
        if(file_exists($filename))
        {
            unlink($filename) ;
        }

        return new JsonResponse($result) ;
    }

    #[Route('/agenda/activite/date/details', name: 'agd_activites_details_date')]
    public function agdDetailsDate(Request $request)
    {
        $date = $request->request->get('date') ;
        $agendas = $this->entityManager->getRepository(Agenda::class)->findBy([
            "date" => \DateTime::createFromFormat('Y-m-d', $date)
            ]) ;
        
        $response = $this->renderView("agenda/detailsDateAganda.html.twig", [
            "agendas" => $agendas
        ]) ;

        return new Response($response) ;
    }

    #[Route('/agenda/detail/{id}', name: 'agd_detail_agenda')]
    public function agdDetailsAgenda($id)
    {
        $agenda = $this->entityManager->getRepository(Agenda::class)->find($id) ;
        
        return $this->render('agenda/detailAgenda.html.twig', [
            "filename" => "agenda",
            "titlePage" => "Detail ",
            "with_foot" => false,
            "agenda" => $agenda
        ]);
    }
}
