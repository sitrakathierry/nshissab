<?php

namespace App\Controller;

use App\Entity\AchBonCommande;
use App\Entity\AchDetails;
use App\Entity\AchHistoPaiement;
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
        $this->appService->synchronisationAchatBonDeCommande() ;

        $filename = $this->filename."listBonCommande(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateAchListBonCommande($filename,$this->agence) ;

        $listBonCommandes = json_decode(file_get_contents($filename)) ;

        // $search = [
        //     "refType" => "TOTAL"
        // ] ;
        
        // $listBonCommandes = $this->appService->searchData($listBonCommandes, $search) ;
        
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
        // $prix = $request->request->get("prix") ;

        $result = $this->appService->verificationElement([
            $designation,
            // $prix,
        ],[
            "designation",
            // "prix",
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
        $marchandise->setPrix(null) ;
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

    #[Route('/achat/bon/marchandise/item/supprime', name: 'achat_bon_marchandise_item_supprime')]
    public function achatSupprimeItemBonMarchandise(Request $request)
    {
        $idDetailBon = $request->request->get('idDetailBon') ;

        $detailBon = $this->entityManager->getRepository(AchDetails::class)->find($idDetailBon) ;

        $detailBon->setStatutGen(False) ;
        $this->entityManager->flush() ;
        
        $filename = $this->filename."listBonCommande(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;
        
        $result["message"] = "Suppression effectué" ;
        $result["type"] = "green" ;

        return new JsonResponse($result) ;
    }

    #[Route('/achat/bon/marchandise/item/get', name: 'achat_get_modif_detail_bon')]
    public function achatGetDetailBonCommande(Request $request)
    {
        $idDetailBon = $request->request->get('idDetailBon') ;
        $detailBon = $this->entityManager->getRepository(AchDetails::class)->find($idDetailBon) ;

        $filename = $this->filename."marchandise(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateAchMarchandise($filename,$this->agence) ;

        $marchandises = json_decode(file_get_contents($filename)) ;

        $response = $this->renderView("achat/getDetailBonCommande.html.twig",[
            "marchandises" => $marchandises,  
            "detailBon" => $detailBon,  
        ]) ;

        return new Response($response) ;
    }

    #[Route('/achat/bon/marchandise/item/update', name: 'achat_update_detail_bon')]
    public function achatUpdateItemBonMarchandise(Request $request)
    {
        $idDetailBon = $request->request->get('idDetailBon') ;
        $achat_bon_designation = $request->request->get('achat_bon_designation') ;
        $achat_bon_reference = $request->request->get('achat_bon_reference') ;
        $achat_bon_quantite = $request->request->get('achat_bon_quantite') ;
        $achat_bon_prix = $request->request->get('achat_bon_prix') ;
    
        $detailBon = $this->entityManager->getRepository(AchDetails::class)->find($idDetailBon) ;

        $marchandise = $this->entityManager->getRepository(AchMarchandise::class)->find($achat_bon_designation) ;
            
        $detailBon->setMarchandise($marchandise) ;
        $detailBon->setDesignation($marchandise->getDesignation()) ;
        $detailBon->setReference($achat_bon_reference) ;
        $detailBon->setQuantite($achat_bon_quantite) ;
        $detailBon->setPrix($achat_bon_prix) ;

        $this->entityManager->flush() ;
        
        $filename = $this->filename."listBonCommande(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;
        
        $result["message"] = "Modification effectué" ;
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
        $achat_bon_enr_reference = $request->request->get('achat_bon_enr_reference') ; 

        foreach ($achat_bon_enr_design_id as $key => $value) {
            $marchandise = $this->entityManager->getRepository(AchMarchandise::class)->find($value) ;
            
            $detail = new AchDetails() ;

            $detail->setAgence($this->agence) ;
            $detail->setBonCommande($bonCommande) ;
            $detail->setMarchandise($marchandise) ;
            $detail->setStatut($statut) ;
            $detail->setStatutGen(True) ;
            $detail->setDesignation($achat_bon_enr_designation[$key]) ;
            $detail->setReference($achat_bon_enr_reference[$key]) ;
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

    #[Route('/achat/bon/commande/addition/save', name: 'achat_bon_commande_addition_save')]
    public function achatSaveAdditionBonCommande(Request $request)
    {
        $idBonCommande = $request->request->get('achat_id_bon_commande') ; 

        $bonCommande = $this->entityManager->getRepository(AchBonCommande::class)->find($idBonCommande) ;

        $ach_lieu = $request->request->get('ach_lieu') ; 
        $ach_date = $request->request->get('ach_date') ; 

        $bonCommande->setLieu($ach_lieu) ;
        $bonCommande->setDate(\DateTime::createFromFormat("d/m/Y",$ach_date)) ;

        $this->entityManager->flush() ;

        $statut = $this->entityManager->getRepository(AchStatut::class)->findOneBy([
            "reference" => "NOTLVR"
        ]) ;

        // Details
        $achat_bon_enr_designation = $request->request->get('achat_bon_enr_designation') ; 
        $achat_bon_enr_design_id = (array)$request->request->get('achat_bon_enr_design_id') ; 
        $achat_bon_enr_quantite = $request->request->get('achat_bon_enr_quantite') ; 
        $achat_bon_enr_prix = $request->request->get('achat_bon_enr_prix') ; 
        $achat_bon_enr_reference = $request->request->get('achat_bon_enr_reference') ; 

        foreach ($achat_bon_enr_design_id as $key => $value) {
            $marchandise = $this->entityManager->getRepository(AchMarchandise::class)->find($value) ;
            
            $detail = new AchDetails() ;

            $detail->setAgence($this->agence) ;
            $detail->setBonCommande($bonCommande) ;
            $detail->setMarchandise($marchandise) ;
            $detail->setStatut($statut) ;
            $detail->setStatutGen(True) ;
            $detail->setDesignation($achat_bon_enr_designation[$key]) ;
            $detail->setReference($achat_bon_enr_reference[$key]) ;
            $detail->setQuantite($achat_bon_enr_quantite[$key]) ;
            $detail->setPrix($achat_bon_enr_prix[$key]) ;

            $this->entityManager->persist($detail) ;
            $this->entityManager->flush() ;
        }

        

        $filename = $this->filename."listBonCommande(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse([
            "type" => "green",    
            "message" => "Information enregistré avec succès",    
        ]) ;
    }

    #[Route('/achat/credit/operation', name: 'achat_credit_operation')]
    public function achatOperationCredit()
    {
        $this->appService->synchronisationAchatBonDeCommande() ;
        
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
        $this->appService->synchronisationAchatBonDeCommande() ;

        $id = $this->appService->decoderChiffre($id) ;

        $bonCommande = $this->entityManager->getRepository(AchBonCommande::class)->find($id) ;

        $totalPaiement = $this->entityManager->getRepository(AchHistoPaiement::class)->getTotalPaiement($id) ; 

        $achat = [
            "id" => $bonCommande->getId(),    
            "numero" => $bonCommande->getNumero(),    
            "fournisseur" => $bonCommande->getFournisseur()->getNom(),    
            "type" => $bonCommande->getType()->getNom(),    
            "description" => $bonCommande->getType()->getNom(),    
            "date" => $bonCommande->getDate()->format("d/m/Y"),    
            "lieu" => $bonCommande->getLieu(),    
            "lettre" => $this->appService->NumberToLetter($bonCommande->getMontant()) ,    
            "refType" =>$bonCommande->getType()->getReference() ,
            "montant" =>$bonCommande->getMontant() ,
            "montantPayee" => is_null($totalPaiement["credit"]) ? 0 : $totalPaiement["credit"] ,
        ] ;

        $filename = $this->filename."listBonCommande(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateAchListBonCommande($filename,$this->agence) ;

        $listBonCommandes = json_decode(file_get_contents($filename)) ;

        $search = [
            "id" => $id
        ] ;
        
        $listBonCommandes = $this->appService->searchData($listBonCommandes, $search) ;

        $histoPaiements = $this->entityManager->getRepository(AchHistoPaiement::class)->findBy([
            "bonCommande" => $bonCommande 
        ]) ; 

        $filename = $this->filename."marchandise(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateAchMarchandise($filename,$this->agence) ;

        $marchandises = json_decode(file_get_contents($filename)) ;

        return $this->render('achat/details.html.twig', [
            "filename" => "achat",
            "titlePage" => "Details Achat", 
            "with_foot" => true,
            "achat" => $achat,
            "listBonCommandes" => $listBonCommandes,
            "histoPaiements" => $histoPaiements,
            "marchandises" => $marchandises,
        ]);
    }

    #[Route('/achat/paiement/credit/save', name: 'achat_paiement_credit_save')]
    public function achatSavePaiementCredit(Request $request)
    {
        $ach_commande_credit_id = $request->request->get('ach_commande_credit_id') ; 
        $ach_commande_credit_date = $request->request->get('ach_commande_credit_date') ; 
        $ach_commande_credit_montant = $request->request->get('ach_commande_credit_montant') ; 

        $result = $this->appService->verificationElement([
            $ach_commande_credit_date,
            $ach_commande_credit_montant,
        ],[
            "Date Paiement",
            "Montant Payé",
            ]) ;
        
        if(!$result["allow"])
            return new JsonResponse($result) ;

        $bonCommande = $this->entityManager->getRepository(AchBonCommande::class)->find($ach_commande_credit_id) ;

        $histoPaiement = new AchHistoPaiement() ;

        $histoPaiement->setAgence($this->agence) ;
        $histoPaiement->setBonCommande($bonCommande) ;
        $histoPaiement->setDate(\DateTime::createFromFormat("d/m/Y",$ach_commande_credit_date)) ;
        $histoPaiement->setMontant($ach_commande_credit_montant) ;

        $this->entityManager->persist($histoPaiement) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."listBonCommande(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse($result) ;
    }

    #[Route('/achat/validation/total/save', name: 'achat_validation_total_save')]
    public function achatSaveValidationTotal(Request $request)
    {
        $idBon = $request->request->get('idBon') ; 
        $statutBon = $request->request->get('statutBon') ; 

        $bonCommande = $this->entityManager->getRepository(AchBonCommande::class)->find($idBon) ;

        $statutBon = $this->entityManager->getRepository(AchStatutBon::class)->findOneBy([
            "reference" => $statutBon   
        ]) ;

        $bonCommande->setStatutBon($statutBon) ;
        $bonCommande->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->flush() ;

        $filename = $this->filename."listBonCommande(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse([
            "type" => "green",    
            "message" => "Information enregistré avec succès",    
        ]) ;
    }
 
    #[Route('/achat/validation/credit/save', name: 'achat_validation_credit_save')]
    public function achatSaveValidationCredit(Request $request)
    {
        $idData = $request->request->get("idData") ;

        $statut = $this->entityManager->getRepository(AchStatut::class)->findOneBy([
            "reference" => "LVR"   
        ]) ;

        $achatDetail = $this->entityManager->getRepository(AchDetails::class)->find($idData) ;
        $achatDetail->setStatut($statut) ;
        $this->entityManager->flush() ;     

        $filename = $this->filename."listBonCommande(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;

        return new Response('<span class="text-success font-weight-bold text-uppercase">Livré</span>') ;
    }
}
