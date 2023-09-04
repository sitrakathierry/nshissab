<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\ChkCheque;
use App\Entity\ChkStatut;
use App\Entity\ChkType;
use App\Entity\CmpBanque;
use App\Entity\CmpCategorie;
use App\Entity\CmpCompte;
use App\Entity\CmpOperation;
use App\Entity\CmpType;
use App\Entity\DepDetails;
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

    #[Route('/comptabilite/banque/update', name: 'compta_banque_update')]
    public function comptaUpdateBanque(Request $request)
    {
        $nomBanque = $request->request->get("nomBanque") ;
        $idBanque = $request->request->get("idBanque") ;

        $banque = $this->entityManager->getRepository(CmpBanque::class)->find($idBanque) ;

        if(empty($nomBanque))
        {
            $result["message"] = "Nom Banque Vide" ;
            $result["type"] = "orange" ;

            return new JsonResponse($result) ;
        }

        $banque->setNom($nomBanque) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."banque(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;

        $result["message"] = "Modification effectué" ;
        $result["type"] = "green" ;

        return new JsonResponse($result) ;
    }
    
    #[Route('/comptabilite/banque/delete', name: 'compta_banque_delete')]
    public function comptaDeleteBanque(Request $request)
    {
        $idBanque = $request->request->get('idBanque') ;

        $banque = $this->entityManager->getRepository(CmpBanque::class)->find($idBanque) ;

        $banque->setStatut(False) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."banque(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;

        $result["message"] = "Suppression effectué" ;
        $result["type"] = "green" ;

        return new JsonResponse($result) ;
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

        $cmp_numero_mode = $request->request->get("cmp_numero_mode") ;
        $cmp_editeur_mode = $request->request->get("cmp_editeur_mode") ;

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
        $operation->setNumeroMode(empty($cmp_numero_mode) ? null : $cmp_numero_mode) ;
        $operation->setEditeurMode(empty($cmp_editeur_mode) ? null : $cmp_editeur_mode) ;
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
    public function comptaRecetteGeneral()
    {
        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];

        $filename = "files/systeme/facture/facture(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateFacture($filename, $this->agence) ;

        $factures = json_decode(file_get_contents($filename)) ;

        $search = [
            "refType" => "DF",
        ] ;

        $factures = $this->appService->searchData($factures,$search) ;

        $elements = [] ;

        foreach ($factures as $facture) {
            # code...
            $item = [
                "id" => $facture->id,
                "date" => $facture->dateFacture,
                "numero" => $facture->numFact,
                "montant" => $facture->total,
                "client" => "-",
                "operation" => "Facture",
                "refOperation" => "FACTURE",
                "refJournal" => "DEBIT"
            ] ;

            array_push($elements,$item) ;
        }

        // corriger le montant dans la caisse en appliquant la remise !!
        $filename = "files/systeme/caisse/commande(agence)/".$this->nameAgence ; 
        if(!file_exists($filename))
            $this->appService->generateCaisseCommande($filename, $this->agence) ;

        $caisses = json_decode(file_get_contents($filename)) ;

        foreach ($caisses as $caisse) {
            $item = [
                "id" => $caisse->id,
                "date" => $caisse->date,
                "numero" => $caisse->numCommande,
                "montant" => $caisse->montant,
                "client" => "-",
                "operation" => "Caisse",
                "refOperation" => "CAISSE",
                "refJournal" => "DEBIT"
            ] ;

            array_push($elements,$item) ;
        }

        usort($elements, [self::class, 'comparaisonDates']);  ;

        $recettes = $elements ;

        return $this->render('comptabilite/recettes/recettesGeneral.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Recettes générales",
            "with_foot" => false,
            "recettes" => $recettes,
            "tabMois" => $tabMois,
        ]);
    }

    #[Route('/comptabilite/recette/vente', name: 'compta_recette_vente')]
    public function comptaRecetteVente()
    {


        
        return $this->render('comptabilite/recettes/recettesVente.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Recettes Ventes",
            "with_foot" => false,
        ]);
    }

    #[Route('/comptabilite/recette/prestation', name: 'compta_recette_prestation')]
    public function comptaRecettePrestation()
    {

        

        return $this->render('comptabilite/recettes/recettesPrestation.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Recettes Prestations",
            "with_foot" => false,
        ]);
    }

    #[Route('/comptabilite/cheque/enregistrement', name: 'compta_cheque_enregistrement')]
    public function comptaChequeEnregistrement()
    {
        $filename = $this->filename."banque(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateCmpBanque($filename, $this->agence) ;

        $banques = json_decode(file_get_contents($filename)) ;

        $types = $this->entityManager->getRepository(ChkType::class)->findAll() ;

        return $this->render('comptabilite/cheque/enregistrementCheque.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Enregistrement Chèque",
            "with_foot" => true,
            "banques" => $banques,
            "types" => $types,
        ]);
    } 

    #[Route('/comptabilite/cheque/consultation', name: 'compta_cheque_consultation')]
    public function comptaChequeConsultation()
    {
        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        $filename = $this->filename."cheque(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateChkCheque($filename,$this->agence) ;

        $cheques = json_decode(file_get_contents($filename)) ;

        $types = $this->entityManager->getRepository(ChkType::class)->findAll() ;

        return $this->render('comptabilite/cheque/consultationCheque.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Consultation Chèques",
            "with_foot" => false,
            "cheques" => $cheques,
            "types" => $types,
            "tabMois" => $tabMois,
        ]);
    }

    #[Route('/comptabilite/depense/declaration', name: 'compta_depense_declaration')]
    public function comptaDeclarationDepense()
    {
        $modePaiements = $this->entityManager->getRepository(DepModePaiement::class)->findAll() ;
        $motifs = $this->entityManager->getRepository(DepMotif::class)->findAll() ;
        $services = $this->entityManager->getRepository(DepService::class)->findBy([
            "agence" => $this->agence ,
            // "statut" => True   
        ]) ;
        $libelles = $this->entityManager->getRepository(DepLibelle::class)->findBy([
            "agence" => $this->agence    
        ]) ;
        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];

        return $this->render('comptabilite/depense/declarationDepense.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Déclaration de dépense",
            "with_foot" => true,
            "tabMois" => $tabMois,
            "modePaiements" => $modePaiements,
            "motifs" => $motifs,
            "services" => $services,
            "libelles" => $libelles,
        ]);
    }

    #[Route('/comptabilite/depense/details/{id}', name: 'compta_depense_details')]
    public function comptaDetailsDepense($id)
    {
        $id = $this->appService->decoderChiffre($id) ;
        $depense = $this->entityManager->getRepository(Depense::class)->find($id) ;

        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        
        $libelleArrayMode = [
            "ESP" => [
                "numero" => "-",
                "editeur" => "-",
                "date" => "-",
            ],
            "CHK" => [
              "numero" => "N° Chèque",
              "editeur" => "Nom du Chèquier",
              "date" => "Date Chèque",
            ],
            "VRM" => [
              "numero" => "N° Virement",
              "editeur" => "Virement émit par",
              "date" => "Date Virement",
            ],
            "CBR" => [
              "numero" => "Reference Carte",
              "editeur" => "Editeur de la Carte",
              "date" => "Date Paiement",
            ],
            "MOB" => [
              "numero" => "Reference de Transfert",
              "editeur" => "Editeur de Transfert",
              "date" => "Date Transfert",
            ],
        ] ;
        
        $refMode = $depense->getModePaiement()->getReference() ;

        $element = [
            "dateDeclaration" => $depense->getDateDeclaration()->format("d/m/Y"),
            "element" => $depense->getElement(),
            "beneficiaire" => $depense->getNomConcerne(),
            "numFacture" => $depense->getNumFacture(),
            "service" => $depense->getService()->getNom(),
            "motif" => $depense->getMotif()->getNom(),
            "modePaiement" => $depense->getModePaiement()->getNom(),
            "refMode" => $refMode,
            "numeroMode" => is_null($depense->getNumeroMode()) ? "-" : $depense->getNumeroMode(),
            "editeurMode" => is_null($depense->getEditeurMode()) ? "-" : $depense->getEditeurMode(),
            "dateMode" => is_null($depense->getDateMode()) ? "-" : $depense->getDateMode()->format("d/m/Y"),
            "lblNumeroMode" => $libelleArrayMode[$refMode]["numero"],
            "lblEditeurMode" => $libelleArrayMode[$refMode]["editeur"],
            "lblDateMode" => $libelleArrayMode[$refMode]["date"],
            "montant" => $depense->getMontantDep(),
            "moisFacture" => $tabMois[$depense->getMoisFacture() - 1] ,
            "anneeFacture" => $depense->getAnneeFacture(),
            "description" => $depense->getDescription(),
            "statut" => $depense->getStatut()->getNom(),
        ] ;

        $details = $this->entityManager->getRepository(DepDetails::class)->findBy([
             "depense" => $depense
        ]) ;

        return $this->render('comptabilite/depense/detailsDepense.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Détails Dépense ",
            "with_foot" => true,
            "depense" => $element,
            "details" => $details,
        ]);
    }

    #[Route('/comptabilite/depense/save', name: 'compta_declaration_depense_save')]
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
        $dep_editeur_mode = $request->request->get("dep_editeur_mode") ;
        $result = $this->appService->verificationElement([
            $dep_nom_concerne,
            $dep_element,
            $dep_service,
            $dep_motif,
            $dep_mode_paiement,
            $dep_montant,
            // $dep_num_facture,
            // $dep_mois_facture,
            $dep_date_declaration,
        ], [
            "Nom Concerné",
            "Elément",
            "Service",
            "Motif",
            "Mode de Paiement",
            "Montant",
            // "Numéro Facture",
            // "Mois Facture",
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
            $service = $this->entityManager->getRepository(DepService::class)->find($dep_service) ;
        }

        $dateMode = null ;
        $numeroMode = null ;
        $editeurMode = null ;

        if(isset($dep_date_mode))
        {
            if(!empty($dep_date_mode))
            {
                $dateMode = \DateTime::createFromFormat("d/m/Y",$dep_date_mode) ;
            }

            $numeroMode = !empty($dep_numero_mode) ? $dep_numero_mode : null ;
            $editeurMode = !empty($dep_editeur_mode) ? $dep_editeur_mode : null ;
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
        $depense->setEditeurMode($editeurMode) ;
        $depense->setMontantDep($dep_montant) ;
        $depense->setNumFacture(empty($dep_num_facture) ? null : $dep_num_facture) ;
        $depense->setMoisFacture(empty($dep_mois_facture) ? null : $dep_mois_facture) ;
        $depense->setAnneeFacture(empty($dep_annee_facture) ? date("Y") : $dep_annee_facture ) ;
        $depense->setDateDeclaration(\DateTime::createFromFormat("d/m/Y",$dep_date_declaration)) ;
        $depense->setStatutGen(True) ;
        $depense->setDescription($depense_editor) ;
        $depense->setCreatedAt(new \DateTimeImmutable) ;
        $depense->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($depense) ;
        $this->entityManager->flush() ;

        $dep_item_id_libelle = (array)$request->request->get("dep_item_id_libelle") ;
        $dep_item_designation = $request->request->get("dep_item_designation") ;
        $dep_item_quantite = $request->request->get("dep_item_quantite") ;
        $dep_item_prix = $request->request->get("dep_item_prix") ;

        foreach ($dep_item_id_libelle as $key => $value) {
            $libelle = $this->entityManager->getRepository(DepLibelle::class)->find($dep_item_id_libelle[$key]) ;

            $detail = new DepDetails() ;

            $detail->setAgence($this->agence) ;
            $detail->setDepense($depense) ;
            $detail->setLibelle($libelle) ;
            $detail->setDesignation($dep_item_designation[$key]) ;
            $detail->setQuantite($dep_item_quantite[$key]) ;
            $detail->setPrixUnitaire($dep_item_prix[$key]) ;
            $detail->setStatutGen(True) ;

            $this->entityManager->persist($detail) ;
            $this->entityManager->flush() ;
        }

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

        $search = [
            "anneeDepense" => date("Y"),
        ] ;

        $depenses = $this->appService->searchData($depenses,$search) ;

        $items = [] ;
        foreach ($depenses as $depense) {
            $key = $tabMois[intval($depense->moisDepense) - 1]." ".$depense->anneeDepense ;

            if(!isset($items[$key]))
            {
                $items[$key] = [] ;
                $items[$key]["montant"] = $depense->montant ;
                $items[$key]["statMotif"] = [] ;
                $items[$key]["statMotif"][$depense->refMotif] = 1 ;
                $items[$key]["statPaiement"] = [] ;
                $items[$key]["statPaiement"][$depense->refMode] = 1 ;
                $items[$key]["nbElement"] = 1 ;
                $items[$key]["detail"] = [] ;
                $items[$key]["detail"][] = $depense ;
            }
            else
            {
                if(isset($items[$key]["statPaiement"][$depense->refMode]))
                    $items[$key]["statPaiement"][$depense->refMode] += 1 ;
                else
                    $items[$key]["statPaiement"][$depense->refMode] = 1 ;

                if(isset($items[$key]["statMotif"][$depense->refMotif]))
                    $items[$key]["statMotif"][$depense->refMotif] += 1 ;
                else
                    $items[$key]["statMotif"][$depense->refMotif] = 1 ;

                $items[$key]["nbElement"] += 1 ;
                $items[$key]["montant"] += $depense->montant ;
                $items[$key]["detail"][] = $depense ;
            }
        }
        
        return $this->render('comptabilite/depense/consultationDepense.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Consultation des dépenses",
            "with_foot" => false,
            "depenses" => $items, 
            "tabMois" => $tabMois, 
        ]);
    }

    #[Route('/comptabilite/depense/service/get', name: 'compta_dep_content_service_get')]
    public function comptaGetContentService(Request $request)
    {
        $type = $request->request->get("type") ;

        if($type == "NEW")
        {
            $response = $this->renderView("comptabilite/depense/service/getNewService.html.twig",[]) ;
        }
        else
        {
            $services = $this->entityManager->getRepository(DepService::class)->findBy([
                "agence" => $this->agence    
            ]) ;
            $response = $this->renderView("comptabilite/depense/service/getExistingService.html.twig",[
                "services" => $services
            ]) ;
        }

        return new Response($response) ;
    }
    
    #[Route('/comptabilite/depense/libelle/get', name: 'compta_dep_content_libelle_get')]
    public function comptaGetContentLibelle(Request $request)
    {
        $type = $request->request->get("type") ;

        if($type == "NEW")
        {
            $response = $this->renderView("comptabilite/depense/libelle/getNewLibelle.html.twig",[]) ;
        }
        else
        {
            $libelles = $this->entityManager->getRepository(DepLibelle::class)->findBy([
                "agence" => $this->agence    
            ]) ;
            $response = $this->renderView("comptabilite/depense/libelle/getExistingLibelle.html.twig",[
                "libelles" => $libelles
            ]) ;
        }

        return new Response($response) ;
    }

    public static function comparaisonDates($a, $b) {
        $dateA = \DateTime::createFromFormat('d/m/Y', $a['date']);
        $dateB = \DateTime::createFromFormat('d/m/Y', $b['date']);
        return $dateB <=> $dateA;
    }

    #[Route('/comptabilite/caisse/journal', name: 'compta_journal_caisse_consultation')]
    public function comptaConsultationJournalCaisse()
    {
        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        
        /*
            - refOperation : ACHAT, FACTURE, DEPENSE, CAISSE
        */

        $filename = "files/systeme/achat/commande(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateAchCommande($filename, $this->agence) ;

        $achats = json_decode(file_get_contents($filename)) ;

        $elements = [] ;

        foreach ($achats as $achat) {
            # code...
            $item = [
                "id" => $achat->id,
                "date" => $achat->date,
                "montant" => $achat->montant,
                "operation" => $achat->operation,
                "refOperation" => $achat->refOperation,
                "refJournal" => $achat->refJournal
            ] ;

            array_push($elements,$item) ;
        }

        $filename = $this->filename."depense(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateDepListeDepense($filename, $this->agence) ;

        $depenses = json_decode(file_get_contents($filename)) ;

        foreach ($depenses as $depense) {
            # code...
            $item = [
                "id" => $depense->id,
                "date" => $depense->dateDeclaration,
                "montant" => $depense->montant,
                "operation" => "Dépense",
                "refOperation" => "DEPENSE",
                "refJournal" => "CREDIT"
            ] ;

            array_push($elements,$item) ;
        }

        $filename = "files/systeme/facture/facture(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateFacture($filename, $this->agence) ;

        $factures = json_decode(file_get_contents($filename)) ;

        $search = [
            "refType" => "DF",
        ] ;

        $factures = $this->appService->searchData($factures,$search) ;

        foreach ($factures as $facture) {
            # code...
            $item = [
                "id" => $facture->id,
                "date" => $facture->dateFacture,
                "montant" => $facture->total,
                "operation" => "Facture",
                "refOperation" => "FACTURE",
                "refJournal" => "DEBIT"
            ] ;

            array_push($elements,$item) ;
        }

        // corriger le montant dans la caisse en appliquant la remise !!
        $filename = "files/systeme/caisse/commande(agence)/".$this->nameAgence ; 
        if(!file_exists($filename))
            $this->appService->generateCaisseCommande($filename, $this->agence) ;

        $caisses = json_decode(file_get_contents($filename)) ;

        foreach ($caisses as $caisse) {
            $item = [
                "id" => $caisse->id,
                "date" => $caisse->date,
                "montant" => $caisse->montant,
                "operation" => "Caisse",
                "refOperation" => "CAISSE",
                "refJournal" => "DEBIT"
            ] ;

            array_push($elements,$item) ;
        }

        usort($elements, [self::class, 'comparaisonDates']);  ;

        $journals = $elements ;

        $items = [] ;
        foreach ($journals as $journal) {
            $mois = intval(explode("/",$journal["date"])[1]);
            $annee = intval(explode("/",$journal["date"])[2]);
            $key = $tabMois[$mois - 1]." ".$annee ;

            if(!isset($items[$key]))
            {
                $items[$key] = [] ;
                $items[$key]["montant"] = $journal["montant"] ;
                $items[$key][$journal["refJournal"]] = 1 ;
                $items[$key][$journal["refOperation"]] = 1 ;
                $items[$key]["nbElement"] = 1 ;
                $items[$key]["detail"] = [] ;
                $items[$key]["detail"][] = $journal ;
            }
            else
            {
                if(isset($items[$key][$journal["refJournal"]]))
                    $items[$key][$journal["refJournal"]] += 1 ;
                else
                    $items[$key][$journal["refJournal"]] = 1 ;

                if(isset($items[$key][$journal["refOperation"]]))
                    $items[$key][$journal["refOperation"]] += 1 ;
                else
                    $items[$key][$journal["refOperation"]] = 1 ;

                $items[$key]["nbElement"] += 1 ;
                $items[$key]["montant"] += $journal["montant"] ;
                $items[$key]["detail"][] = $journal ;
            }
        }

        return $this->render('comptabilite/journaldeCaisse.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Journal de caisse",
            "with_foot" => false,
            "tabMois" => $tabMois,
            "journals" => $items,
        ]);
    }

    #[Route('/comptabilite/depense/search', name: 'compta_depense_search')]
    public function comptaSearchDepense(Request $request)
    {
        $currentDate = $request->request->get('currentDate') ;
        $dateDeclaration = $request->request->get('dateDeclaration') ;
        $dateDebut = $request->request->get('dateDebut') ;
        $dateFin = $request->request->get('dateFin') ;
        $anneeDepense = $request->request->get('anneeDepense') ;
        $moisDepense = $request->request->get('moisDepense') ;
        $affichage = $request->request->get('affichage') ;

        if($affichage == "JOUR")
        {
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $anneeDepense = "" ;
            $moisDepense = "" ;
        }
        else if($affichage == "SPEC")
        {
            $currentDate = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $anneeDepense = "" ;
            $moisDepense = "" ;
        }
        else if($affichage == "LIMIT")
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $anneeDepense = "" ;
            $moisDepense = "" ;
        }
        else if($affichage == "MOIS")
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
        }
        else if($affichage == "ANNEE")
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $moisDepense = "" ;
        }
        else
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $anneeDepense = "" ;
            $moisDepense = "" ;
        }

        $search = [
            "currentDate" => $currentDate,
            "dateDeclaration" => $dateDeclaration,
            "dateDebut" => $dateDebut,
            "dateFin" => $dateFin,
            "anneeDepense" => $anneeDepense,
            "moisDepense" => $moisDepense,
        ] ;

        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        
        $filename = $this->filename."depense(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateDepListeDepense($filename, $this->agence) ;

        $depenses = json_decode(file_get_contents($filename)) ;

        $depenses = $this->appService->searchData($depenses,$search) ;

        $items = [] ;
        foreach ($depenses as $depense) {
            $key = $tabMois[intval($depense->moisDepense) - 1]." ".$depense->anneeDepense ;

            if(!isset($items[$key]))
            {
                $items[$key] = [] ;
                $items[$key]["montant"] = $depense->montant ;
                $items[$key]["statMotif"] = [] ;
                $items[$key]["statMotif"][$depense->refMotif] = 1 ;
                $items[$key]["statPaiement"] = [] ;
                $items[$key]["statPaiement"][$depense->refMode] = 1 ;
                $items[$key]["nbElement"] = 1 ;
                $items[$key]["detail"] = [] ;
                $items[$key]["detail"][] = $depense ;
            }
            else
            {
                if(isset($items[$key]["statPaiement"][$depense->refMode]))
                    $items[$key]["statPaiement"][$depense->refMode] += 1 ;
                else
                    $items[$key]["statPaiement"][$depense->refMode] = 1 ;

                if(isset($items[$key]["statMotif"][$depense->refMotif]))
                    $items[$key]["statMotif"][$depense->refMotif] += 1 ;
                else
                    $items[$key]["statMotif"][$depense->refMotif] = 1 ;

                $items[$key]["nbElement"] += 1 ;
                $items[$key]["montant"] += $depense->montant ;
                $items[$key]["detail"][] = $depense ;
            }
        }

        if(!empty($items))
        {
            $response = $this->renderView("comptabilite/depense/searchDepense.html.twig", [
                "depenses" => $items
            ]) ;
        }
        else
        {
            $response = '<div class="w-100 p-4"><div class="alert alert-sm alert-warning">Désolé, aucun élément trouvé</div></div>' ;
        }

        return new Response($response) ; 
    }

    #[Route('/comptabilite/journal/search', name: 'compta_journal_search')]
    public function comptaSearchJournal(Request $request)
    {
        $currentDate = $request->request->get('currentDate') ;
        $dateDeclaration = $request->request->get('dateDeclaration') ;
        $dateDebut = $request->request->get('dateDebut') ;
        $dateFin = $request->request->get('dateFin') ;
        $anneeDepense = $request->request->get('anneeDepense') ;
        $moisDepense = $request->request->get('moisDepense') ;
        $affichage = $request->request->get('affichage') ;

        if($affichage == "JOUR")
        {
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $anneeDepense = "" ;
            $moisDepense = "" ;
        }
        else if($affichage == "SPEC")
        {
            $currentDate = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $anneeDepense = "" ;
            $moisDepense = "" ;
        }
        else if($affichage == "LIMIT")
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $anneeDepense = "" ;
            $moisDepense = "" ;
        }
        else if($affichage == "MOIS")
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
        }
        else if($affichage == "ANNEE")
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $moisDepense = "" ;
        }
        else
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $anneeDepense = "" ;
            $moisDepense = "" ;
        }

        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        
        $filename = "files/systeme/achat/commande(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateAchCommande($filename, $this->agence) ;

        $achats = json_decode(file_get_contents($filename)) ;

        $elements = [] ;

        foreach ($achats as $achat) {
            # code...
            $item = [
                "id" => $achat->id,
                "date" => $achat->date,
                "currentDate" => $achat->date,
                "dateDeclaration" => $achat->date,
                "dateDebut" => $achat->date,
                "dateFin" => $achat->date,
                "anneeDepense" => explode("/",$achat->date)[2],
                "moisDepense" => explode("/",$achat->date)[1],
                "montant" => $achat->montant,
                "operation" => $achat->operation,
                "refOperation" => $achat->refOperation,
                "refJournal" => $achat->refJournal
            ] ;

            array_push($elements,$item) ;
        }

        $filename = $this->filename."depense(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateDepListeDepense($filename, $this->agence) ;

        $depenses = json_decode(file_get_contents($filename)) ;

        foreach ($depenses as $depense) {
            # code...
            $item = [
                "id" => $depense->id,
                "date" => $depense->dateDeclaration,
                "currentDate" => $depense->dateDeclaration,
                "dateDeclaration" => $depense->dateDeclaration,
                "dateDebut" => $depense->dateDeclaration,
                "dateFin" => $depense->dateDeclaration,
                "anneeDepense" => explode("/",$depense->dateDeclaration)[2],
                "moisDepense" => explode("/",$depense->dateDeclaration)[1],
                "montant" => $depense->montant,
                "operation" => "Dépense",
                "refOperation" => "DEPENSE",
                "refJournal" => "CREDIT"
            ] ;

            array_push($elements,$item) ;
        }

        $filename = "files/systeme/facture/facture(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateFacture($filename, $this->agence) ;

        $factures = json_decode(file_get_contents($filename)) ;

        $search = [
            "refType" => "DF",
        ] ;

        $factures = $this->appService->searchData($factures,$search) ;

        foreach ($factures as $facture) {
            # code...
            $item = [
                "id" => $facture->id,
                "date" => $facture->dateFacture,
                "currentDate" => $facture->dateFacture,
                "dateDeclaration" => $facture->dateFacture,
                "dateDebut" => $facture->dateFacture,
                "dateFin" => $facture->dateFacture,
                "anneeDepense" => explode("/",$facture->dateFacture)[2],
                "moisDepense" => explode("/",$facture->dateFacture)[1],
                "montant" => $facture->total,
                "operation" => "Facture",
                "refOperation" => "FACTURE",
                "refJournal" => "DEBIT"
            ] ;

            array_push($elements,$item) ;
        }

        // corriger le montant dans la caisse en appliquant la remise !!
        $filename = "files/systeme/caisse/commande(agence)/".$this->nameAgence ; 
        if(!file_exists($filename))
            $this->appService->generateCaisseCommande($filename, $this->agence) ;

        $caisses = json_decode(file_get_contents($filename)) ;

        foreach ($caisses as $caisse) {
            $item = [
                "id" => $caisse->id,
                "date" => $caisse->date,
                "currentDate" => $caisse->date,
                "dateDeclaration" => $caisse->date,
                "dateDebut" => $caisse->date,
                "dateFin" => $caisse->date,
                "anneeDepense" => explode("/",$caisse->date)[2],
                "moisDepense" => explode("/",$caisse->date)[1],
                "montant" => $caisse->montant,
                "operation" => "Caisse",
                "refOperation" => "CAISSE",
                "refJournal" => "DEBIT"
            ] ;

            array_push($elements,$item) ;
        }

        usort($elements, [self::class, 'comparaisonDates']);  ;

        $journals = $elements ;

        $search = [
            "currentDate" => $currentDate,
            "dateDeclaration" => $dateDeclaration,
            "dateDebut" => $dateDebut,
            "dateFin" => $dateFin,
            "anneeDepense" => $anneeDepense,
            "moisDepense" => $moisDepense,
        ] ;

        $datas = [] ;

        foreach ($journals as $data) {
            $datas[] = (object)$data ;
        }

        $journals = $this->appService->searchData($datas,$search) ;

        $datas = [] ;

        foreach ($journals as $data) {
            $datas[] = (array)$data ;
        }
        $journals = $datas ;

        $items = [] ;
        foreach ($journals as $journal) {
            $mois = intval(explode("/",$journal["date"])[1]);
            $annee = intval(explode("/",$journal["date"])[2]);
            $key = $tabMois[$mois - 1]." ".$annee ;

            if(!isset($items[$key]))
            {
                $items[$key] = [] ;
                $items[$key]["montant"] = $journal["montant"] ;
                $items[$key][$journal["refJournal"]] = 1 ;
                $items[$key][$journal["refOperation"]] = 1 ;
                $items[$key]["nbElement"] = 1 ;
                $items[$key]["detail"] = [] ;
                $items[$key]["detail"][] = $journal ;
            }
            else
            {
                if(isset($items[$key][$journal["refJournal"]]))
                    $items[$key][$journal["refJournal"]] += 1 ;
                else
                    $items[$key][$journal["refJournal"]] = 1 ;

                if(isset($items[$key][$journal["refOperation"]]))
                    $items[$key][$journal["refOperation"]] += 1 ;
                else
                    $items[$key][$journal["refOperation"]] = 1 ;

                $items[$key]["nbElement"] += 1 ;
                $items[$key]["montant"] += $journal["montant"] ;
                $items[$key]["detail"][] = $journal ;
            }
        }

        if(!empty($items))
        {
            $response = $this->renderView("comptabilite/searchJournal.html.twig", [
                "journals" => $items
            ]) ;
        }
        else
        {
            $response = '<div class="w-100 p-4"><div class="alert alert-sm alert-warning">Désolé, aucun élément trouvé</div></div>' ;
        }

        return new Response($response) ; 
    }
    
    #[Route('/comptabilite/cheque/save', name: 'compta_cheque_save')]
    public function comptaSaveCheque(Request $request)
    {
        $chk_nom_chequier = $request->request->get("chk_nom_chequier") ;
        $chk_banque = $request->request->get("chk_banque") ;
        $chk_type = $request->request->get("chk_type") ;
        $chk_numCheque = $request->request->get("chk_numCheque") ;
        $chk_date_cheque = $request->request->get("chk_date_cheque") ;
        $chk_montant = $request->request->get("chk_montant") ;
        $cheque_editor = $request->request->get("cheque_editor") ;
        $chk_date_declaration = $request->request->get("chk_date_declaration") ;

        $result = $this->appService->verificationElement([
            $chk_nom_chequier,
            $chk_banque,
            $chk_type,
            $chk_numCheque,
            $chk_date_cheque,
            $chk_montant,
            $chk_date_declaration,
        ], [
            "Nom Chèquier",
            "Banque",
            "Type",
            "Numéro de Chèque",
            "Date de Chèque",
            "Montant",
            "Date déclaration",
            ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $banque = $this->entityManager->getRepository(CmpBanque::class)->find($chk_banque) ;
        $type = $this->entityManager->getRepository(ChkType::class)->find($chk_type) ;
        $statut = $this->entityManager->getRepository(ChkStatut::class)->findOneBy([
            "reference" =>  "DECLARE"
        ]) ;

        $cheque = new ChkCheque() ;

        $cheque->setAgence($this->agence) ;
        $cheque->setBanque($banque) ;
        $cheque->setType($type) ;
        $cheque->setNomChequier($chk_nom_chequier) ;
        $cheque->setNumCheque($chk_numCheque) ;
        $cheque->setDateCheque(\DateTime::createFromFormat("d/m/Y",$chk_date_cheque)) ;
        $cheque->setMontant($chk_montant) ;
        $cheque->setDateDeclaration(\DateTime::createFromFormat("d/m/Y",$chk_date_declaration)) ;
        $cheque->setDescription($cheque_editor) ;
        $cheque->setStatut($statut) ;
        $cheque->setStatutGen(True) ; 
        $cheque->setCreatedAt(new \DateTimeImmutable) ;
        $cheque->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($cheque) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."cheque(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse($result) ;
    }

    #[Route('/comptabilite/cheque/details/{id}', name: 'compta_cheque_details')]
    public function comptaDetailsCheque($id)
    {
        $id = $this->appService->decoderChiffre($id) ;
        $cheque = $this->entityManager->getRepository(ChkCheque::class)->find($id) ;

        $element = [
            "id" => $cheque->getId(),
            "nomChequier" => $cheque->getNomChequier(),
            "banque" => $cheque->getBanque()->getNom(),
            "type" => $cheque->getType()->getNom(),
            "numCheque" => $cheque->getNumCheque(),
            "dateCheque" => $cheque->getDateCheque()->format("d/m/Y"),
            "montant" => $cheque->getMontant(),
            "desciprtion" => $cheque->getDescription(),
            "date" => $cheque->getDateDeclaration()->format("d/m/Y"),
            "statut" => $cheque->getStatut()->getNom(),
            "refStatut" => $cheque->getStatut()->getReference(),
        ] ;

        return $this->render('comptabilite/cheque/detailsCheque.html.twig', [
            "filename" => "comptabilite",
            "titlePage" => "Détails Chèque",
            "with_foot" => true,
            "cheque" => $element,
        ]);
    }

    #[Route('/comptabilite/cheque/validation', name: 'compta_cheque_validation')]
    public function comptaValidationCheque(Request $request)
    {
        $id = $request->request->get("id") ;
        $cheque = $this->entityManager->getRepository(ChkCheque::class)->find($id) ;

        $statut = $this->entityManager->getRepository(ChkStatut::class)->findOneBy([
            "reference" =>  "VALIDE"
        ]) ;

        $cheque->setStatut($statut);
        $cheque->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->flush() ;

        $filename = $this->filename."cheque(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse([
            "type" => "green",
            "message" => "Validation effectué",
        ]) ;
    }

    #[Route('/comptabilite/cheque/search', name: 'compta_cheque_search')]
    public function comptaSearchCheque(Request $request)
    {
        $idType = $request->request->get('idType') ;
        $currentDate = $request->request->get('currentDate') ;
        $dateDeclaration = $request->request->get('dateDeclaration') ;
        $dateDebut = $request->request->get('dateDebut') ;
        $dateFin = $request->request->get('dateFin') ;
        $annee = $request->request->get('annee') ;
        $mois = $request->request->get('mois') ;
        $affichage = $request->request->get('affichage') ;

        if($affichage == "JOUR")
        {
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $annee = "" ;
            $mois = "" ;
        }
        else if($affichage == "SPEC")
        {
            $currentDate = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $annee = "" ;
            $mois = "" ;
        }
        else if($affichage == "LIMIT")
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $annee = "" ;
            $mois = "" ;
        }
        else if($affichage == "MOIS")
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
        }
        else if($affichage == "ANNEE")
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $mois = "" ;
        }
        else
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $annee = "" ;
            $mois = "" ;
        }

        $search = [
            "idType" => $idType,
            "currentDate" => $currentDate,
            "dateDeclaration" => $dateDeclaration,
            "dateDebut" => $dateDebut,
            "dateFin" => $dateFin,
            "annee" => $annee,
            "mois" => $mois,
        ] ;

        $filename = $this->filename."cheque(agence)/".$this->nameAgence ;
        if(!file_exists($filename))
            $this->appService->generateChkCheque($filename,$this->agence) ;

        $cheques = json_decode(file_get_contents($filename)) ;

        $cheques = $this->appService->searchData($cheques,$search) ;

        if(!empty($cheques))
        {
            $response = $this->renderView("comptabilite/cheque/searchCheque.html.twig", [
                "cheques" => $cheques
            ]) ;
        }
        else
        {
            $response = '<tr><td colspan="9" class="p-2"><div class="alert w-100 alert-sm alert-warning">Désolé, aucun élément trouvé</div></td></tr>' ;
        }

        return new Response($response) ; 
    }

    #[Route('/comptabilite/recette/search', name: 'compta_recette_search')]
    public function comptaSearchRecette(Request $request)
    {
        $currentDate = $request->request->get('currentDate') ;
        $dateDeclaration = $request->request->get('dateDeclaration') ;
        $dateDebut = $request->request->get('dateDebut') ;
        $dateFin = $request->request->get('dateFin') ;
        $annee = $request->request->get('annee') ;
        $mois = $request->request->get('mois') ;
        $affichage = $request->request->get('affichage') ;

        if($affichage == "JOUR")
        {
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $annee = "" ;
            $mois = "" ;
        }
        else if($affichage == "SPEC")
        {
            $currentDate = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $annee = "" ;
            $mois = "" ;
        }
        else if($affichage == "LIMIT")
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $annee = "" ;
            $mois = "" ;
        }
        else if($affichage == "MOIS")
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
        }
        else if($affichage == "ANNEE")
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $mois = "" ;
        }
        else
        {
            $currentDate = "" ;
            $dateDeclaration = "" ;
            $dateDebut = "" ;
            $dateFin = "" ;
            $annee = "" ;
            $mois = "" ;
        }

        $filename = "files/systeme/facture/facture(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateFacture($filename, $this->agence) ;

        $factures = json_decode(file_get_contents($filename)) ;

        $search = [
            "refType" => "DF",
        ] ;

        $factures = $this->appService->searchData($factures,$search) ;

        $elements = [] ;
        foreach ($factures as $facture) {
            # code...
            $item = [
                "id" => $facture->id,
                "date" => $facture->dateFacture,
                "currentDate" => $facture->dateFacture,
                "dateDeclaration" => $facture->dateFacture,
                "dateDebut" => $facture->dateFacture,
                "dateFin" => $facture->dateFacture,
                "annee" => explode("/",$facture->dateFacture)[2],
                "mois" => explode("/",$facture->dateFacture)[1],
                "numero" => $facture->numFact,
                "montant" => $facture->total,
                "client" => "-",
                "operation" => "Facture",
                "refOperation" => "FACTURE",
                "refJournal" => "DEBIT"
            ] ;

            array_push($elements,$item) ;
        }

        // corriger le montant dans la caisse en appliquant la remise !!
        $filename = "files/systeme/caisse/commande(agence)/".$this->nameAgence ; 
        if(!file_exists($filename))
            $this->appService->generateCaisseCommande($filename, $this->agence) ;

        $caisses = json_decode(file_get_contents($filename)) ;

        foreach ($caisses as $caisse) {
            $item = [
                "id" => $caisse->id,
                "date" => $caisse->date,
                "currentDate" => $caisse->date,
                "dateDeclaration" => $caisse->date,
                "dateDebut" => $caisse->date,
                "dateFin" => $caisse->date,
                "annee" => explode("/",$caisse->date)[2],
                "mois" => explode("/",$caisse->date)[1],
                "numero" => $caisse->numCommande,
                "montant" => $caisse->montant,
                "client" => "-",
                "operation" => "Caisse",
                "refOperation" => "CAISSE",
                "refJournal" => "DEBIT"
            ] ;

            array_push($elements,$item) ;
        }

        usort($elements, [self::class, 'comparaisonDates']);  ;

        $recettes = $elements ;

        $search = [
            "currentDate" => $currentDate,
            "dateDeclaration" => $dateDeclaration,
            "dateDebut" => $dateDebut,
            "dateFin" => $dateFin,
            "annee" => $annee,
            "mois" => $mois,
        ] ;

        $records = [] ;

        foreach ($recettes as $recette) {
            $records[] = (object)$recette ;
        }

        $recettes = $this->appService->searchData($records,$search) ;

        if(!empty($recettes))
        {
            $response = $this->renderView("comptabilite/recettes/searchRecette.html.twig", [
                "recettes" => $recettes
            ]) ;
        }
        else
        {
            $response = '<tr><td colspan="5" class="p-2"><div class="alert w-100 alert-sm alert-warning">Désolé, aucun élément trouvé</div></td></tr>' ;
        }

        return new Response($response) ; 
    }
    
}
