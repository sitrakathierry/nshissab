<?php

namespace App\Controller;

use App\Entity\AgcCivilite;
use App\Entity\AgcSexe;
use App\Entity\Agence;
use App\Entity\CoiffCategorie;
use App\Entity\CoiffCoupes;
use App\Entity\CoiffCpPrix;
use App\Entity\CoiffEmployee;
use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CoiffureController extends AbstractController
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
        $this->filename = "files/systeme/coiffure/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"],
            "agence" => $this->agence 
        ]) ;
        $this->nomAgence = strtoupper($this->agence->getNom()) ;
    }
    

    #[Route('/coiffure/categorie/coupes', name: 'coiffure_categorie_coupes')]
    public function coiffureCategorie()
    {
        $categories = $this->entityManager->getRepository(CoiffCategorie::class)->findBy([
            "agence" => $this->agence,
            "statut" => True
        ]) ;

        return $this->render('coiffure/categorieCoupes.html.twig', [
            "filename" => "coiffure",
            "titlePage" => "Sous Catégorie",
            "catCoupes" => $categories,
            "with_foot" => false,
        ]); 
    }

    #[Route('/coiffure/coupes/cheuveux', name: 'coiffure_coupes_cheuveux')]
    public function coiffureCoupesCheuveux()
    {
        $categories = $this->entityManager->getRepository(CoiffCategorie::class)->findBy([
            "agence" => $this->agence,
            "statut" => True
        ]) ;

        $cpPrixs = $this->entityManager->getRepository(CoiffCpPrix::class)->generatePrixCoupes([
            "agence" => $this->agence,
            "filename" => $this->filename."prixCoupes(agence)/".$this->nameAgence
        ]) ;
 
        return $this->render('coiffure/coupesCheuveux.html.twig', [
            "filename" => "coiffure",
            "titlePage" => "Soins de beauté",
            "with_foot" => false,
            "categories" => $categories,
            "cpPrixs" => $cpPrixs,
        ]);
    }

    #[Route('/coiffure/coupes/cheuveux/save', name: 'coiffure_coupes_cheuveux_save')]
    public function coiffureSaveCoupesCheuveux(Request $request)
    {
        $coiff_sous_categorie = $request->request->get("coiff_sous_categorie") ;
        $coiff_coupes_nom = $request->request->get("coiff_coupes_nom") ;
        $coiff_coupes_prix = $request->request->get("coiff_coupes_prix") ;
        $mod_image_origine = $request->request->get("mod_image_origine") ;

        $result = $this->appService->verificationElement([
            $mod_image_origine,
            $coiff_sous_categorie,
            $coiff_coupes_nom,
            $coiff_coupes_prix,
        ], [
            "Image",
            "Sous Catégorie",
            "Désignation",
            "Prix",
        ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $categorie = $this->entityManager->getRepository(CoiffCategorie::class)->find($coiff_sous_categorie) ;

        $coupe = new CoiffCoupes() ;

        $coupe->setAgence($this->agence) ;
        $coupe->setCategorie($categorie) ;
        $coupe->setNom($coiff_coupes_nom) ;
        $coupe->setPhoto($mod_image_origine) ;
        $coupe->setStatut(True) ;
        
        $this->entityManager->persist($coupe) ;
        $this->entityManager->flush() ;

        $cpPrix = new CoiffCpPrix() ;

        $cpPrix->setAgence($this->agence) ;
        $cpPrix->setCoupes($coupe) ;
        $cpPrix->setMontant(floatval($coiff_coupes_prix)) ;
        $cpPrix->setStatut(True) ;
        $cpPrix->setCreatedAt(new \DateTimeImmutable) ;
        $cpPrix->setUpdatedAt(new \DateTimeImmutable) ;
        
        $this->entityManager->persist($cpPrix) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."prixCoupes(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse($result) ;
    }

    #[Route('/coiffure/employee', name: 'coiffure_employee')]
    public function coiffureInformationEmployee()
    {

        $sexes = $this->entityManager->getRepository(AgcSexe::class)->findAll() ;

        $employees = $this->entityManager->getRepository(CoiffEmployee::class)->generateCoiffEmployee([
            "agence" => $this->agence,
            "filename" => $this->filename."employee(agence)/".$this->nameAgence 
        ]) ;

        return $this->render('coiffure/empoloyee.html.twig', [
            "filename" => "coiffure",
            "titlePage" => "Employée Coiffure",
            "with_foot" => false,
            "sexes" => $sexes,
            "employees" => $employees,
        ]);
    }

    #[Route('/coiffure/employee/save', name: 'coiffure_employee_save')]
    public function coiffureSaveInformationEmployee(Request $request)
    {
        $coiff_emp_sexe = $request->request->get("coiff_emp_sexe") ;
        $coiff_emp_nom = $request->request->get("coiff_emp_nom") ;
        $coiff_emp_prenom = $request->request->get("coiff_emp_prenom") ;

        $result = $this->appService->verificationElement([
            $coiff_emp_sexe,
            $coiff_emp_nom,
            $coiff_emp_prenom,
        ], [
            "Sexe",
            "Nom",
            "Prénom(s)",
        ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $sexe = $this->entityManager->getRepository(AgcSexe::class)->find($coiff_emp_sexe) ;

        $employee = new CoiffEmployee() ;

        $employee->setAgence($this->agence) ;
        $employee->setSexe($sexe) ;
        $employee->setNom($coiff_emp_nom) ;
        $employee->setPrenom($coiff_emp_prenom) ;
        $employee->setStatut(True) ;
        $employee->setCreatedAt(new \DateTimeImmutable) ;
        $employee->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($employee) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."employee(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse($result) ;
    }

    #[Route('/coiffure/categorie/coupes/save', name: 'coiffure_categorie_coupes_save')]
    public function coiffureSaveCategorie(Request $request)
    {
        $coiff_categorie = $request->request->get("coiff_categorie") ;
        $coiff_sous_categorie = $request->request->get("coiff_sous_categorie") ;

        $result = $this->appService->verificationElement([
            $coiff_categorie,
            $coiff_sous_categorie
        ], [
            "Catégorie",
            "Sous Catégorie"
        ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $categorie = new CoiffCategorie() ;

        $categorie->setAgence($this->agence) ;
        $categorie->setNom($coiff_sous_categorie) ;
        $categorie->setGenre($coiff_categorie) ;
        $categorie->setStatut(True) ;
        
        $this->entityManager->persist($categorie) ;
        $this->entityManager->flush() ;

        return new JsonResponse($result) ;
    }

    #[Route('/coiffure/coupes/item/prix/search', name: 'coiff_coupes_item_prix_search')]
    public function coiffureSearchCoupesItemPrix(Request $request)
    {
        $idPrix = $request->request->get("idPrix");
        $idGenre = $request->request->get("idGenre");

        $cpPrixs = $this->entityManager->getRepository(CoiffCpPrix::class)->generatePrixCoupes([
            "agence" => $this->agence,
            "filename" => $this->filename."prixCoupes(agence)/".$this->nameAgence
        ]) ;

        $search = [
            "id" => $idPrix,
            "genre" => $idGenre,
        ] ;

        $cpPrixs = $this->appService->searchData($cpPrixs,$search) ;

        $cpPrixs = array_values($cpPrixs) ;

        return new JsonResponse($cpPrixs) ;
    }
}
