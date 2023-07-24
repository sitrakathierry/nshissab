<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AchatController extends AbstractController
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
        $this->filename = "files/systeme/achat/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"] 
        ]) ;
    }

    #[Route('/achat/bon/commande/creation', name: 'compta_achat_bon_commande_creation')]
    public function achatsCreationBondeCommande()
    {
        return $this->render('achat/creationBonDeCommande.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Création bon de commande (achat)",
            "with_foot" => true,
        ]);
    }

    #[Route('/achat/bon/commande/liste', name: 'compta_achat_bon_commande_liste')]
    public function achatsListeBondeCommande()
    {
        return $this->render('achat/listeBonDeCommande.html.twig', [
            "filename" => "achat",
            "titlePage" => "Consultation bon de commande (achat)",
            "with_foot" => false,
        ]);
    }

    #[Route('/achat/marchandise/operation', name: 'achat_marchandise_operation')]
    public function achatOperationMarchandise()
    {
        return $this->render('achat/marchandise/operation.html.twig', [
            "filename" => "achat",
            "titlePage" => "Marchandise",
            "with_foot" => false,
        ]);
    }

    #[Route('/achat/credit/operation', name: 'achat_credit_operation')]
    public function achatOperationCredit()
    {
        return $this->render('achat/credit/operation.html.twig', [
            "filename" => "achat",
            "titlePage" => "Credit Achat",
            "with_foot" => false,
        ]);
    }

}
