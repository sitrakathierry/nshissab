<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
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
    private $nomAgence ;
    public function __construct(EntityManagerInterface $entityManager,SessionInterface $session, AppService $appService)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->appService = $appService ;
        $this->appService->checkUrl() ;
        $this->user = $this->session->get("user") ;
        $this->agence = $this->entityManager->getRepository(Agence::class)->find($this->user["agence"]) ; 
        $this->filename = "files/systeme/caisse/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"] 
        ]) ;
        $this->nomAgence = strtoupper($this->agence->getNom()) ;
    }
    #[Route('/client/creation', name: 'clt_creation')]
    public function clientCreation(): Response
    {
        $nomAgence = $this->nomAgence ; 
        return $this->render('client/creation.html.twig', [
            "filename" => "client",
            "titlePage" => "Création Client",
            "with_foot" => true,
            "nomAgence" => $nomAgence 
        ]);
    }

    #[Route('/client/consultation', name: 'clt_consultation')]
    public function clientConsultation(): Response
    {
        return $this->render('client/consultation.html.twig', [
            "filename" => "client",
            "titlePage" => "Liste des Clients",
            "with_foot" => false
        ]);
    }
}
