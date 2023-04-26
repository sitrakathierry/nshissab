<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\PrdCategories;
use App\Entity\PrdEntrepot;
use App\Entity\PrdPreferences;
use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AbstractController
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
        $this->filename = "files/systeme/stock/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"] 
        ]) ;
    }
    
    #[Route('/stock/creationproduit', name: 'stock_creationproduit')]
    public function stockCreationproduit(): Response
    {
        return $this->render('stock/creationproduit.html.twig', [
            "filename" => "stock",
            "titlePage" => "Création Produit",
            "with_foot" => true
        ]);
    }

    #[Route('/stock/general', name: 'stock_general')]
    public function stockGeneral(): Response
    {
        

        return $this->render('stock/stockgeneral.html.twig', [
            "filename" => "stock",
            "titlePage" => "Stock Général",
            "with_foot" => false
        ]);
    }

    #[Route('/stock/categorie/creation/{id}', name: 'stock_cat_creation', defaults:['id' => null])]
    public function stockCatCreation($id): Response
    {
        
        $categorie = [] ;
        if(!is_null($id))
        {
            $user = $this->session->get("user") ;
            $agence = $this->entityManager->getRepository(Agence::class)->find($user["agence"]) ; 

            $filename = "files/systeme/stock/categorie(agence)/".strtolower($agence->getNom())."-".$agence->getId().".json" ;
            if(!file_exists($filename))
                $this->appService->generateStockCategorie($filename, $agence) ;
            $categories = json_decode(file_get_contents($filename)) ;
            foreach ($categories as $cat) {
                if($cat->id == $id)
                {
                    $categorie = $cat ;
                    break ;
                }
            }
            
        }

        return $this->render('stock/categorie/creation.html.twig', [
            "filename" => "stock",
            "titlePage" => "Création Catégorie",
            "with_foot" => true,
            "categorie" => $categorie
        ]);
    }

    #[Route('/stock/categorie/save',name: 'stock_save_categorie')]
    public function stockCategorieSave(Request $request)
    {
        $image = $request->request->get('image') ;
        $nom = $request->request->get('nom') ;
        $id = $request->request->get('id') ;

        $user = $this->session->get("user") ;
        $agence = $this->entityManager->getRepository(Agence::class)->find($user["agence"]) ; 

        $base64Image = explode(";base64,",$image) ;
        if(!isset($base64Image[1]))
        {
            $image = "" ;
        }

        $reponse = [] ;
        if(empty($id))
        {
            $categorie = new PrdCategories() ;
            $reponse["message"] = "Catégorie ajoutée" ;
            $reponse["type"] = "green" ;
        }
        else
        {
            $categorie = $this->entityManager->getRepository(PrdCategories::class)->find($id) ; 
            $reponse["message"] = "Mise à jour terminée" ;
            $reponse["type"] = "dark" ;
        }

        $categorie->setAgence($agence) ;
        $categorie->setImages($image) ;
        $categorie->setNom($nom) ;
        $categorie->setCreatedAt(new \DateTimeImmutable) ;
        $categorie->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($categorie);
        $this->entityManager->flush();

        $filename = "files/systeme/stock/categorie(agence)/".strtolower($agence->getNom())."-".$agence->getId().".json" ;
        $this->appService->generateStockCategorie($filename, $agence) ;

        return new JsonResponse($reponse) ;
    }

    #[Route('/stock/categorie/delete',name:'stock_delete_categorie')]
    public function stockCategorieDelete(Request $request)
    {
        $id = $request->request->get("id") ;
        $categorie = $this->entityManager->getRepository(PrdCategories::class)->find($id) ; 

        $this->entityManager->remove($categorie);
        $this->entityManager->flush();

        $user = $this->session->get("user") ;
        $agence = $this->entityManager->getRepository(Agence::class)->find($user["agence"]) ; 

        $filename = "files/systeme/stock/categorie(agence)/".strtolower($agence->getNom())."-".$agence->getId().".json" ;
        $this->appService->generateStockCategorie($filename, $agence) ;

        return new JsonResponse(["message" => "Catégorie supprimée", "type" => "green"]) ;
    }   

    #[Route('/stock/categorie/consultation/{nom}', name: 'stock_cat_consultation', defaults : ["nom" => null])]
    public function stockCatConsultation($nom): Response
    {
        $user = $this->session->get("user") ;
        $agence = $this->entityManager->getRepository(Agence::class)->find($user["agence"]) ; 

        $filename = "files/systeme/stock/categorie(agence)/".strtolower($agence->getNom())."-".$agence->getId().".json" ;
        if(!file_exists($filename))
        {   
            $this->appService->generateStockCategorie($filename, $agence) ;
        }
        $categories = json_decode(file_get_contents($filename)) ;

        if(!is_null($nom))
        {
            $responses = $this->appService->searchData($categories,[$nom]) ;
            $categories = $responses ;
        }
        return $this->render('stock/categorie/consultation.html.twig', [
            "filename" => "stock",
            "titlePage" => "Liste des Catégories de Produit",
            "with_foot" => false,
            "categories" => $categories
        ]);
    }

    #[Route('/stock/preferences', name: 'stock_preferences')]
    public function stockPreferences(): Response
    {
        $filename = $this->filename."categorie(agence)/".$this->nameAgence ;
        $categories = json_decode(file_get_contents($filename)) ;

        $filename = $this->filename."preference(user)/".$this->nameUser.".json" ;
        if(!file_exists($filename))
            $this->appService->generateStockPreferences($filename,$this->userObj) ;

        $elements = [] ;
        $dataPreferences = json_decode(file_get_contents($filename)) ;

        foreach ($categories as $cat) {
            $exist = False;
            for ($i=0; $i < count($dataPreferences); $i++) { 
                if($cat->id == $dataPreferences[$i]->categorie)
                {
                    $exist = True ;
                    break ;
                }
            }
            if(!$exist)
            {
                array_push($elements,$cat) ;
            }
        }

        $categories = $elements ;

        return $this->render('stock/preferences.html.twig', [
            "filename" => "stock",
            "titlePage" => "Préférences",
            "with_foot" => false,
            "categories" => $categories,
            "preferences" => $dataPreferences
        ]);
    }

    #[Route('/stock/preferences/save', name: 'stock_save_prefs')]
    public function stockSavePrefs(Request $request)
    {
        $preferences = (array)$request->request->get('preferences') ;
        $preferences = explode(",",$preferences[0]) ;

        foreach ($preferences as $key => $value) {
            $preference = new PrdPreferences() ;
            $categorie = $this->entityManager->getRepository(PrdCategories::class)->find($value) ;

            $preference->setCategorie($categorie) ;
            $preference->setUser($this->userObj) ;
            $preference->setStatut(True) ;
            $preference->setCreatedAt(new \DateTimeImmutable) ;
            $preference->setUpdatedAt(new \DateTimeImmutable) ;

            $this->entityManager->persist($preference) ;
            $this->entityManager->flush() ;
        }

        $filename = $this->filename."preference(user)/".$this->nameUser.".json" ;
        $this->appService->generateStockPreferences($filename,$this->userObj) ;

        $dataPreferences = json_decode(file_get_contents($filename)) ;

        $result = $this->renderView("stock/searchPreferences.html.twig",[
            "preferences" => $dataPreferences
        ]) ;

        return new Response($result) ;
    }
    
    #[Route('/stock/preferences/delete', name: 'stock_delete_prefs')]
    public function stockDeletePrefs(Request $request)
    {
        $id = $request->request->get('id') ;

        $preference = $this->entityManager->getRepository(PrdPreferences::class)->find($id) ;

        $this->entityManager->remove($preference) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."preference(user)/".$this->nameUser.".json" ;
        $this->appService->generateStockPreferences($filename,$this->userObj) ;

        return new JsonResponse([
            "message" => "Suppression effectuée",
            "type" => "green"
        ]) ;
    }
    
    
    #[Route('/stock/inventaire', name: 'stock_inventaire')]
    public function stockInventaire(): Response
    {
        

        return $this->render('stock/inventaire.html.twig', [
            "filename" => "stock",
            "titlePage" => "Inventaire des Produits",
            "with_foot" => false
        ]);
    }

    #[Route('/stock/entrepot', name: 'stock_entrepot')]
    public function stockEntrepot(): Response
    {
        
        $filename = $this->filename."entrepot(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
        {   
            $this->appService->generateStockEntrepot($filename,$this->agence) ;
        }
        $entrepots = json_decode(file_get_contents($filename)) ;

        return $this->render('stock/entrepot.html.twig', [
            "filename" => "stock",
            "titlePage" => "Entrepot",
            "with_foot" => true,
            "entrepots" => $entrepots
        ]);
    }

    #[Route('/stock/entrepot/save', name: 'stock_save_entrepot')]
    public function stockSaveEntrepot(Request $request)
    {
        $nom = $request->request->get("nom") ;
        $adresse = $request->request->get("adresse") ;
        $telephone = $request->request->get("telephone") ;

        $data = [
            $nom,
            $adresse,
            $telephone,
        ] ;

        $dataMessage = [
            "Nom",
            "Adresse",
            "Téléphone",
        ] ;

        $result = $this->appService->verificationElement($data,$dataMessage) ;
        if(!$result["allow"])
            return new JsonResponse($result) ;

        $id = $request->request->get('id') ;

        if(!isset($id))
        {
            $entrepot = new PrdEntrepot() ;
            $entrepot->setCreatedAt(new \DateTimeImmutable) ;
        }
        else
        {
            $entrepot = $this->entityManager->getRepository(PrdEntrepot::class)->find($id) ;
        }

        $entrepot->setNom($nom) ;
        $entrepot->setAdresse($adresse) ;
        $entrepot->setTelephone($telephone) ;
        $entrepot->setAgence($this->agence) ;
        $entrepot->setUpdatedAt(new \DateTimeImmutable) ;
        
        $this->entityManager->persist($entrepot);
        $this->entityManager->flush();
        
        $this->appService->generateStockEntrepot($this->filename."entrepot(agence)/".$this->nameAgence,$this->agence) ;

        return new JsonResponse($result) ;
    }
    
    #[Route('/stock/entrepot/edit', name: 'stock_edit_entrepot')]
    public function stockEditEntrepot(Request $request)
    {
        $id = $request->request->get('id') ;
        $filename = $this->filename."entrepot(agence)/".$this->nameAgence ;
        $entrepots = json_decode(file_get_contents($filename)) ;
        $result = [] ;
        foreach ($entrepots as $entrepot) {
            if($entrepot->id == $id)
            {
                $result = $entrepot ;
                break ;
            }
        } 

        return new JsonResponse($result) ;
    }

    #[Route('/stock/entrepot/delete', name: 'stock_delete_entrepot')]
    public function stockDeleteEntrepot(Request $request)
    {
        $id = $request->request->get('id') ;

        $entrepot = $this->entityManager->getRepository(PrdEntrepot::class)->find($id) ;

        $this->entityManager->remove($entrepot);
        $this->entityManager->flush();

        $this->appService->generateStockEntrepot($this->filename."entrepot(agence)/".$this->nameAgence,$this->agence) ;
        
        return new JsonResponse([
            "message" => "Suppression effectuée",
            "type" => "green"
        ]) ;
    }

    #[Route('/stock/entrepot/search', name:'stock_search_entrepot')]
    public function stockSearchEntrepot(Request $request)
    {
        $nom = $request->request->get('nom') ;
        $adresse = $request->request->get('adresse') ;
        $telephone = $request->request->get('telephone') ;

        $filename = $this->filename."entrepot(agence)/".$this->nameAgence ;
        $entrepots =(array)(json_decode(file_get_contents($filename))) ;
        
        $datas = [
            "nom" => $nom,
            "adresse" => $adresse,
            "telephone" => $telephone
        ] ;

        $entrepots = $this->appService->searchData($entrepots,$datas) ; 

        $result = $this->renderView('stock/entrepot/search.html.twig',[
            "entrepots" => $entrepots
        ]) ;

        return new Response($result) ;
    }

    #[Route('/stock/fournisseur', name: 'stock_fournisseur')]
    public function stockFournisseur(): Response
    {
        

        return $this->render('stock/fournisseur.html.twig', [
            "filename" => "stock",
            "titlePage" => "Fournisseur",
            "with_foot" => true
        ]);
    }

    #[Route('/stock/stockentrepot', name: 'stock_stockentrepot')]
    public function stockStockentrepot(): Response
    {
        

        return $this->render('stock/stockentrepot.html.twig', [
            "filename" => "stock",
            "titlePage" => "Stock d'entrepot",
            "with_foot" => false
        ]);
    }

    #[Route('/stock/stockinterne/libellee', name: 'stock_int_libellee')]
    public function stockIntLibellee(): Response
    {
        

        return $this->render('stock/stockinterne/libellee.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/creation', name: 'stock_int_creation')]
    public function stockIntCreation(): Response
    {
        

        return $this->render('stock/stockinterne/creation.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/stock', name: 'stock_int_stock')]
    public function stockIntStock(): Response
    {
        

        return $this->render('stock/stockinterne/stock.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/approvisionnement', name: 'stock_int_approvisionnement')]
    public function stockIntApprovisionnement(): Response
    {
        

        return $this->render('stock/stockinterne/approvisionnement.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/sorties', name: 'stock_int_sorties')]
    public function stockIntSorties(): Response
    {
        

        return $this->render('stock/stockinterne/sorties.html.twig', [
            
        ]);
    }

    #[Route('/stock/stockinterne/entreesortie', name: 'stock_int_entreesortie')]
    public function stockIntEntreesortie(): Response
    {
        

        return $this->render('stock/stockinterne/entreesortie.html.twig', [
            
        ]);
    }

    #[Route('/stock/approvisionnement/ajouter', name: 'stock_appr_ajouter')]
    public function stockApprAjouter(): Response
    {
        

        return $this->render('stock/approvisionnement/ajouter.html.twig', [
            "filename" => "stock",
            "titlePage" => "Approvisionnement des Produits",
            "with_foot" => true
        ]);
    }

    #[Route('/stock/approvisionnement/liste', name: 'stock_appr_liste')]
    public function stockApprListe(): Response
    {
        

        return $this->render('stock/approvisionnement/liste.html.twig', [
            "filename" => "stock",
            "titlePage" => "Liste des approvisionnements",
            "with_foot" => false
        ]);
    }
}
