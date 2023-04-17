<?php

namespace App\Controller;

use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SocieteController extends AbstractController
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
     
    #[Route('/societe/details', name: 'soc_details')]
    public function societeDetails(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl["response"])
        {
            $url = $this->generateUrl($allowUrl["route"]);
            return new RedirectResponse($url);
        } 
        
        return $this->render('societe/details.html.twig', [
            "filename" => "societe",
            "titlePage" => "Détails Société",
            "with_foot" => true
        ]);
    }
}
