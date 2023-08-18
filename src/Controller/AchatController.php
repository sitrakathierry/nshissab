<?php

namespace App\Controller;

use App\Entity\AchBonCommande;
use App\Entity\AchDetails;
use App\Entity\AchMarchandise;
use App\Entity\AchStatut;
use App\Entity\AchStatutBon;
use App\Entity\AchType;
use App\Entity\Agence;
use App\Entity\PrdFournisseur;
use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        $filename = "files/systeme/stock/fournisseur(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateStockFournisseur($filename,$this->agence) ;

        $fournisseurs = json_decode(file_get_contents($filename)) ;

        $types = $this->entityManager->getRepository(AchType::class)->findAll() ;

        $filename = $this->filename."marchandise(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateAchMarchandise($filename,$this->agence) ;

        $marchandises = json_decode(file_get_contents($filename)) ;
        
        return $this->render('achat/creationBonDeCommande.html.twig', [
            "filename" => "achat",
            "titlePage" => "Création bon de commande (achat)",
            "with_foot" => true,
            "fournisseurs" => $fournisseurs,
            "types" => $types,
            "marchandises" => $marchandises,
        ]);
    }

    #[Route('/achat/bon/commande/liste', name: 'compta_achat_bon_commande_liste')]
    public function achatsListeBondeCommande()
    {
        $filename = $this->filename."listBonCommande(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateAchListBonCommande($filename,$this->agence) ;

        $listBonCommandes = json_decode(file_get_contents($filename)) ;

        $search = [
            "refType" => "TOTAL"
        ] ;
        
        $listBonCommandes = $this->appService->searchData($listBonCommandes, $search) ;
        
        $listBonCommandes = array_values($listBonCommandes) ;

        return $this->render('achat/listeBonDeCommande.html.twig', [
            "filename" => "achat",
            "titlePage" => "Consultation bon de commande (achat)",
            "with_foot" => false,
            "listBonCommandes" => $listBonCommandes,
        ]);
    }

    #[Route('/achat/marchandise/operation', name: 'achat_marchandise_operation')]
    public function achatOperationMarchandise()
    {
        $filename = $this->filename."marchandise(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateAchMarchandise($filename,$this->agence) ;

        $marchandises = json_decode(file_get_contents($filename)) ;

        return $this->render('achat/marchandise/operation.html.twig', [
            "filename" => "achat",
            "titlePage" => "Marchandise",
            "with_foot" => false,
            "marchandises" => $marchandises,
        ]);
    } 

    #[Route('/achat/marchandise/get/new', name: 'achat_new_marchandise')]
    public function achatGetNewMarchandise()
    {
        $response = $this->renderView("achat/marchandise/newMarchandise.html.twig") ;
        return new Response($response) ;
    }

    #[Route('/achat/marchandise/get/existing', name: 'achat_existing_marchandise')]
    public function achatGetExistingMarchandise()
    {
        $filename = $this->filename."marchandise(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateAchMarchandise($filename,$this->agence) ;

        $marchandises = json_decode(file_get_contents($filename)) ;

        $response = $this->renderView("achat/marchandise/existingMarchandise.html.twig",[
            "marchandises" => $marchandises,
        ]) ;

        return new Response($response) ;
    }

    
    #[Route('/achat/marchandise/prix/get', name: 'achat_marchandise_prix_get')]
    public function achatGetPrixMarchandise(Request $request)
    {
        $id = $request->request->get("id") ;
        if(empty($id))
        {
            return new JsonResponse([
                "type" => "orange",
                "message" => "Désignation vide",
            ]) ;
        }
        else
        {
            $marchandise = $this->entityManager->getRepository(AchMarchandise::class)->find($id) ;

            return new JsonResponse([
                "type" => "green",
                "message" => "success",
                "prix" => $marchandise->getPrix(),
            ]) ;
        }

    }

    #[Route('/achat/marchandise/creation', name: 'achat_marchandise_creation')]
    public function achatCreationMarchandise(Request $request)
    {
        $idM = $request->request->get("idM") ;
        $designation = $request->request->get("designation") ;
        $prix = $request->request->get("prix") ;

        $result = $this->appService->verificationElement([
            $designation,
            $prix,
        ],[
            "designation",
            "prix",
        ]) ;
        
        if(!$result["allow"])
            return new JsonResponse($result) ;

        if(isset($idM))
        {
            $marchandise = $this->entityManager->getRepository(AchMarchandise::class)->find($idM) ;
        }
        else
        {
            $marchandise = new AchMarchandise() ;

            $marchandise->setAgence($this->agence) ;
            $marchandise->setStatutGen(True) ;
            $marchandise->setCreatedAt(new \DateTimeImmutable) ;
        }

        $marchandise->setDesignation($designation) ;
        $marchandise->setPrix($prix) ;
        $marchandise->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($marchandise) ;
        $this->entityManager->flush() ;
        

        $filename = $this->filename."marchandise(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;
        
        $result["id"] = $marchandise->getId() ;
        if(isset($idM))
            $result["message"] = "Modification effectué" ;

        return new JsonResponse($result) ;
    } 

    
    #[Route('/achat/marchandise/supprime', name: 'achat_marchandise_supprime')]
    public function achatSupprimeMarchandise(Request $request)
    {
        $idM = $request->request->get('idM') ;

        $marchandise = $this->entityManager->getRepository(AchMarchandise::class)->find($idM) ;

        $marchandise->setStatutGen(False) ;
        $marchandise->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->flush() ;
        
        $filename = $this->filename."marchandise(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;
        
        $result["message"] = "Suppression effectué" ;
        $result["type"] = "green" ;

        return new JsonResponse($result) ;
    }

    #[Route('/achat/bon/commande/save', name: 'achat_bon_commande_save')]
    public function achatSaveBonCommande(Request $request)
    {
        // Bon de commande
        $achat_bon_fournisseur = $request->request->get('achat_bon_fournisseur') ; 
        $achat_bon_type_paiement = $request->request->get('achat_bon_type_paiement') ; 
        $compta_achat_editor = $request->request->get('compta_achat_editor') ; 
        $achat_bon_val_total_Gen = $request->request->get('achat_bon_val_total_Gen') ; 
        $ach_lieu = $request->request->get('ach_lieu') ; 
        $ach_date = $request->request->get('ach_date') ; 

        $result = $this->appService->verificationElement([
            $achat_bon_fournisseur,
            $achat_bon_type_paiement,
        ],[
            "Fournisseur",
            "Type de paiement",
            ]) ;
        
        if(!$result["allow"])
            return new JsonResponse($result) ;

        $fournisseur = $this->entityManager->getRepository(PrdFournisseur::class)->find($achat_bon_fournisseur) ;
        $type = $this->entityManager->getRepository(AchType::class)->find($achat_bon_type_paiement) ;
        $statutBon = $this->entityManager->getRepository(AchStatutBon::class)->findOneBy([
            "reference" => "ENCR"
            ]) ;
        $statut = $this->entityManager->getRepository(AchStatut::class)->findOneBy([
            "reference" => "NOTLVR"
            ]) ;

        $lastRecordBonCmd = $this->entityManager->getRepository(AchBonCommande::class)->findOneBy([], ['id' => 'DESC']);
        $numBonCmd = !is_null($lastRecordBonCmd) ? ($lastRecordBonCmd->getId()+1) : 1 ;
        $numBonCmd = str_pad($numBonCmd, 5, "0", STR_PAD_LEFT);
        // $numBonCmd = $numBonCmd."/".date('y') ; 

        $bonCommande = new AchBonCommande() ;

        $bonCommande->setAgence($this->agence) ;
        $bonCommande->setFournisseur($fournisseur) ;
        $bonCommande->setType($type) ;
        $bonCommande->setStatutBon($statutBon) ;
        $bonCommande->setStatut($statut) ;
        $bonCommande->setStatutGen(True) ;
        $bonCommande->setNumero($numBonCmd) ;
        $bonCommande->setDate(\DateTime::createFromFormat('j/m/Y',$ach_date)) ;
        $bonCommande->setLieu($ach_lieu) ;
        $bonCommande->setDescription($compta_achat_editor) ;
        $bonCommande->setMontant($achat_bon_val_total_Gen) ;
        $bonCommande->setCreatedAt(new \DateTimeImmutable) ;
        $bonCommande->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($bonCommande) ;
        $this->entityManager->flush() ;

        // Details
        $achat_bon_enr_designation = $request->request->get('achat_bon_enr_designation') ; 
        $achat_bon_enr_design_id = (array)$request->request->get('achat_bon_enr_design_id') ; 
        $achat_bon_enr_quantite = $request->request->get('achat_bon_enr_quantite') ; 
        $achat_bon_enr_prix = $request->request->get('achat_bon_enr_prix') ; 

        foreach ($achat_bon_enr_design_id as $key => $value) {
            $marchandise = $this->entityManager->getRepository(AchMarchandise::class)->find($value) ;
            
            $detail = new AchDetails() ;

            $detail->setAgence($this->agence) ;
            $detail->setBonCommande($bonCommande) ;
            $detail->setMarchandise($marchandise) ;
            $detail->setStatut($statut) ;
            $detail->setStatutGen(True) ;
            $detail->setDesignation($achat_bon_enr_designation[$key]) ;
            $detail->setQuantite($achat_bon_enr_quantite[$key]) ;
            $detail->setPrix($achat_bon_enr_prix[$key]) ;

            $this->entityManager->persist($detail) ;
            $this->entityManager->flush() ;
        }

        $filename = $this->filename."listBonCommande(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse($result) ;
    }

    #[Route('/achat/credit/operation', name: 'achat_credit_operation')]
    public function achatOperationCredit()
    {
        $filename = $this->filename."listBonCommande(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateAchListBonCommande($filename,$this->agence) ;

        $listBonCommandes = json_decode(file_get_contents($filename)) ;

        $search = [
            "refType" => "CREDIT"
        ] ;
        
        $listBonCommandes = $this->appService->searchData($listBonCommandes, $search) ;
        
        $listBonCommandes = array_values($listBonCommandes) ;

        return $this->render('achat/credit/operation.html.twig', [
            "filename" => "achat",
            "titlePage" => "Credit Achat",
            "with_foot" => false,
            "listBonCommandes" => $listBonCommandes,
        ]);
    }
 
    #[Route('/achat/details/{id}', name: 'achat_details', defaults:["id" => null])]
    public function achatDetails($id)
    {
        $id = $this->appService->decoderChiffre($id) ;

        $bonCommande = $this->entityManager->getRepository(AchBonCommande::class)->find($id) ;

        $achat = [
            "numero" => $bonCommande->getNumero(),    
            "fournisseur" => $bonCommande->getFournisseur()->getNom(),    
            "type" => $bonCommande->getType()->getNom(),    
            "description" => $bonCommande->getType()->getNom(),    
        ] ;

        return $this->render('achat/details.html.twig', [
            "filename" => "achat",
            "titlePage" => "Details Achat",
            "with_foot" => true,
            "achat" => $achat,
        ]);
    }
}
