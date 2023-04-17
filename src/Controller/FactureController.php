<?php

namespace App\Controller;

use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class FactureController extends AbstractController
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
    
    #[Route('/facture/creation', name: 'ftr_creation')]
    public function factureCreation(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 
        
        return $this->render('facture/creation.html.twig', [
            "filename" => "facture",
            "titlePage" => "Création Facture",
            "with_foot" => true
        ]);
    }

    #[Route('/facture/consultation', name: 'ftr_consultation')]
    public function factureConsultation(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 
        
        return $this->render('facture/consultation.html.twig', [
            "filename" => "facture",
            "titlePage" => "Consultation Facture",
            "with_foot" => false
        ]);
    }
}
