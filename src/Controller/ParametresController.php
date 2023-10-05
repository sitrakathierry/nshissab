<?php

namespace App\Controller;

use App\Entity\AgcDevise;
use App\Entity\Agence;
use App\Entity\Devise;
use App\Entity\Menu;
use App\Entity\MenuAgence;
use App\Entity\MenuUser;
use App\Entity\ModModelePdf;
use App\Entity\ParamTvaType;
use App\Entity\Produit;
use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ParametresController extends AbstractController
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
        $this->filename = "files/systeme/parametres/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"] 
        ]) ;
    }

    #[Route('/parametres', name: 'app_parametres')]
    public function index(): Response
    {
        return $this->render('parametres/index.html.twig', [
            'controller_name' => 'ParametresController',
        ]);
    }

    #[Route('/parametres/general', name: 'param_general')]
    public function paramGeneral(): Response
    {

        $filename = $this->filename."general(agence)/".$this->nameAgence ; 

        if(!file_exists($filename))
            $this->appService->generateParamGeneral($filename, $this->agence) ; 

        $devises = json_decode(file_get_contents($filename)) ;
        
        $deviseAgence = $this->agence->getDevise() ;

        return $this->render('parametres/general.html.twig', [
            "filename" => "parametres",
            "titlePage" => "Paramètre Général",
            "with_foot" => false,
            "devises" => $devises,
            "deviseAgence" => $deviseAgence
        ]);
    }
    
    #[Route('/parametres/agence/update', name: 'param_agence_update')]
    public function parametreAgenceUpdate(Request $request)
    {
        $devise_symbole_base = $request->request->get('devise_symbole_base') ; 
        $devise_lettre_base = $request->request->get('devise_lettre_base') ; 
        $devise_modif = $request->request->get('devise_modif') ; 

        $agcDevise = $this->entityManager->getRepository(AgcDevise::class)->findOneBy([
            "symbole" => strtoupper($devise_symbole_base ),
            "lettre" => $devise_lettre_base,
        ]) ;

        if(is_null($agcDevise)) 
        {
            $agcDevise = new AgcDevise() ;
            $agcDevise->setSymbole($devise_symbole_base) ;
            $agcDevise->setLettre($devise_lettre_base) ;

            $this->entityManager->persist($agcDevise) ;
            $this->entityManager->flush() ;

            $this->agence->setDevise($agcDevise) ;
            $this->entityManager->flush() ;
        }
        else
        {
            if(isset($devise_modif))
            {
                $agcDevise->setSymbole($devise_symbole_base) ;
                $agcDevise->setLettre($devise_lettre_base) ;
    
                $this->entityManager->persist($agcDevise) ;
                $this->entityManager->flush() ;
    
                $this->agence->setDevise($agcDevise) ;
                $this->entityManager->flush() ;
            }
            else
            {
                $this->agence->setDevise($agcDevise) ;
                $this->entityManager->flush() ;
            }
        }

        return new JsonResponse([
            "type" => "green",
            "message" => "Votre devise de base a été défini avec succès"
        ]) ;
    }

    
    #[Route('/parametres/devise/save', name: 'param_devise_save')]
    public function paramSaveDeviseGeneral(Request $request)
    {
        $devise_symbole_change = $request->request->get('devise_symbole_change') ; 
        $devise_lettre_change = $request->request->get('devise_lettre_change') ; 
        $devise_montant_base = $request->request->get('devise_montant_base') ; 

        $data = [
            $devise_symbole_change,
            $devise_lettre_change,
            $devise_montant_base
        ] ;

        $dataMessage = [
            "Symbole",
            "Lettre",
            "Montant de base"
        ] ;

        $result = $this->appService->verificationElement($data, $dataMessage) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;
        
        $devise = new Devise() ;

        $devise->setAgence($this->agence) ;
        $devise->setSymbole($devise_symbole_change);
        $devise->setLettre($devise_lettre_change) ;
        $devise->setMontantBase($devise_montant_base) ;
        $devise->setStatut(True) ;
        $devise->setCreatedAt(new \DateTimeImmutable) ;
        $devise->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($devise) ;
        $this->entityManager->flush() ;

        return new JsonResponse($result) ;
    }

    #[Route('/parametres/tva/creation', name: 'param_tva_creation_type')]
    public function paramTvaCreationType()
    {
        $paramTvaTypes = $this->entityManager->getRepository(ParamTvaType::class)->findBy([
            "agence" => $this->agence,
            "statut" => True
        ]) ;
        return $this->render('parametres/tva/creationType.html.twig', [
            "filename" => "parametres",
            "titlePage" => "Création Type TVA",
            "with_foot" => false,
            "paramTvaTypes" => $paramTvaTypes
        ]);
    }   

    #[Route('/parametres/tva/definition', name: 'param_tva_definir_plage')]
    public function paramTvadefinitionPlage()
    {
        $paramTvaTypes = $this->entityManager->getRepository(ParamTvaType::class)->findBy([
            "agence" => $this->agence,
            "statut" => True
        ]) ;
        
        $filename = "files/systeme/stock/preference(user)/".$this->nameUser.".json" ;
        $preferences = json_decode(file_get_contents($filename)) ;
        
        $path = "files/systeme/stock/stock_general(agence)/".$this->nameAgence ; 

        $filename = $this->filename."produitTypeTva(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;
            
        if(!file_exists($filename))
            $this->appService->generateProduitParamTypeTva($path,$filename,$this->agence) ;
        
        $produitsTypeTvas = json_decode(file_get_contents($filename)) ;

        $search = [
            "tvaType" => "-"
        ] ;

        $produitsTypeTvas = $this->appService->searchData($produitsTypeTvas,$search) ;

        return $this->render('parametres/tva/definitionPlage.html.twig', [
            "filename" => "parametres",
            "titlePage" => "Définition Plage TVA",
            "with_foot" => false,
            "paramTvaTypes" => $paramTvaTypes,
            "categories" => $preferences,
            "produitsTypeTvas" => $produitsTypeTvas
        ]);
    }

    #[Route('/parametres/tva/type/display', name: 'param_display_elem_type_tva')]
    public function paramDisplayElemTypeTva(Request $request)
    {
        $path = "files/systeme/stock/stock_general(agence)/".$this->nameAgence ; 
        $filename = $this->filename."produitTypeTva(agence)/".$this->nameAgence ;    
        // unlink($filename) ;
        if(!file_exists($filename))
            $this->appService->generateProduitParamTypeTva($path,$filename,$this->agence) ;
        
        $produitsStock = json_decode(file_get_contents($filename)) ;

        $idTypeTva = $request->request->get('idTypeTva') ;

        $search = [
            "tvaType" => $idTypeTva
        ] ;
        $produitsTypeTvas = $this->appService->searchData($produitsStock,$search) ;

        $search = [
            "tvaType" => "-"
        ] ;
        $produits = $this->appService->searchData($produitsStock,$search) ;

        $response = $this->renderView('parametres/tva/displayTypeTva.html.twig', [
            "produitsTypeTvas" => $produitsTypeTvas,
            "produits" => $produits
        ]) ;

        return new Response($response) ;
    }

    
    #[Route('/parametres/tva/type/search', name: 'param_search_prd_in_tva_type')]
    public function paramSearchPrdInTvaType(Request $request)
    {
        $path = "files/systeme/stock/stock_general(agence)/".$this->nameAgence ; 
        $filename = $this->filename."produitTypeTva(agence)/".$this->nameAgence ;    
        // unlink($filename) ;
        if(!file_exists($filename))
            $this->appService->generateProduitParamTypeTva($path,$filename,$this->agence) ;
        
        $produitsStock = json_decode(file_get_contents($filename)) ;

        $tvaType = $request->request->get('tvaType') ;
        $idC = $request->request->get('idC') ;
        $produit = $request->request->get('produit') ;

        $search = [
            "idC" => $idC,
            "produit" => $produit,
            "tvaType" => $tvaType
        ] ;

        $produitsTypeTvas = $this->appService->searchData($produitsStock,$search) ;

        $response = $this->renderView('parametres/tva/searchPrdTypeTva.html.twig', [
            "produitsTypeTvas" => $produitsTypeTvas
        ]) ;

        return new Response($response) ;
    }

    
    #[Route('/parametres/tva/produit/update', name: 'param_tva_update_produit')]
    public function paramTvaUpdateProduit(Request $request)
    {
        $info = (array)$request->request->get('info') ;
        if(empty($info))
        {
            $result = [
                "type" => "orange",
                "message" => "Aucun élément séléctionnée"
            ] ;

            return new JsonResponse($result) ;
        }

        foreach ($info as $info) {
            $idP = $info['idP'] ;
            $idType = $info['idType'] ;

            $produit = $this->entityManager->getRepository(Produit::class)->find($idP) ;
            if(!empty($idType))
            {   
                $paramTvaType = $this->entityManager->getRepository(ParamTvaType::class)->find($idType) ;
                $produit->setTvaType($paramTvaType) ;
                $result = [
                    "type" => "green",
                    "message" => "Type ajoutée"
                ] ;
            }
            else
            {
                $produit->setTvaType(null) ;
                $result = [
                    "type" => "dark",
                    "message" => "Type supprimée"
                ] ;
            }
            
            $this->entityManager->flush() ;
        } 
        
        $path = "files/systeme/stock/stock_general(agence)/".$this->nameAgence ; 
        $filename = $this->filename."produitTypeTva(agence)/".$this->nameAgence ;    
        unlink($filename) ;
        if(!file_exists($filename))
            $this->appService->generateProduitParamTypeTva($path,$filename,$this->agence) ;

        return new JsonResponse($result) ;

    }

    #[Route('/parametres/tva/save', name: 'param_tva_save_type')]
    public function paramTvaSaveType(Request $request)
    {
        $tva_designation = $request->request->get('tva_designation'); 
        $tva_valeur = $request->request->get('tva_valeur'); 

        $data = [
            $tva_designation,
            $tva_valeur
        ] ;

        $dataMessage = [
            "Désignation",
            "Valeur",
        ];

        $result = $this->appService->verificationElement($data,$dataMessage) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $paramTvaType = new ParamTvaType() ;

        $paramTvaType->setAgence($this->agence);
        $paramTvaType->setDesignation($tva_designation) ;
        $paramTvaType->setValeur($tva_valeur) ;
        $paramTvaType->setStatut(True) ;
        $paramTvaType->setCreatedAt(new \DateTimeImmutable);
        $paramTvaType->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($paramTvaType) ;
        $this->entityManager->flush() ;

        return new JsonResponse($result) ;
    }
 
    #[Route('/parametres/modele/pdf/creation', name: 'param_modele_pdf_creation')]
    public function paramCreationModelePdf()
    {
        return $this->render('parametres/modele/creationModelePdf.html.twig', [
            "filename" => "parametres",
            "titlePage" => "Création Modèle Pdf",
            "with_foot" => true,
        ]);
    }

    #[Route('/parametres/information/societe/get', name: 'params_information_societe_get')]
    public function paramsGetInformationSociete(Request $request)
    {
        $information = $request->request->get("information") ;
        if($information == "SOCIETE")
        {
            $response = $this->renderView('parametres/modele/getInformationSociete.html.twig', [
                "agence" => $this->agence 
            ]);
        }
        else
        {
            $response = $this->renderView('parametres/modele/getInformationPersonnel.html.twig', [
                "user" => $this->userObj
            ]);
        }

        return new Response($response) ;
    }

    #[Route('/parametres/modele/contenu/get', name: 'params_contenu_modele_pdf_get')]
    public function paramsGetContenuModelePdf(Request $request)
    {
        $indice = $request->request->get("indice") ;
        $response = $this->renderView('parametres/modele/pdf/getContenuModelePdf_'.$indice.'.html.twig');

        return new Response($response) ;
    }

    #[Route('/parametres/modele/pdf/consultation', name: 'param_modele_pdf_consultation')]
    public function paramConsultationModelePdf()
    {
        $filename = $this->filename."modelePdf(user)/".$this->nameUser."_".$this->userObj->getId().".json" ;    
        if(!file_exists($filename))
            $this->appService->generateModModelePdf($filename,$this->userObj) ;
        
        $modelePdfs = json_decode(file_get_contents($filename)) ;
        
        return $this->render('parametres/modele/consultationModelePdf.html.twig', [
            "filename" => "parametres",
            "titlePage" => "Liste Modèle Pdf",
            "with_foot" => true,
            "modelePdfs" => $modelePdfs,
        ]);
    }

    
    #[Route('/parametres/modele/pdf/get', name: 'param_modele_pdf_get')]
    public function paramGetModelePdf()
    {
        $filename = $this->filename."modelePdf(user)/".$this->nameUser."_".$this->userObj->getId().".json" ;    
        if(!file_exists($filename))
            $this->appService->generateModModelePdf($filename,$this->userObj) ;
        
        $modelePdfs = json_decode(file_get_contents($filename)) ;

        $response = $this->renderView("parametres/modele/getModelePdf.html.twig",[
            "modelePdfs" => $modelePdfs,  
        ]) ;

        return new Response($response) ;
    }

    #[Route('/parametres/modele/pdf/details/{id}', name: 'param_modele_pdf_detail', defaults:["id" => null])]
    public function paramDetailModelePdf($id)
    {
        $id = $this->appService->decoderChiffre($id) ;
        $modelePdf = $this->entityManager->getRepository(ModModelePdf::class)->find($id) ;
        
        $templateModele = $this->renderView('parametres/modele/editPdf/getContenu'.$modelePdf->getFormeModele().'.html.twig',[
            "modelePdf" => $modelePdf
        ]) ;

        return $this->render('parametres/modele/detailModelePdf.html.twig', [
            "filename" => "parametres",
            "titlePage" => "Détail Modèle Pdf",
            "with_foot" => true,
            "modelePdf" => $modelePdf, 
            "templateModele" => $templateModele
        ]) ;
    }
    
    #[Route('/parametres/modele/pdf/save', name: 'param_modele_pdf_save')]
    public function paramSaveModelePdf(Request $request)
    {
        $mod_id_modele = $request->request->get("mod_id_modele") ;
        $modele_nom = $request->request->get("modele_nom") ;
        $modele_value = $request->request->get("modele_value") ;
        $modele_editor = $request->request->get("modele_editor") ;
        // $pdf_contenu_modele = $request->request->get("pdf_contenu_modele") ;
        $data_modele_image_left = $request->request->get("data_modele_image_left") ;
        $data_modele_image_right = $request->request->get("data_modele_image_right") ;
        $forme_modele_pdf = $request->request->get("forme_modele_pdf") ;

        $result = $this->appService->verificationElement([
            $modele_nom,
            $modele_value,
            $forme_modele_pdf,
            $modele_editor,
        ],[
            "Nom Modele",
            "Type Modèle",
            "Forme Modèle",
            "Contenu Modèle",
        ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        if(isset($mod_id_modele))
        {
            $modelePdf = $this->entityManager->getRepository(ModModelePdf::class)->find($mod_id_modele) ;
            $formeModele = $forme_modele_pdf ;
        }
        else
        {
            $modelePdf = new ModModelePdf() ;
            $modelePdf->setUpdatedAt(new \DateTimeImmutable) ;
            $modelePdf->setUser($this->userObj) ;
            $formeModele = "ModelePdf_".$forme_modele_pdf ;
        }
        
        $modelePdf->setNom($modele_nom) ;
        $modelePdf->setType($modele_value) ;
        $modelePdf->setContenu($modele_editor) ;
        $modelePdf->setFormeModele($formeModele) ;
        $modelePdf->setImageLeft(empty($data_modele_image_left) ? null : $data_modele_image_left) ;
        $modelePdf->setImageRight(empty($data_modele_image_right) ? null : $data_modele_image_right) ;
        $modelePdf->setStatut(True) ;
        $modelePdf->setCreatedAt(new \DateTimeImmutable) ;
        
        $this->entityManager->persist($modelePdf) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."modelePdf(user)/".$this->nameUser."_".$this->userObj->getId().".json" ; 
        if(file_exists($filename))
            unlink($filename) ;
        
        if(isset($mod_id_modele))
        {
            $result = [
                "type" => "green",    
                "message" => "Modification effectué",    
            ] ;
        }

        return new JsonResponse($result) ;
    }

    #[Route('/parametres/declaration/service', name: 'param_declaration_service')]
    public function paramDeclatationService(Request $request)
    {
        $services = $this->entityManager->getRepository(DepService::class)->findBy([
            "agence" => $this->agence    
        ]) ;

        return $this->render('parametres/service/configurationService.html.twig', [
            "filename" => "parametres",
            "titlePage" => "Paramètre Service",
            "with_foot" => true,
            "services" => $services,
        ]);
    }

    #[Route('/parametres/utilisateur/agent/creation', name: 'param_utilisateur_creation_agent')]
    public function paramUtilisateurCreationAgent()
    {
        $password = $this->appService->generatePassword() ;
        return $this->render('parametres/utilisateur/creationCompteAgent.html.twig', [
            "filename" => "parametres",
            "titlePage" => "Création Agent",
            "with_foot" => true,
            'password' => $password,
        ]);
    }

    #[Route('/parametres/utilisateur/agent/save', name: 'param_utilisateur_save_agent')]
    public function paramUtilisateurSaveAgent(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $userAgences = $this->entityManager->getRepository(User::class)->findBy([
            "agence" => $this->agence,
            "statut" => True,    
        ]) ;

        $limiteCompte = $this->agence->getCapacite() ;
        $totalCompteActif = count($userAgences) ;

        if($totalCompteActif >= $limiteCompte)
        {
            return new JsonResponse([
                "message"=> '<div class="text-center">Désole, la capacité maximum est atteint.<br>Vous ne pouvez plus créer de compte. <br>Veuiller vous renseigner auprès du responsable pour plus d\'information . Merci.</div>', 
                "type"=> "red"
            ]) ;
        }
        

        $username = $request->request->get('username') ;
        $password = $request->request->get('password') ;
        $email = $request->request->get('email') ;
        $poste = $request->request->get('poste') ;

        $data = [$username ,$password ,$email ,$poste] ;
        $dataMessage = ["nom d'utilisateur" ,"mot de passe" ,"email" ,"responsabilite"] ;
        
        $result = $this->appService->verificationElement($data,$dataMessage) ;

        $allow = $result["allow"] ;
        $type = $result["type"] ;
        $message = $result["message"] ;

        $chk_uname = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => strtoupper($username),
            "agence" => $this->agence
        ]) ;

        if(!is_null($chk_uname))
        {
            $allow = False ;
            $type="orange" ;
            $message = "Votre nom d'utilisateur existe déjà, veuillez entrer un autre" ;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $chk_email = $this->entityManager->getRepository(User::class)->findOneBy([
                "email" => $email,
                "agence" => $this->agence,
                "statut" => True
            ]) ;
            
            if(!is_null($chk_email))
            {
                $allow = False ;
                $type="orange" ;
                $message = "Votre adresse email existe déjà, veuillez entrer un autre" ;
            }
        } else {
            $allow = False ;
            $type="red" ;
            $message = "Votre adresse email est invalide" ;
        }

        if(strlen($password) < 8)
        {
            $allow = False ;
            $type="orange" ;
            $message = "Votre mot de passe doit contenir au moins 8 caractère" ;
        }

        $userJumSystem = $this->entityManager->getRepository(User::class)->findBy([
            "username" => strtoupper($username)
        ]) ;

        foreach ($userJumSystem as $userJumeau) {
            $isPasswordValid = $passwordEncoder->isPasswordValid($userJumeau,$password);
            if ($isPasswordValid) {
                $newPass = "admin#".date("dmYHis") ;
                $userJumeau->setPassword($this->appService->hashPassword($userJumeau,$newPass)) ;
                $this->entityManager->flush() ;

                return new JsonResponse([
                    "message" => "Entrer un nouveau mot de passe", 
                    "type" => "red"
                ]) ;
            }
        }

        if(!$allow)
            return new JsonResponse(["message"=>$message, "type"=>$type]) ;

        $user = new User() ;
        
        $encodedPass = $this->appService->hashPassword($user,$password) ;

        $user->setUsername(strtoupper($username)) ;
        $user->setEmail($email);
        $user->setPassword($encodedPass) ;
        $user->setPoste($poste) ;
        $user->setAgence($this->agence) ;
        $user->setStatut(True) ; 
        $user->setRoles(["AGENT"]) ;
        $user->setCreatedAt(new \DateTimeImmutable) ;
        $user->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $filename = 'files/json/menu/'.strtolower($username).'_'.$this->agence->getId().'.json' ;

        $dataUser = [] ;
        if(!file_exists($filename))
            file_put_contents($filename,json_encode($dataUser)) ;

        return new JsonResponse(["message"=>$message, "type"=>$type]) ;
    }

    #[Route('/parametres/utilisateur/agent/liste', name: 'param_utilisateur_listes_agent')]
    public function paramUtilisateurListesAgent()
    {
        $agents = $this->entityManager->getRepository(User::class)->findBy([
            "agence" => $this->agence,
            "statut" => True,    
        ]) ;

        return $this->render('parametres/utilisateur/listesCompteAgent.html.twig', [
            "filename" => "parametres",
            "titlePage" => "Liste des agents",
            "with_foot" => false,
            "agents" => $agents,
        ]);
    }

    public function regenerateUserMenu($is_user = null)
    {
        if(!is_null($is_user))
            $user = $is_user ;
        else
            $user = $this->session->get("user")  ; 

        $filename = "files/json/menu/".strtolower($user['username'])."_".$user['agence'].".json" ;
        if(!file_exists($filename))
        {
            $userClass = $this->entityManager
                            ->getRepository(User::class)
                            ->findOneBy(array("email" => $user['email'])) ;
            
            $menus = [] ;
            
            $menuUsers = $this->appService->requestMenu($userClass->getRoles()[0],$userClass,null) ;

            if(!empty($menuUsers))
            {
                $id = 0;
                $this->appService->getMenu($menuUsers,$id,$menus,$user) ;
            } 
            
            $json = json_encode($menus) ;
            file_put_contents($filename, $json); 
        }
    }

    #[Route('/parametres/utilisateur/agent/attribution', name: 'param_utilisateur_attribution_menu_agent')]
    public function paramAttributionMenuAgent()
    {
        $filename = "files/json/menu/".$this->nameUser."_".$this->agence->getId().".json" ;
        if(!file_exists($filename))
            $this->regenerateUserMenu(null) ;

        $menu_array = json_decode(file_get_contents($filename)) ;

        $agents = $this->entityManager->getRepository(User::class)->findBy([
            "agence" => $this->agence
        ]) ;

        return $this->render('parametres/utilisateur/gestionMenuAgent.html.twig',[
            "filename" => "parametres",
            "titlePage" => "Attribution menu agent",
            "with_foot" => true,
            "agents" => $agents,
            "menus" => $menu_array,
        ]);

        // return $this->render('parametres/utilisateur/gestionMenuAgent.html.twig', [
        //     "filename" => "parametres",
        //     "titlePage" => "Attribution menu agent",
        //     "with_foot" => true,
        // ]);
    }

    #[Route('/parametres/user/agent/attribution/save', name: 'param_save_attribution_menu_agent')]
    public function paramSaveAttributionMenuAgent(Request $request)
    {
        $agent = $request->request->get('agence') ;
        try
        {
            $menus = (array)$request->request->get('menus') ;
            $compareMenu = [] ;
            foreach ($menus as $unMenu) {
                array_push($compareMenu,$unMenu) ;
            }

            $userAgent = $this->entityManager->getRepository(User::class)->find($agent) ;

            if(!is_null($menus))
            {
                // dd($menus) ;

                $toremove = [];
                for ($i=0; $i < count($menus); $i++) { 
                    $menu = $this->entityManager->getRepository(Menu::class)->find($menus[$i]) ;
                    
                    $oneMenuAgence = $this->entityManager->getRepository(MenuAgence::class)->findOneBy([
                        "menu" => $menu,
                        "agence" => $this->agence
                    ]) ;

                    $chkMenuUser = $this->entityManager->getRepository(MenuUser::class)->findOneBy([
                        "menuAgence" => $oneMenuAgence,
                        "user" => $userAgent
                    ]) ;


                    if(!is_null($chkMenuUser))
                    {
                        array_push($toremove,$menus[$i]) ;
                    }
                }
                
                $menus = array_diff($menus, $toremove);
                
                $menus = array_values($menus) ;

                $addMenus = [] ;

                foreach ($menus as $menu) {
                    array_push($addMenus,$menu) ; 
                }

                for ($i=0; $i < count($addMenus); $i++) { 
                    $menu = $this->entityManager->getRepository(Menu::class)->find($menus[$i]) ;
                    $chkMenuAg = $this->entityManager->getRepository(MenuAgence::class)->findOneBy([
                        "menu" => $menu,
                        "agence" => $this->agence
                    ]) ;

                    $menuUser = new MenuUser() ;
                    $menuUser->setMenuAgence($chkMenuAg) ;
                    $menuUser->setUser($userAgent) ;
                    $menuUser->setStatut(True) ;
                    $menuUser->setCreatedAt(new \DateTimeImmutable) ;
                    $menuUser->setUpdatedAt(new \DateTimeImmutable) ;
    
                    $this->entityManager->persist($menuUser);
                    $this->entityManager->flush();
                }

                $menuUserAll = $this->entityManager->getRepository(MenuUser::class)->findBy([
                    "user" => $userAgent
                ]) ;

                // dd($menuAgAll) ;
                foreach ($menuUserAll as $mUser) {
                    $menuId = $mUser->getMenuAgence()->getMenu()->getId() ;
                    if(!in_array($menuId,$compareMenu))
                    {
                        $this->entityManager->remove($mUser);
                        $this->entityManager->flush();
                    }
                }

                $dataUser = [
                    "id" => $userAgent->getId(),
                    "username" => $userAgent->getUsername(),
                    "email" => $userAgent->getEmail(),
                    "agence" => $userAgent->getAgence()->getId(),
                ] ;

                $filename = "files/json/menu/".strtolower($userAgent->getUsername())."_".$userAgent->getAgence()->getId().".json" ;

                if(file_exists($filename))
                    unlink($filename) ;

                $this->regenerateUserMenu($dataUser) ;
                $type = 'green' ;
                $message = "Information enregistré avec succès" ;
            }
            else
            {
                $type = 'orange' ;
                $message = "Veuiller sélectionner un menu" ;
            }
            
        }
        catch(\Exception $e)
        {
            if(empty($this->agence))
            {
                $type = 'orange' ;
                $message = "Veuillez sélectionner une agence" ;
            }
            else
            {
                $type = 'red' ;
                $message = $e->getMessage() ;
            }
            
        }

        return new JsonResponse(["type" => $type, "message" => $message]) ;
    }

    #[Route('/parametres/user/agent/menu/get', name: 'param_user_get_menu_Agent')]
    public function paramGetMenuAgent(Request $request)
    {
        $idUserAgent = $request->request->get("idAgence") ;

        $user = $this->entityManager->getRepository(User::class)->find($idUserAgent) ;

        $dataUser = [
            "id" => $user->getId(),
            "username" => $user->getUsername(),
            "email" => $user->getEmail(),
            "agence" => $user->getAgence()->getId(),
        ] ;

        $filename = "files/json/menu/".strtolower($user->getUsername())."_".$user->getAgence()->getId().".json" ;
        if(!file_exists($filename))
            $this->regenerateUserMenu($dataUser) ;
        
        $menuManager = json_decode(file_get_contents($filename)) ;

        $response = [
            "manager" => strtolower($user->getUsername()),
            "menuManager" => $menuManager
        ] ;
        
        return new JsonResponse($response) ;
    }

    #[Route('/parametres/modele/element/delete', name: 'param_modele_delete_element')]
    public function paramModeleDeleteElement(Request $request)
    {
        $idModele = $request->request->get("idModele") ;

        $modelePdf = $this->entityManager->getRepository(ModModelePdf::class)->find($idModele) ;

        $modelePdf->setStatut(False) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."modelePdf(user)/".$this->nameUser."_".$this->userObj->getId().".json" ; 
        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse([
            "type" => "green",    
            "message" => "Suppression effectué",    
        ]) ;
    }

    #[Route('/parametres/utilisateur/agent/disable', name: 'param_utils_attribution_agent_update')]
    public function paramDisableCompteAgent(Request $request)
    {
        $type = $request->request->get("type") ; 
        $idUser = $request->request->get("idUser") ; 

        $user = $this->entityManager->getRepository(User::class)->find($idUser) ;
        
        if($type == "DESACTIVE")
        {
            $user->setDisabled(True) ;
            $message = "Désactivation effectué" ;
        }
        else if($type == "ACTIVER")
        {
            $user->setDisabled(null) ;
            $message = "Activation effectué" ;
        }
        else if($type == "EFFACER")
        {
            $user->setStatut(False) ;
            $message = "Suppresion effectué" ;
        }
        $this->entityManager->flush() ;

        return new JsonResponse([
            "type" => "green",    
            "message" => $message,    
        ]) ;
    }

    #[Route('/parametres/utilisateur/agent/details/{idUser}', name: 'param_utilisateur_details_agent',defaults:["idUser" => null])]
    public function paramDetailsUserAgent($idUser)
    {
        $user = $this->entityManager->getRepository(User::class)->find($idUser) ;

        return $this->render('parametres/utilisateur/detailsUserAgent.html.twig',[
            "filename" => "parametres",
            "titlePage" => "Details agent",
            "with_foot" => true,
            "agent" => $user,
        ]);
    }

    
    #[Route('/parametres/user/contentPass/get', name: 'param_user_content_password_get')]
    public function paramGetUserContentPass()
    {
        $response = $this->renderView("parametres/utilisateur/getContentMotDePasse.html.twig") ;

        return new Response($response) ;
    }

    #[Route('/parametres/user/password/update', name: 'param_user_update_password')]
    public function paramUpdateUserPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $idUser = $request->request->get("idUser") ;
        $newPass = $request->request->get("newPass") ;
        
        if(strlen($newPass) < 8)
        {
            return new JsonResponse([
                "type" => "orange",    
                "message" => "Votre mot de passe doit contenir au moins 8 caractère",    
            ]) ;
        }

        $user = $this->entityManager->getRepository(User::class)->find($idUser) ;
        
        $userJumSystem = $this->entityManager->getRepository(User::class)->findBy([
            "username" => strtoupper($user->getUsername())
        ]) ;

        if(count($userJumSystem) > 1)
        {
            foreach ($userJumSystem as $userJumeau) {
                if($userJumeau->getId() == $idUser)
                    continue;

                $isPasswordValid = $passwordEncoder->isPasswordValid($userJumeau,$newPass);
                if ($isPasswordValid) {
                    $newPassExist = "admin#".date("dmYHis") ;
                    $userJumeau->setPassword($this->appService->hashPassword($userJumeau,$newPassExist)) ;
                    $this->entityManager->flush() ;
                    
                    return new JsonResponse([
                        "message" => "Entrer un nouveau mot de passe", 
                        "type" => "red"
                    ]) ;
                }
            }
        }

        $encodedPass = $this->appService->hashPassword($user,$newPass) ;
        $user->setPassword($encodedPass) ;
        $this->entityManager->flush();

        return new JsonResponse([
            "type" => "green",    
            "message" => "Mise à jour effectué",    
        ]) ;
    }

    
}
