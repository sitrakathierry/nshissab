<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\CmpBanque;
use App\Entity\CmpCategorie;
use App\Entity\CmpCompte;
use App\Entity\CmpOperation;
use App\Entity\CmpType;
use App\Entity\Depense;
use App\Entity\DepLibelle;
use App\Entity\DepModePaiement;
use App\Entity\DepMotif;
use App\Entity\DepService;
use App\Entity\DepStatut;
use App\Entity\User;
use App\Service\AppService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ComptabiliteController extends AbstractController
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
        $this->filename = "files/systeme/comptabilite/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"] 
        ]) ;
    }
    
    #[Route('/comptabilite/banque/etablissement', name: 'compta_banque_etablissement')]
    public function comptaBanqueEtablissement()
    {
        $filename = $this->filename."banque(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateCmpBanque($filename, $this->agence) ;
        
        $banques = json_decode(file_get_contents($filename)) ;

        return $this->render('comptabilite/banque/etablissementBancaire.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Etablissement bancaire",
            "with_foot" => false,
            "banques" => $banques
        ]);
    }

    #[Route('/comptabilite/banque/compte/bancaire', name: 'compta_banque_compte_bancaire')]
    public function comptaBanqueCompteBancaire()
    {
        $filename = $this->filename."banque(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateCmpBanque($filename, $this->agence) ;
        
        $banques = json_decode(file_get_contents($filename)) ;

        $filename = $this->filename."compte(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateCmpCompte($filename, $this->agence) ;
        
        $comptes = json_decode(file_get_contents($filename)) ;

        return $this->render('comptabilite/banque/compteBancaire.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Compte bancaire",
            "with_foot" => false,
            "banques" => $banques,
            "comptes" => $comptes
        ]);
    }

    #[Route('/comptabilite/banque/operation/bancaire', name: 'compta_banque_operation_bancaire')]
    public function comptaBanqueOperationBancaire()
    {
        $filename = $this->filename."banque(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateCmpBanque($filename, $this->agence) ;
        
        $banques = json_decode(file_get_contents($filename)) ;

        $categories = $this->entityManager->getRepository(CmpCategorie::class)->findAll() ;
        $types = $this->entityManager->getRepository(CmpType::class)->findAll() ;

        return $this->render('comptabilite/banque/operationBancaire.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Opération bancaire",
            "with_foot" => true,
            "banques" => $banques,
            "categories" => $categories,
            "types" => $types,
        ]);
    }

    #[Route('/comptabilite/banque/mouvement/compte', name: 'compta_banque_mouvement_compte')]
    public function comptaBanqueMouvementCompte()
    {
        $filename = $this->filename."operation(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateCmpOperation($filename, $this->agence) ;
        
        $operations = json_decode(file_get_contents($filename)) ;
        
        return $this->render('comptabilite/banque/mouvementCompte.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Mouvement des comptes",
            "with_foot" => false,
            "operations" => $operations,
        ]);  
    }

    #[Route('/comptabilite/banque/etablissement/save', name: 'compta_banque_etablissement_save')]
    public function comptaBanqueSaveEtablissement(Request $request)
    {
        $cmp_banque_nom = $request->request->get("cmp_banque_nom") ;

        $result = $this->appService->verificationElement([
            $cmp_banque_nom
        ], [
            "Nom"
            ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;
        
        $banque = new CmpBanque() ;

        $banque->setAgence($this->agence) ;
        $banque->setNom($cmp_banque_nom) ;
        $banque->setStatut(True) ;
        $banque->setCreatedAt(new \DateTimeImmutable) ;
        $banque->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($banque) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."banque(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;
        
        return new JsonResponse($result) ;
        
        
    }

    #[Route('/comptabilite/banque/compte/save', name: 'compta_banque_compte_bancaire_save')]
    public function comptaBanqueSaveCompte(Request $request)
    {
        $cmp_compte_banque = $request->request->get("cmp_compte_banque") ;
        $cmp_compte_numero = $request->request->get("cmp_compte_numero") ;
        $cmp_compte_solde = $request->request->get("cmp_compte_solde") ;
        
        $result = $this->appService->verificationElement([
            $cmp_compte_banque,
            $cmp_compte_numero,
            $cmp_compte_solde,
        ], [
            "Banque",
            "Numéro de compte",
            "Solde",
            ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;
        
        $banque = $this->entityManager->getRepository(CmpBanque::class)->find($cmp_compte_banque) ;

        $compte = $this->entityManager->getRepository(CmpCompte::class)->findOneBy([
            "banque" =>  $banque,   
            "numero" =>  $cmp_compte_numero,   
            "statut" =>  True,   
        ]) ;

        $categorie = $this->entityManager->getRepository(CmpCategorie::class)->findOneBy([
            "reference" => "DEP"
        ]) ;

        $type = $this->entityManager->getRepository(CmpType::class)->findOneBy([
            "reference" => "CSH"
        ]) ;

        if(!is_null($compte))
        {
            $compte->setSolde($compte->getSolde() + floatval($cmp_compte_solde)) ;
            $this->entityManager->flush() ;
        }
        else
        {
            $compte = new CmpCompte() ;
    
            $compte->setAgence($this->agence) ;
            $compte->setBanque($banque) ;
            $compte->setNumero($cmp_compte_numero) ;
            $compte->setSolde($cmp_compte_solde) ;
            $compte->setStatut(True) ;
            $compte->setCreatedAt(new \DateTimeImmutable) ;
            $compte->setUpdatedAt(new \DateTimeImmutable) ;
    
            $this->entityManager->persist($compte) ;
            $this->entityManager->flush() ;
        }

        $operation = new CmpOperation() ;
        
        $operation->setAgence($this->agence) ;
        $operation->setBanque($banque) ;
        $operation->setCompte($compte) ;
        $operation->setCategorie($categorie) ;
        $operation->setType($type) ;
        $operation->setNumero("-") ;
        $operation->setMontant($cmp_compte_solde) ;
        $operation->setDate(\DateTime::createFromFormat('j/m/Y',date('d/m/Y'))) ;
        $operation->setPersonne("-") ;
        $operation->setStatut(True) ;
        $operation->setCreatedAt(new \DateTimeImmutable) ;
        $operation->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($operation) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."operation(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;

        $filename = $this->filename."compte(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;
        
        return new JsonResponse($result) ; 
        
    }

    #[Route('/comptabilite/banque/compte/get', name: 'compta_banque_compte_bancaire_get')]
    public function comptaBanqueGetCompte(Request $request)
    {
        $id = $request->request->get("id") ;
        if(empty($id))
        {
            return new Response('<option value="" >-</option>') ;
        }

        $banque = $this->entityManager->getRepository(CmpBanque::class)->find($id) ;

        $comptes = $this->entityManager->getRepository(CmpCompte::class)->findBy([
            "banque" => $banque    
        ]) ;

        $response = $this->renderView("comptabilite/banque/compteOptions.html.twig",[
            "comptes" => $comptes
            ]) ;
        
        return new Response($response) ;
    }

    #[Route('/comptabilite/banque/operation/save', name: 'compta_banque_operation_save')]
    public function comptaBanqueSaveOperationBancaire(Request $request)
    {
        $cmp_operation_banque = $request->request->get("cmp_operation_banque") ;
        $cmp_operation_compte = $request->request->get("cmp_operation_compte") ;
        $cmp_operation_categorie = $request->request->get("cmp_operation_categorie") ;
        $cmp_operation_numero = $request->request->get("cmp_operation_numero") ;
        $cmp_operation_type = $request->request->get("cmp_operation_type") ;
        $cmp_operation_montant = $request->request->get("cmp_operation_montant") ;
        $cmp_operation_date = $request->request->get("cmp_operation_date") ;
        $cmp_operation_personne = $request->request->get("cmp_operation_personne") ;

        $result = $this->appService->verificationElement([
            $cmp_operation_banque,
            $cmp_operation_compte,
            $cmp_operation_categorie,
            $cmp_operation_type,
            $cmp_operation_montant,
            $cmp_operation_date,
            $cmp_operation_personne,
        ], [
            "Banque",
            "Compte Bancaire",
            "Opération",
            "Type de l'opération",
            "Montant",
            "Date",
            "Personne concerné",
            ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;
        
        $compte = $this->entityManager->getRepository(CmpCompte::class)->find($cmp_operation_compte) ;
        $banque = $this->entityManager->getRepository(CmpBanque::class)->find($cmp_operation_banque) ;
        $categorie = $this->entityManager->getRepository(CmpCategorie::class)->find($cmp_operation_categorie) ;
        $type = $this->entityManager->getRepository(CmpType::class)->find($cmp_operation_type) ;

        if($categorie->getReference() == "DEP")
        {
            $compte->setSolde($compte->getSolde() + floatval($cmp_operation_montant)) ;
        }
        else if($categorie->getReference() == "RET")
        {
            $solde = $compte->getSolde() ;

            if(floatval($cmp_operation_montant) > $solde)
            {
                return new JsonResponse([
                    "type" => "orange",
                    "message" => "Désole, le montant spécifié est supérieur au solde du compte"
                    ]) ;
            }
            $compte->setSolde($compte->getSolde() - floatval($cmp_operation_montant)) ;
        }
        $this->entityManager->flush() ;

        $operation = new CmpOperation() ;
    
        $operation->setAgence($this->agence) ;
        $operation->setBanque($banque) ;
        $operation->setCompte($compte) ;
        $operation->setCategorie($categorie) ;
        $operation->setType($type) ;
        $operation->setNumero(empty($cmp_operation_numero) ? "-" : $cmp_operation_numero) ;
        $operation->setMontant($cmp_operation_montant) ;
        $operation->setDate(\DateTime::createFromFormat('j/m/Y',$cmp_operation_date)) ;
        $operation->setPersonne($cmp_operation_personne) ;
        $operation->setStatut(True) ;
        $operation->setCreatedAt(new \DateTimeImmutable) ;
        $operation->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($operation) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."operation(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;

        $filename = $this->filename."compte(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;
        return new JsonResponse($result) ;
        
    }
    
    #[Route('/comptabilite/banque/compte/solde/get', name: 'compta_banque_compte_solde_get')]
    public function comptaBanqueGetSoldeCompte(Request $request)
    {
        $id = $request->request->get("id") ;
        if(empty($id))
        {
            return new JsonResponse([
                "solde" => ""
                ]) ;
        }

        $compte = $this->entityManager->getRepository(CmpCompte::class)->find($id) ;
        
        return new JsonResponse([
            "solde" => $compte->getSolde()
            ]) ;
    }

    #[Route('/comptabilite/recette/general', name: 'compta_recette_general')]
    public function comptaRecetteGeneral(Request $request)
    {

        

        return $this->render('comptabilite/recettes/recettesGeneral.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Recettes générales",
            "with_foot" => false,
        ]);
    }

    #[Route('/comptabilite/cheque/enregistrement', name: 'compta_cheque_enregistrement')]
    public function comptaChequeEnregistrement()
    {

        

        return $this->render('comptabilite/cheque/enregistrementCheque.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Enregistrement Chèque",
            "with_foot" => true,
        ]);
    } 

    #[Route('/comptabilite/cheque/consultation', name: 'compta_cheque_consultation')]
    public function comptaChequeConsultation()
    {

        

        return $this->render('comptabilite/cheque/consultationCheque.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Consultation Chèques",
            "with_foot" => false,
        ]);
    }

    #[Route('/comptabilite/depense/declaration', name: 'compta_depense_declaration')]
    public function comptaDeclarationDepense()
    {
        $modePaiements = $this->entityManager->getRepository(DepModePaiement::class)->findAll() ;
        $motifs = $this->entityManager->getRepository(DepMotif::class)->findAll() ;

        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];

        return $this->render('comptabilite/depense/declarationDepense.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Déclaration de dépense",
            "with_foot" => true,
            "tabMois" => $tabMois,
            "modePaiements" => $modePaiements,
            "motifs" => $motifs,
        ]);
    }

    #[Route('/comptabilite/depense/depense/save', name: 'compta_declaration_depense_save')]
    public function comptaSaveDeclarationDepense(Request $request)
    {
        $dep_nom_concerne = $request->request->get("dep_nom_concerne") ;
        $dep_element = $request->request->get("dep_element") ;
        $dep_service = $request->request->get("dep_service") ;
        $dep_mode_paiement = $request->request->get("dep_mode_paiement") ;
        $dep_motif = $request->request->get("dep_motif") ;
        $dep_montant = $request->request->get("dep_montant") ;
        $dep_date_mode = $request->request->get("dep_date_mode") ;
        $dep_numero_mode = $request->request->get("dep_numero_mode") ;
        $dep_num_facture = $request->request->get("dep_num_facture") ;
        $dep_mois_facture = $request->request->get("dep_mois_facture") ;
        $dep_annee_facture = $request->request->get("dep_annee_facture") ;
        $depense_editor = $request->request->get("depense_editor") ;
        $dep_date_declaration = $request->request->get("dep_date_declaration") ;
        $add_new_service = $request->request->get("add_new_service") ;

        $result = $this->appService->verificationElement([
            $dep_nom_concerne,
            $dep_element,
            $dep_service,
            $dep_motif,
            $dep_mode_paiement,
            $dep_montant,
            $dep_num_facture,
            $dep_mois_facture,
            $dep_date_declaration,
        ], [
            "Nom Concerné",
            "Elément",
            "Service",
            "Motif",
            "Mode de Paiement",
            "Montant",
            "Numéro Facture",
            "Mois Facture",
            "Date de déclaration",
            ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;
        
        $modePaiement = $this->entityManager->getRepository(DepModePaiement::class)->find($dep_mode_paiement) ;
        $motif = $this->entityManager->getRepository(DepMotif::class)->find($dep_motif) ;
        $statut = $this->entityManager->getRepository(DepStatut::class)->findOneBy([
            "reference" => "DECL"    
        ]) ;
        
        if($add_new_service == "OUI")
        {
            $service = new DepService() ;

            $service->setAgence($this->agence) ;
            $service->setNom($dep_service) ;

            $this->entityManager->persist($service) ;
            $this->entityManager->flush() ;
        }
        else
        {
            $service = $this->entityManager->getRepository(DepMotif::class)->find($dep_service) ;
        }

        $dateMode = null ;
        $numeroMode = null ;

        if(isset($dep_date_mode))
        {
            if(!empty($dep_date_mode))
            {
                $dateMode = \DateTime::createFromFormat("d/m/Y",$dep_date_mode) ;
            }

            $numeroMode = !empty($dep_numero_mode) ? $dep_numero_mode : null ;
        }

        $depense = new Depense() ;

        $depense->setAgence($this->agence) ;
        $depense->setService($service) ;
        $depense->setMotif($motif) ;
        $depense->setModePaiement($modePaiement) ;
        $depense->setStatut($statut) ;
        $depense->setElement($dep_element) ;
        $depense->setNomConcerne($dep_nom_concerne) ;
        $depense->setDateMode($dateMode) ;
        $depense->setNumeroMode($numeroMode) ;
        $depense->setMontantDep($dep_montant) ;
        $depense->setNumFacture($dep_num_facture) ;
        $depense->setMoisFacture($dep_mois_facture) ;
        $depense->setAnneeFacture(empty($dep_annee_facture) ? date("Y") : $dep_annee_facture ) ;
        $depense->setDateDeclaration(\DateTime::createFromFormat("d/m/Y",$dep_date_declaration)) ;
        $depense->setStatutGen(True) ;
        $depense->setCreatedAt(new \DateTimeImmutable) ;
        $depense->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($depense) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."depense(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;
        
        return new JsonResponse($result) ;
        
    }

    #[Route('/comptabilite/depense/libelle/save', name: 'compta_libelle_depense_save')]
    public function compteSaveLibelleDepense(Request $request)
    {
        $nomLibelle = $request->request->get("libelle") ;

        $libelle = new DepLibelle() ;

        $libelle->setAgence($this->agence) ;
        $libelle->setNom($nomLibelle) ;

        $this->entityManager->persist($libelle) ;
        $this->entityManager->flush() ;
        

        // $filename = $this->filename."libelleDepense(agence)/".$this->nameAgence ;
        // if(file_exists($filename))
        //     unlink($filename) ;
        
        $result["id"] = $libelle->getId() ;

        return new JsonResponse($result) ;
    } 

    #[Route('/comptabilite/depense/consultation', name: 'compta_depense_consultation')]
    public function comptaConsultationDepense()
    {
        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        
        $filename = $this->filename."depense(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateDepListeDepense($filename, $this->agence) ;

        $depenses = json_decode(file_get_contents($filename)) ;

        return $this->render('comptabilite/depense/consultationDepense.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Consultation des dépenses",
            "with_foot" => false,
            "depenses" => $depenses,
        ]);
    }

    #[Route('/comptabilite/caisse/journal', name: 'compta_journal_caisse_consultation')]
    public function comptaConsultationJournalCaisse()
    {

        
        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        
        return $this->render('comptabilite/journaldeCaisse.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Journal de caisse",
            "with_foot" => false,
            "tabMois" => $tabMois,
        ]);
    }
}
