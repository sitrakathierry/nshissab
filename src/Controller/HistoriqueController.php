<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\HistoHistorique;
use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class HistoriqueController extends AbstractController
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
        $this->filename = "files/systeme/credit/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"],
            "agence" => $this->agence  
        ]) ;
    }

    #[Route('/historique/consultation', name: 'histo_element_consultation')]
    public function index(): Response
    {
        $filename = "files/systeme/historique/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->entityManager->getRepository(HistoHistorique::class)->genererHistorique($this->agence,$filename) ;
        
        $historiques = json_decode(file_get_contents($filename)) ;

        return $this->render('historique/consultation.html.twig', [
            "filename" => "historique",
            "titlePage" => "Historique des actions",
            "with_foot" => false,
            "historiques" => $historiques,
        ]);
    }
}
