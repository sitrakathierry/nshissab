<?php

namespace App\Controller;

use App\Entity\AgdAcompte;
use App\Entity\AgdCommentaire;
use App\Entity\AgdEcheance;
use App\Entity\AgdHistoAcompte;
use App\Entity\AgdHistorique;
use App\Entity\AgdLivraison;
use App\Entity\AgdTypes;
use App\Entity\Agence;
use App\Entity\Agenda;
use App\Entity\CmdBonCommande;
use App\Entity\CrdDetails;
use App\Entity\CrdFinance;
use App\Entity\Facture;
use App\Entity\HistoHistorique;
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

class AgendaController extends AbstractController
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
        $this->filename = "files/systeme/agenda/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"],
            "agence" => $this->agence
        ]) ;
    }
    
    #[Route('/agenda/creation/{id}', name: 'agd_agenda_creation', defaults : ["id" => NULL])]
    public function agdCreationAgenda($id): Response
    {
        $agdTypes = $this->entityManager->getRepository(AgdTypes::class)->findAll() ;
        $agenda = "" ; 
        $titlePage = "Création Agenda" ;

        if(!is_null($id))
        {
            $agenda = $this->entityManager->getRepository(Agenda::class)->find($id) ;
            $titlePage = "Reporter Agenda" ;
        }

        // $code = $this->appService->encodeChiffre(2148560) ;

        // $donnee = [
        //     "code" => $code,
        //     "decode" => $this->appService->decoderChiffre($code) ,
        // ] ;

        // dd($donnee) ;

        return $this->render('agenda/creation.html.twig', [
            "filename" => "agenda",
            "titlePage" => $titlePage,
            "with_foot" => true,
            "agdTypes" => $agdTypes,
            "agenda" => $agenda
        ]);
    }

    #[Route('/agenda/consultation', name: 'agd_agenda_consultation')]
    public function agdConsultationAgenda(): Response
    {
        $filename = $this->filename."agenda(agence)/".$this->nameAgence ;
        $fileSearch = $this->filename."agenda(agence)/file_search".$this->nameAgence ;
        if (!file_exists($filename)) 
        {
            $this->appService->checkAllDateAgenda($filename) ;
            $this->appService->generateAgenda($filename,$fileSearch,$this->agence) ;
        }

        $mois = [
            1 =>  "Janvier",
            2 =>  "Février",
            3 =>  "Mars",
            4 =>  "Avril",
            5 =>  "Mai",
            6 =>  "Juin",
            7 =>  "Juillet",
            8 =>  "Août",
            9 =>  "Septembre",
            10 =>  "Octobre",
            11 =>  "Novembre",
            12 =>  "Décembre",
            ] ; 

        return $this->render('agenda/consultation.html.twig', [
            "filename" => "agenda",
            "titlePage" => "Consultation agenda",
            "with_foot" => false,
            "calendarFile" => $filename,
            "mois" => $mois 
        ]);
    }

    #[Route('/agenda/activite/save', name: 'agd_activites_save')] 
    public function agdSaveActivite(Request $request)
    {
        // dd($request->request) ;

        $agd_type = $request->request->get("agd_type") ;
        $agd_client = $request->request->get("agd_client") ;
        $agd_date = $request->request->get("agd_date") ;
        $agd_heure = $request->request->get("agd_heure") ;
        $agd_lieu = $request->request->get("agd_lieu") ;
        $agenda_editor = $request->request->get("agenda_editor") ;
        $agd_nom = $request->request->get("agd_nom") ;
        $agd_objet = $request->request->get("agd_objet") ;
        $agd_refobjet = $request->request->get("agd_refobjet") ;
        $agd_pers_interne = $request->request->get("agd_pers_interne") ;

        $data = [
            $agd_type,
            $agd_client,
            $agd_objet,
            $agd_date,
            $agd_heure,
            $agd_lieu,
            ] ;

        $dataMessage = [
            "Type Agenda",
            $agd_nom,
            $agd_refobjet,
            "Date",
            "Heure",
            "Lieu",
            ] ;
            
        $code_agenda = $request->request->get('agd_code_agenda') ; 
        if(is_null($code_agenda))
        {
            $result = $this->appService->verificationElement($data, $dataMessage) ;

            if(!$result["allow"])
                return new JsonResponse($result) ;
        }
        
        $type = $this->entityManager->getRepository(AgdTypes::class)->find($agd_type) ;

        if(!is_null($code_agenda))
        {
            $agenda = $this->entityManager->getRepository(Agenda::class)->find($code_agenda) ;

            $histoAgenda = new AgdHistorique() ;

            $histoAgenda->setAgenda($agenda) ;
            $histoAgenda->setDate($agenda->getDate()) ;
            $histoAgenda->setHeure($agenda->getHeure()) ; 
            
            $this->entityManager->persist($histoAgenda) ;
            $this->entityManager->flush() ;

            $agenda->setDate(\DateTime::createFromFormat('j/m/Y',$agd_date)) ;
            $agenda->setStatut(True) ;
            $this->entityManager->flush() ;

            $filename = $this->filename."agenda(agence)/".$this->nameAgence ;
            if(file_exists($filename))
            {
                unlink($filename) ;
            }

            return new JsonResponse([
                "type" => "green",
                "message" => "Le programme a été reporté le $agd_date",
                "redirect" => True
                ]) ;
        }

        $agenda = new Agenda() ;

        $agenda->setAgence($this->agence) ;
        $agenda->setClientNom($agd_client) ;
        $agenda->setPersInterne($agd_pers_interne) ;
        $agenda->setDate(\DateTime::createFromFormat('j/m/Y',$agd_date)) ;
        $agenda->setHeure($agd_heure) ;
        $agenda->setLieu($agd_lieu) ; 
        $agenda->setType($type) ;
        $agenda->setObjet($agd_objet) ;
        $agenda->setDescription($agenda_editor) ;
        $agenda->setStatut(True) ;
        $agenda->setCreatedAt(new \DateTimeImmutable) ;
        $agenda->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($agenda) ;
        $this->entityManager->flush() ;
        
        $filename = $this->filename."agenda(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;

        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "AGD",
            "nomModule" => "AGENDA",
            "refAction" => "CRT",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Nouvelle Agenda ; Type -> ". $type->getNom() ." ; ".$type->getDesignation()." -> ".strtoupper($agd_client),
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

        return new JsonResponse($result) ;
    }

    #[Route('/agenda/activite/date/details', name: 'agd_activites_details_date')]
    public function agdDetailsDate(Request $request)
    {
        $date = $request->request->get('date') ;

        $agendas = $this->entityManager->getRepository(Agenda::class)->findBy([
            "agence" => $this->agence,
            "date" => \DateTime::createFromFormat('Y-m-d', $date)
        ]) ;
        
        $echeances = $this->entityManager->getRepository(AgdEcheance::class)->findBy([
            "agence" => $this->agence,
            "date" => \DateTime::createFromFormat('Y-m-d', $date)
        ]) ;

        $elements = [] ;
        foreach ($echeances as $echeance) {
            $facture = $echeance->getCatTable()->getFacture() ;
            $client = $this->appService->getFactureClient($facture)["client"] ;
            
            // Personnalier le statut pour pouvoir faciliter l'affichage
            //  - En cours : 1 => ECR
            //  - En Alerte : NULL => WRN
            //  - Terminé : 0 => END

            if($echeance->isStatut())
            {
                $statut = "ECR" ;
            } else if(is_null($echeance->isStatut()))
            {
                $statut = "WRN" ;
            }
            else
            {
                $statut = "END" ;
            }

            $element = [] ;

            $element["type"] = "Crédit" ;
            $element["id"] = $echeance->getCatTable()->getId() ;
            $element["client"] = $client ;
            $element["statut"] = $statut ;
            $element["numFact"] = $facture->getNumFact() ;
            $element["montant"] = $echeance->getMontant() ;

            array_push($elements,$element) ; 
        }

        $agendaAcomptes = $this->entityManager->getRepository(AgdAcompte::class)->findBy([
            "agence" => $this->agence,
            "date" => \DateTime::createFromFormat('Y-m-d', $date)
        ]) ;
        
        $acompteArray = [] ;
        foreach ($agendaAcomptes as $agendaAcompte) {
            $facture = $agendaAcompte->getAcompte()->getFacture() ;
            $client = $this->appService->getFactureClient($facture)["client"] ;
            
            // Personnalier le statut pour pouvoir faciliter l'affichage
            //  - En cours : 1 => ECR
            //  - En Alerte : NULL => WRN
            //  - Terminé : 0 => END

            if($agendaAcompte->isStatut())
            {
                $statut = "ECR" ;
            } else if(is_null($agendaAcompte->isStatut()))
            {
                $statut = "WRN" ;
            }
            else
            {
                $statut = "END" ;
            }

            $element = [] ;

            $element["type"] = "Acompte" ;
            $element["id"] = $agendaAcompte->getAcompte()->getId() ;
            $element["client"] = $client ;
            $element["statut"] = $statut ;
            $element["numFact"] = $facture->getNumFact() ;
            $element["objet"] = $agendaAcompte->getObjet() ;

            array_push($acompteArray,$element) ; 
        }

        // DEBUT DETAIL AGENDA LIVRAISON 

        $agdLivraisons = $this->entityManager->getRepository(AgdLivraison::class)->findBy([
            "agence" => $this->agence,
            "date" => \DateTime::createFromFormat('Y-m-d', $date)
        ]) ;

        $livraisonArray = [] ;
        foreach ($agdLivraisons as $agdLivraison) {
            $livraison = $agdLivraison->getLivraison() ;

            if($livraison->getTypeSource() == "Facture")
            {
                $facture = $this->entityManager->getRepository(Facture::class)->find($livraison->getSource()) ;   
            }
            else
            {
                $bc = $this->entityManager->getRepository(CmdBonCommande::class)->find($livraison->getSource()) ; 
                $facture = $bc->getFacture() ;  
            }

            $client = $this->appService->getFactureClient($facture)["client"] ;
            
            // Personnalier le statut pour pouvoir faciliter l'affichage
            //  - En cours : 1 => ECR
            //  - En Alerte : NULL => WRN
            //  - Terminé : 0 => END

            if($agdLivraison->isStatut())
            {
                $statut = "ECR" ;
            }
            else
            {
                $statut = "END" ;
            }

            $element = [] ;

            $element["type"] = "Livraison" ;
            $element["id"] = $agdLivraison->getLivraison()->getId() ;
            $element["client"] = $client ;
            $element["statut"] = $statut ;
            $element["numLvr"] = $livraison->getNumLivraison() ;
            $element["objet"] = $agdLivraison->getObjet() ;

            array_push($livraisonArray,$element) ; 
        }

        // FIN DETAIL AGENDA LIVRAISON 

        $listEcheances = $elements ;
        $response = $this->renderView("agenda/detailsDateAganda.html.twig", [
            "agendas" => $agendas,
            "listEcheances" => $listEcheances,
            "acompteArray" => $acompteArray,
            "livraisonArray" => $livraisonArray
        ]) ;

        return new Response($response) ;
    }

    #[Route('/agenda/detail/{id}', name: 'agd_detail_agenda')]
    public function agdDetailsAgenda($id)
    {
        $agenda = $this->entityManager->getRepository(Agenda::class)->find($id) ;
        
        $commentaires = $this->entityManager->getRepository(AgdCommentaire::class)->findBy([
            "agenda" => $agenda
            ]) ;

        return $this->render('agenda/detailAgenda.html.twig', [
            "filename" => "agenda",
            "titlePage" => "Detail ",
            "with_foot" => false,
            "agenda" => $agenda,
            "commentaires" => $commentaires
        ]);
    }

    #[Route('/agenda/commentaire/save', name: 'agd_commenataire_save')]
    public function agsSaveCommentaire(Request $request)
    {
        $agd_agenda = $request->request->get("agd_agenda") ;
        $adg_content_comment = $request->request->get("adg_content_comment") ;

        $data = [
            $adg_content_comment
        ] ;

        $dataMessage = [
            "Commentaire"
        ] ;

        $result = $this->appService->verificationElement($data, $dataMessage) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;
        
        $agenda = $this->entityManager->getRepository(Agenda::class)->find($agd_agenda) ;

        $commentaire = new AgdCommentaire() ;

        $commentaire->setAgenda($agenda) ;
        $commentaire->setContenu($adg_content_comment) ;
        $commentaire->setCreatedAt(new \DateTimeImmutable) ;
        $commentaire->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($commentaire) ;
        $this->entityManager->flush() ;

        return new JsonResponse($result) ;
    }

    #[Route('/agenda/echeance/check', name: 'agd_echeance_check')]
    public function agdCheckEcheance(Request $request): Response
    {
        $id = $request->request->get('id') ;

        $echeance = $this->entityManager->getRepository(AgdEcheance::class)->find($id) ;

        $date = $echeance->getDate() ;
        $montant = $echeance->getMontant() ;

        $dateEcheance = $date->format('d/m/Y') ; 
        $dateActuel = date('d/m/Y') ;

        $compareInf = $this->appService->compareDates($dateEcheance,$dateActuel,"P") ;
        $compareSup = $this->appService->compareDates($dateEcheance,$dateActuel,"G") ;

        if($compareInf || $compareSup)
        {
            $date = \DateTime::createFromFormat('j/m/Y',$dateActuel) ;
            $echeance->setDate(\DateTime::createFromFormat('j/m/Y',$dateActuel)) ;
            $this->entityManager->flush() ;
        }

        $result = [
            "type" => "green",
            "message" => "Information enregistré avec succès",
        ] ;

        $finance = $echeance->getCatTable() ;
        $crd_paiement_montant = $montant ;
        $totalFacture = $finance->getFacture()->getTotal() ; 
        $totalPayee = $this->entityManager->getRepository(CrdDetails::class)->getFinanceTotalPayee($finance->getId()) ; 

        $ttcRestant = $totalFacture - $totalPayee["total"] ;
        $ttcRestant = $ttcRestant - $montant ; 
        
        if($ttcRestant < 0)
        {
            $crd_paiement_montant = $montant - abs($ttcRestant) ;
            $result["type"] = "green";
            $result["message"] = "Enregistrement effectué. Le montant dépasse de ".abs($ttcRestant) ; 
        }

        // DEBUT INSERTION

        $crdDetail = new CrdDetails() ;
 
        $crdDetail->setFinance($finance) ; 
        $crdDetail->setDate($date) ;
        $crdDetail->setMontant(floatval($crd_paiement_montant)) ;
        $crdDetail->setAgence($this->agence) ;

        $this->entityManager->persist($crdDetail) ;
        $this->entityManager->flush() ; 
        $refPaiement = $finance->getPaiement()->getReference() ; 

        $this->appService->updateStatutFinance($finance) ;

        // DESACTIVER L'ECHEANCE
        $echeance->setStatut(False) ;
        $this->entityManager->flush() ;

        if($refPaiement == "AC")
            $filename = "files/systeme/credit/acompte(agence)/".$this->nameAgence ;
        else
            $filename = "files/systeme/credit/credit(agence)/".$this->nameAgence ;
            
        if(file_exists($filename))
            unlink($filename) ;
        if(!file_exists($filename))
        {
            $this->appService->generateCredit($filename,$this->agence,$refPaiement) ;
        }

        $filename = "files/systeme/agenda/agenda(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;

        $filename = "files/systeme/credit/suiviCredit(agence)/".$this->nameAgence ;
        if(file_exists($filename))
            unlink($filename) ;

        // 034 47 543 35

        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "CRD",
            "nomModule" => "CREDIT",
            "refAction" => "VLD",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Validation Echéance ; Date : ".$dateActuel." ; Montant : ".$crd_paiement_montant,
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE



        return new JsonResponse($result) ;
    }

    #[Route('/agenda/acompte/save', name: 'agd_acompte_agenda_save')]
    public function agdSaveAcompteAgenda(Request $request): Response
    {
        $agd_acp_id = $request->request->get('agd_acp_id') ;
        $agd_acp_date = $request->request->get('agd_acp_date') ;
        $agd_acp_objet = $request->request->get('agd_acp_objet') ;

        $result = $this->appService->verificationElement([
            $agd_acp_date,
            $agd_acp_objet,
        ],[
            "Date",
            "Objet",
            ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $finance = $this->entityManager->getRepository(CrdFinance::class)->find($agd_acp_id) ;

        $unAgdAcompte = $this->entityManager->getRepository(AgdAcompte::class)->findOneBy([
            "acompte" => $finance
        ]) ;
        
        if(!is_null($unAgdAcompte))
        {
            $agdHistoAcompte = new AgdHistoAcompte() ;
            $agdHistoAcompte->setAgendaAcompte($unAgdAcompte) ;
            $agdHistoAcompte->setDate($unAgdAcompte->getDate()) ;
            
            $this->entityManager->persist($agdHistoAcompte) ;
            $this->entityManager->flush() ;

            $unAgdAcompte->setDate(\DateTime::createFromFormat('j/m/Y',$agd_acp_date)) ;
            $unAgdAcompte->setObjet($agd_acp_objet) ;
            $this->entityManager->flush() ;
            
            $filename = "files/systeme/agenda/agenda(agence)/".$this->nameAgence ;
            if(file_exists($filename))
            {
                unlink($filename) ;
            }

            return new JsonResponse($result) ;
        }
         
        $agd_acompte = new AgdAcompte() ;

        $agd_acompte->setAgence($this->agence) ;
        $agd_acompte->setAcompte($finance) ;
        $agd_acompte->setObjet($agd_acp_objet) ;
        $agd_acompte->setDate(\DateTime::createFromFormat('j/m/Y',$agd_acp_date)) ;
        $agd_acompte->setStatut(True) ;
        
        $this->entityManager->persist($agd_acompte) ;
        $this->entityManager->flush() ;
        
        $filename = "files/systeme/agenda/agenda(agence)/".$this->nameAgence ;
        if(file_exists($filename))
        {
            unlink($filename) ;
        }
        
        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "AGD",
            "nomModule" => "AGENDA",
            "refAction" => "CRT",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Sauvegarde Acompte sur Agenda ; Date : ".$agd_acp_date." ; Objet : ".$agd_acp_objet,
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

        return new JsonResponse($result) ;
    }

    #[Route('/agenda/echeance/delete', name: 'agenda_delete_echeance')]
    public function agdDeleteEcheance(Request $request)
    {
        $idEcheance = $request->request->get('idEcheance') ;
        $echeance = $this->entityManager->getRepository(AgdEcheance::class)->find($idEcheance) ;

        $this->entityManager->remove($echeance) ;
        $this->entityManager->flush() ;

        $filename = "files/systeme/agenda/agenda(agence)/".$this->nameAgence ;
        if(file_exists($filename))
        {
            unlink($filename) ;
        }

        // DEBUT SAUVEGARDE HISTORIQUE

        $this->entityManager->getRepository(HistoHistorique::class)
        ->insererHistorique([
            "refModule" => "CRD",
            "nomModule" => "CREDIT",
            "refAction" => "DEL",
            "user" => $this->userObj,
            "agence" => $this->agence,
            "nameAgence" => $this->nameAgence,
            "description" => "Suppression Echéance ; Date : ".$echeance->getDate()->format("d/m/Y")." ; Montant : ".$echeance->getMontant(),
        ]) ;

        // FIN SAUVEGARDE HISTORIQUE

        return new JsonResponse([
            "type" => "green",
            "message" => "Suppression effectuée",
        ]) ;
    }

    #[Route('/agenda/calendar/search', name: 'agenda_calendar_search')]
    public function agdCalendarSearch(Request $request)
    {
        $typeAgenda = $request->request->get("typeAgenda") ;
        $agd_search_mois = $request->request->get("agd_search_mois") ;
        $agd_search_annee = $request->request->get("agd_search_annee") ;
        
        $agendaResult = [] ;

        $filename = $this->filename."agenda(agence)/".$this->nameAgence ;
        $fileSearch = $this->filename."agenda(agence)/file_search".$this->nameAgence ;
        if (!file_exists($fileSearch)) 
        {
            $this->appService->checkAllDateAgenda($filename) ;
            $this->appService->generateAgenda($filename,$fileSearch,$this->agence) ;
        }

        if($typeAgenda == "ALL")
        {
            $typeAgenda = "" ;
            $agd_search_mois = "" ;
            $agd_search_annee = "" ;
        }
        else if($agd_search_mois == intval(date("m")) && $agd_search_annee == intval(date("Y")))
        {
            $agd_search_mois = "" ;
            $agd_search_annee = "" ;
        }
        

        $search = [
            "typeAgenda" => $typeAgenda,
            "mois" => $agd_search_mois,
            "annee" => $agd_search_annee,
        ] ;

        $elements = json_decode(file_get_contents($fileSearch)) ;

        $elements = $this->appService->searchData($elements,$search) ;
        
        $elements = array_values($elements) ;

        $items = $elements ;

        // Group the markup by date using array_reduce
        $mergedMarkup = array_reduce($items, function ($result, $item) {
            $date = $item->date;
            $markup = $item->markup;

            if (isset($result[$date])) {
                $result[$date]['markup'] .= $markup;
            } else {
                $result[$date] = (array)$item;
            }

            return $result;
        }, []);

        foreach ($mergedMarkup as $mark) {
            $newMarkUp = [] ;
            $newMarkUp['date'] = $mark['date'] ;
            $newMarkUp['markup'] = "<div class=\"d-flex w-100 flex-column align-items-center justify-content-center\">
                <b>[day]</b>
                <div class=\"d-flex w-100 flex-row align-items-center justify-content-center\">
                    ".$mark['markup']."
                </div>
            </div>" ;
            array_push($agendaResult,$newMarkUp) ;
        }
        
        return new JsonResponse($agendaResult) ;
    }
}
