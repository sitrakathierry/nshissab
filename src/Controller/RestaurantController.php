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

class RestaurantController extends AbstractController
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
        $this->filename = "files/systeme/restaurant/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"],
            "agence" => $this->agence
        ]) ;
    }


    #[Route('/restaurant/ingredient/appro', name: 'restaurant_ingredient_appro')]
    public function restoApproIngredient(): Response
    {
        return $this->render('restaurant/ingredients/approvisionnement.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Approvisionnement ingrédient", 
            "with_foot" => true,
        ]);
    }

    #[Route('/restaurant/ingredient/unite', name: 'restaurant_ingredient_unite')]
    public function restoUniteIngredient(): Response
    {
        return $this->render('restaurant/ingredients/uniteIngredient.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Unité", 
            "with_foot" => true,
        ]);
    }

    #[Route('/restaurant/ingredient/consultation', name: 'restaurant_ingredient_consultation')]
    public function restoConsultationIngredient(): Response
    {
        return $this->render('restaurant/ingredients/consultation.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Consultation Ingrédient", 
            "with_foot" => false,
        ]);
    }

    #[Route('/restaurant/ingredient/mouvement', name: 'restaurant_ingredient_mouvement')]
    public function restoMouvementIngredient(): Response
    {
        return $this->render('restaurant/ingredients/mouvement.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Mouvement (Entrée - Sortie)", 
            "with_foot" => false,
        ]);
    }

    #[Route('/restaurant/ingredient/sortir', name: 'restaurant_ingredient_sortir')]
    public function restoSortirIngredient(): Response
    {
        return $this->render('restaurant/ingredients/sortirIngredient.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Sortir Ingrédient", 
            "with_foot" => true,
        ]);
    }

    #[Route('/restaurant/table/creation', name: 'restaurant_table_creation')]
    public function restoCreationTable(): Response
    {
        return $this->render('restaurant/table/creation.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Nouvelle table", 
            "with_foot" => true,
        ]);
    }

    #[Route('/restaurant/table/suivi', name: 'restaurant_table_suivi')]
    public function restoSuiviTable(): Response
    {
        return $this->render('restaurant/table/suiviTable.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Nouvelle table", 
            "with_foot" => true,
        ]);
    }

    #[Route('/restaurant/table/reservation', name: 'restaurant_table_reservation')]
    public function restoReservationTable(): Response
    {
        return $this->render('restaurant/table/reservationTable.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Nouvelle table", 
            "with_foot" => true,
        ]);
    }

    #[Route('/restaurant/repas/categorie', name: 'restaurant_repas_categorie')]
    public function restoCategorieRepas(): Response
    {
        return $this->render('restaurant/repas/categorieRepas.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Catégorie Plats / Accompagnements", 
            "with_foot" => true,
        ]);
    }

    #[Route('/restaurant/repas/creation', name: 'restaurant_repas_creation')]
    public function restoCreationRepas(): Response
    {
        return $this->render('restaurant/repas/creation.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Création Plats / Accompagnements", 
            "with_foot" => true,
        ]);
    }

    #[Route('/restaurant/repas/consultation', name: 'restaurant_repas_consultation')]
    public function restoConsultationRepas(): Response
    {
        return $this->render('restaurant/repas/consultation.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Création Plats / Accompagnements", 
            "with_foot" => true,
        ]);
    }

    #[Route('/restaurant/boissons/categorie', name: 'restaurant_boissons_categorie')]
    public function restoCategorieBoissons(): Response
    {
        return $this->render('restaurant/boissons/categorieBoissons.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Catégorie Plats / Accompagnements", 
            "with_foot" => true,
        ]);
    }

    #[Route('/restaurant/boissons/creation', name: 'restaurant_boissons_creation')]
    public function restoCreationBoissons(): Response
    {
        return $this->render('restaurant/boissons/creation.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Création Plats / Accompagnements", 
            "with_foot" => true,
        ]);
    }

    #[Route('/restaurant/boissons/consultation', name: 'restaurant_boissons_consultation')]
    public function restoConsultationBoissons(): Response
    {
        return $this->render('restaurant/boissons/consultation.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Création Plats / Accompagnements", 
            "with_foot" => true,
        ]);
    }

    #[Route('/restaurant/menus/creation', name: 'restaurant_menus_creation')]
    public function restoCreationMenus(): Response
    {
        return $this->render('restaurant/menus/creation.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Création menus", 
            "with_foot" => true,
        ]);
    }

    #[Route('/restaurant/menus/consultation', name: 'restaurant_menus_consultation')]
    public function restoConsultationMenus(): Response
    {
        return $this->render('restaurant/menus/consultation.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Consultation menus", 
            "with_foot" => true,
        ]);
    }

    #[Route('/restaurant/caisse', name: 'restaurant_caisse')]
    public function restoCaisseMenus(): Response
    {
        return $this->render('restaurant/caisse/caisseRestaurant.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Caisse", 
            "with_foot" => true,
        ]);
    }

    #[Route('/restaurant/commandes/surPlace', name: 'restaurant_commandes_sur_place')]
    public function restoCommandesSurPlace(): Response
    {
        return $this->render('restaurant/commandes/commandesSurPlace.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Commandes Sur Place", 
            "with_foot" => false,
        ]);
    }

    #[Route('/restaurant/commandes/emporter', name: 'restaurant_commandes_emporter')]
    public function restoCommandesaEmporter(): Response
    {
        return $this->render('restaurant/commandes/commandesEmporter.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Commandes A Emporter", 
            "with_foot" => false,
        ]);
    }

    #[Route('/restaurant/commandes/livrer', name: 'restaurant_commandes_livrer')]
    public function restoCommandesLivrer(): Response
    {
        return $this->render('restaurant/commandes/commandesLivrer.html.twig', [
            "filename" => "restaurant",
            "titlePage" => "Commandes A Livrer", 
            "with_foot" => false,
        ]);
    }
}
