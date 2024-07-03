<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\CaissePanier;
use App\Entity\HistoHistorique;
use App\Entity\ModModelePdf;
use App\Entity\PrdApprovisionnement;
use App\Entity\PrdHistoEntrepot;
use App\Entity\PrdVariationPrix;
use App\Entity\User;
use App\Service\AppService;
use App\Service\PdfGenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{
    private $entityManager;
    private $session ;
    private $appService ;
    private $urlGenerator ;
    private $nameUser ;
    private $agence ;
    private $userObj ; 
    private $user ; 
    private $nameAgence ; 

    public function __construct(EntityManagerInterface $entityManager,SessionInterface $session, AppService $appService)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->appService = $appService ;
        $this->user = $this->session->get("user") ;
        $this->appService->checkUrl() ;
        $this->nameUser = strtolower($this->session->get("user")["username"]) ;
        $this->agence = $this->entityManager->getRepository(Agence::class)->find($this->user["agence"]) ; 
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"],
            "agence" => $this->agence 
        ]) ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
    }

    /**
     * @Route("/home", name="app_home")
     */
    public function index(): Response
    {
        $username = $this->session->get("user")["username"] ;
        // $agence = $this->entityManager->getRepository(Agence::class)->find($this->session->get("user")["agence"]) ; 
        // $user = $this->entityManager->getRepository(User::class)->findOneBy([
        //     "username" => "TEST",
        //     "agence" => $agence
        //     ]) ;
        // dd($this->appService->hashPassword($user, "Admin@18")) ;
        return $this->render('home/index.html.twig', [
           "username" => $username 
        ]);
    }

    /**
     * @Route("/home/stock/produit/update", name="home_update_stock_produit")
     */
    public function updateStockProduit()
    {
        $this->entityManager->getRepository(PrdApprovisionnement::class)->effacementStockNegatif([
            "agence" => $this->agence,
            "user" => $this->userObj,
            "statut" => True,
        ]) ;

        if($this->user["role"] == "ADMIN")
        {
            $url = $this->generateUrl('app_admin');
        }
        else
        {
            $url = $this->generateUrl('app_home');
        }
        
        return new RedirectResponse($url);
    }

    /**
     * @Route("/home/datas/update", name="home_datas_update")
     */
    public function homeUpdateData(): Response
    {
        $variationPrixs = $this->entityManager->getRepository(PrdVariationPrix::class)->findAll() ;
        
        foreach($variationPrixs as $variationPrix)
        {
            $histoEntrepots = $this->entityManager->getRepository(PrdHistoEntrepot::class)->findBy([
                "variationPrix" => $variationPrix  
            ]) ;

            if(count($histoEntrepots) > 1)
            {
                $elements = [] ;
                foreach ($histoEntrepots as $histoEntrepot) {
                    # code...
                    $item = [] ;

                    $item["id"] = $histoEntrepot->getId() ;
                    $item["identrepot"] = $histoEntrepot->getEntrepot()->getId() ;
                    $item["indice"] = $histoEntrepot->getIndice() ;
                    $item["idVariation"] = $histoEntrepot->getVariationPrix()->getId() ;

                    array_push($elements,$item) ;
                }

                $indices = array(); // Tableau pour stocker les indices uniques

                foreach ($elements as $item) {
                    $indices[] = $item["indice"];
                }

                $uniqueIndices = array_unique($indices);

                if(count($uniqueIndices) > 1)
                {
                    dd($elements) ;
                }
            }
        }

        // CAISSE PANIER
        // $caissePaniers = $this->entityManager->getRepository(CaissePanier::class)->findAll() ;
        
        // foreach($caissePaniers as $caissePanier)
        // {
        //     $caissePanier->setVariationPrix($caissePanier->getHistoEntrepot()->getVariationPrix()) ;
        //     $this->entityManager->flush() ;
        // }

        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/home/datas/variation/insert/{idVar}/{idHEnt}", name="home_datas_variation_insert")
    */
    public function homeInsertDataVariation($idVar,$idHEnt): Response
    {
        $variationPrix = $this->entityManager->getRepository(PrdVariationPrix::class)->find($idVar) ;
        $histoEntrepot = $this->entityManager->getRepository(PrdHistoEntrepot::class)->find($idHEnt) ;

        $newVariation = new PrdVariationPrix() ;
    
        $newVariation->setProduit($variationPrix->getProduit()) ;
        $newVariation->setPrixVente($variationPrix->getPrixVente()) ;
        $newVariation->setIndice(null) ;
        $newVariation->setStock($histoEntrepot->getStock()) ;
        $newVariation->setStockAlert($variationPrix->getStockAlert()) ;
        $newVariation->setStatut(True) ;
        $newVariation->setCreatedAt(new \DateTimeImmutable) ;
        $newVariation->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($newVariation) ;
        $this->entityManager->flush() ;

        $histoEntrepot->setVariationPrix($newVariation) ;
        $histoEntrepot->setUpdatedAt(new \DateTimeImmutable) ;
        $this->entityManager->flush() ;

        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/home/datas/indice/update", name="home_datas_indice_update")
    */
    public function homeUpdateDataIndice(): Response
    {
        $variationPrixs = $this->entityManager->getRepository(PrdVariationPrix::class)->findAll() ;
        foreach($variationPrixs as $variationPrix)
        {
            $histoEntrepot = $this->entityManager->getRepository(PrdHistoEntrepot::class)->findOneBy([
                "variationPrix" => $variationPrix    
            ]) ;
            $variationPrix->setIndice($histoEntrepot->getIndice()) ;
            $this->entityManager->flush() ;
        }

        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/home/datas/variation/update/{idVar}/{idHEnt}", name="home_datas_variation_update")
    */
    public function homeUpdateDataVariation($idVar,$idHEnt): Response
    {
        $variationPrix = $this->entityManager->getRepository(PrdVariationPrix::class)->find($idVar) ;
        $histoEntrepot = $this->entityManager->getRepository(PrdHistoEntrepot::class)->find($idHEnt) ;

        $variationPrix->setStock($variationPrix->getStock() + $histoEntrepot->getStock()) ;
        $this->entityManager->flush() ;

        $histoEntrepot->setVariationPrix($variationPrix) ;
        $histoEntrepot->setUpdatedAt(new \DateTimeImmutable) ;
        $this->entityManager->flush() ;

        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/home/refresh/{key}", name="home_refresh")
     */
    public function refreshFile($key)
    {
        $this->appService->homeRefreshAllFiles($key) ;

        return $this->redirectToRoute('app_home');
    }

    public function validerImpressionFichier($idModeleEntete,$idModeleBas,$contenu) 
    {
        $contentEntete = "" ;
        if(!empty($idModeleEntete) || !is_null($idModeleEntete))
        {
            $modeleEntete = $this->entityManager->getRepository(ModModelePdf::class)->find($idModeleEntete) ;
            $imageLeft = is_null($modeleEntete->getImageLeft()) ? "" : $modeleEntete->getImageLeft() ;
            $imageRight = is_null($modeleEntete->getImageRight()) ? "" : $modeleEntete->getImageRight() ;
            $contentEntete = $this->renderView("parametres/modele/forme/getForme".$modeleEntete->getFormeModele().".html.twig",[
                "imageContentLeft" => $imageLeft ,
                "textContentEditor" => $modeleEntete->getContenu() ,
                "imageContentRight" => $imageRight ,
            ]) ;
            // $contentEntete = $imageLeft." ".$modeleEntete->getContenu();
        }
        
        $contentBas = "" ;
        if(!empty($idModeleBas) || !is_null($idModeleBas))
        {
            $modeleBas = $this->entityManager->getRepository(ModModelePdf::class)->find($idModeleBas) ;
            $imageLeft = is_null($modeleBas->getImageLeft()) ? "" : $modeleBas->getImageLeft() ;
            $imageRight = is_null($modeleBas->getImageRight()) ? "" : $modeleBas->getImageRight() ;
            $contentBas = $this->renderView("parametres/modele/forme/getForme".$modeleBas->getFormeModele().".html.twig",[
                "imageContentLeft" => $imageLeft ,
                "textContentEditor" => $modeleBas->getContenu() ,
                "imageContentRight" => $imageRight ,
            ]) ;
            // $contentBas = $modeleBas->getContenu() ;
        }

        $contentIMpression = $this->renderView("home/impression/impressionFichier.html.twig",[
            "contentEntete" => $contentEntete,
            "contentBas" => $contentBas,
            'contenu' => $contenu
        ]) ; 
        
        $pdfGenService = new PdfGenService() ;

        $pdfGenService->generatePdf($contentIMpression,$this->nameUser) ;
    
        // Redirigez vers une autre page pour afficher le PDF
        return $this->redirectToRoute('display_pdf');
    }

    #[Route('/home/profil/user', name: 'home_profil_utilisateur')]
    public function homeProfilUtilisateur()
    {
        return $this->render('home/profilUtilisateur.html.twig', [
            "filename" => "home",
            "titlePage" => "Profil Utilisateur",
            "with_foot" => true,
            "user" => $this->userObj
        ]);
    }
 
    #[Route('/home/profil/user/update', name: 'home_profil_utilisateur_update')]
    public function homeUpdateProfilUtilisateur(Request $request)
    {
        $home_user_nom = $request->request->get("home_user_nom") ;
        $home_user_email = $request->request->get("home_user_email") ;
        $home_user_resp = $request->request->get("home_user_resp") ;
        $home_user_mdp = $request->request->get("home_user_mdp") ;
        $home_user_confirm = $request->request->get("home_user_confirm") ;

        if(isset($home_user_mdp))
        {
            if(strlen($home_user_mdp) < 8)
            {
                return new JsonResponse([
                    "type" => "orange",
                    "message" => "Votre mot de passe doit contenir au moins 8 caractère"
                ]) ;
            }
            else if($home_user_mdp !== $home_user_confirm)
            {
                return new JsonResponse([
                    "type" => "orange",
                    "message" => "Mot de passe non identique. Confirmer votre mot de passe"
                ]) ;
            }

            $encodedPass = $this->appService->hashPassword($this->userObj,$home_user_mdp) ;
            $this->userObj->setPassword($encodedPass) ;
        }

        if (!filter_var($home_user_email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse([
                "type" => "orange",
                "message" => "Votre adresse email est invalide"
            ]) ;
        }

        $chk_uname = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => strtoupper($home_user_nom),
            "agence" => $this->agence
        ]) ;

        if(!is_null($chk_uname))
        {
            if($chk_uname->getId() != $this->userObj->getId())
            {
                return new JsonResponse([
                    "type" => "orange",
                    "message" => "Votre nom d'utilisateur existe déjà, veuillez entrer un autre"
                ]) ;
            }
        }

        $chk_email = $this->entityManager->getRepository(User::class)->findOneBy([
            "email" => strtoupper($home_user_email),
            "agence" => $this->agence
        ]) ;

        if(!is_null($chk_email))
        {
            if($chk_email->getId() != $this->userObj->getId())
            {
                return new JsonResponse([
                    "type" => "orange",
                    "message" => "Votre email existe déjà, veuillez entrer un autre"
                ]) ;
            }
        }

        $this->userObj->setEmail($home_user_email) ;
        $this->userObj->setUsername(strtoupper($home_user_nom)) ;
        $this->userObj->setPoste(strtoupper($home_user_resp)) ;
        $this->userObj->setUpdatedAt(new \DateTimeImmutable) ;

        $data = [
            "username" => strtoupper($home_user_nom),
            "email" => $home_user_email,
            "deviseLettre" => $this->session->get("user")["deviseLettre"],
            "deviseSymbole" => $this->session->get("user")["deviseSymbole"],
            "agence" => $this->agence->getId(),
            "role" => $this->session->get("user")["role"],
            "csrf_token" => $this->session->get("user")["csrf_token"]
        ];  
        
        $this->session->set("user", $data) ;

        $this->entityManager->flush() ;

        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "COMPTE",
            "nomModule" => "COMPTE",
            "refAction" => "MOD",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Modification Profil Utilisateur -> ". strtoupper($home_user_nom) ,
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

        return new JsonResponse([
            "type" => "green",
            "message" => "Modification effectuée"
        ]) ;

    }

    #[Route('/home/user/mdp/formulaire', name: 'home_user_mdp_form')]
    public function homeUserGetFormulaireMdp()
    {
        $response = $this->renderView("home/formulaireMotDePasse.html.twig") ;
        return new Response($response) ;
    }
}
