<?php

namespace App\Controller;

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
    public function __construct(EntityManagerInterface $entityManager,SessionInterface $session, AppService $appService)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->appService = $appService ;
    }

    #[Route('/client/creation', name: 'clt_creation')]
    public function clientCreation(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 
 
        return $this->render('client/creation.html.twig', [
            "filename" => "client",
            "titlePage" => "Création Client",
            "with_foot" => true
        ]);
    }

    #[Route('/client/consultation', name: 'clt_consultation')]
    public function clientConsultation(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 
        
        return $this->render('client/consultation.html.twig', [
            "filename" => "client",
            "titlePage" => "Liste des Clients",
            "with_foot" => false
        ]);
    }
}
