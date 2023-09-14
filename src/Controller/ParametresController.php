<?php

namespace App\Controller;

use App\Entity\AgcDevise;
use App\Entity\Agence;
use App\Entity\Devise;
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

        $agcDevise = $this->entityManager->getRepository(AgcDevise::class)->findOneBy([
            "symbole" => $devise_symbole_base 
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
            $this->agence->setDevise($agcDevise) ;
            $this->entityManager->flush() ;
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
        
        return $this->render('parametres/modele/detailModelePdf.html.twig', [
            "filename" => "parametres",
            "titlePage" => "Détail Modèle Pdf",
            "with_foot" => true,
            "modelePdf" => $modelePdf,
        ]) ;
    }
    
    #[Route('/parametres/modele/pdf/save', name: 'param_modele_pdf_save')]
    public function paramSaveModelePdf(Request $request)
    {
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

        $modelePdf = new ModModelePdf() ;
        
        $modelePdf->setUser($this->userObj) ;
        $modelePdf->setNom($modele_nom) ;
        $modelePdf->setType($modele_value) ;
        $modelePdf->setContenu($modele_editor) ;
        $modelePdf->setFormeModele("ModelePdf_".$forme_modele_pdf) ;
        $modelePdf->setImageLeft(empty($data_modele_image_left) ? null : $data_modele_image_left) ;
        $modelePdf->setImageRight(empty($data_modele_image_right) ? null : $data_modele_image_right) ;
        $modelePdf->setStatut(True) ;
        $modelePdf->setCreatedAt(new \DateTimeImmutable) ;
        $modelePdf->setUpdatedAt(new \DateTimeImmutable) ;
        
        $this->entityManager->persist($modelePdf) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."modelePdf(user)/".$this->nameUser."_".$this->userObj->getId().".json" ; 
        if(file_exists($filename))
            unlink($filename) ;
        
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
}
