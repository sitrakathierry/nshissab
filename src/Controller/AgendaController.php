<?php

namespace App\Controller;

use App\Entity\AgdCommentaire;
use App\Entity\AgdEcheance;
use App\Entity\AgdTypes;
use App\Entity\Agence;
use App\Entity\Agenda;
use App\Entity\CrdDetails;
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
            "username" => $this->user["username"] 
        ]) ;
    }
    
    #[Route('/agenda/creation', name: 'agd_agenda_creation')]
    public function agdCreationAgenda(): Response
    {
        $agdTypes = $this->entityManager->getRepository(AgdTypes::class)->findAll() ;

        return $this->render('agenda/creation.html.twig', [
            "filename" => "agenda",
            "titlePage" => "Creation agenda",
            "with_foot" => true,
            "agdTypes" => $agdTypes
        ]);
    }

    #[Route('/agenda/consultation', name: 'agd_agenda_consultation')]
    public function agdConsultationAgenda(): Response
    {
        $filename = $this->filename."agenda(agence)/".$this->nameAgence ;
        if (!file_exists($filename)) {
            $this->appService->checkAllDateAgenda() ;
            $this->appService->generateAgenda($filename, $this->agence) ;
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

        $result = $this->appService->verificationElement($data, $dataMessage) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $type = $this->entityManager->getRepository(AgdTypes::class)->find($agd_type) ;

        $agenda = new Agenda() ;

        $agenda->setAgence($this->agence) ;
        $agenda->setClientNom($agd_client) ;
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
        {
            unlink($filename) ;
        }

        return new JsonResponse($result) ;
    }

    #[Route('/agenda/activite/date/details', name: 'agd_activites_details_date')]
    public function agdDetailsDate(Request $request)
    {
        $date = $request->request->get('date') ;
        $agendas = $this->entityManager->getRepository(Agenda::class)->findBy([
            "date" => \DateTime::createFromFormat('Y-m-d', $date)
        ]) ;
        
        $echeances = $this->entityManager->getRepository(AgdEcheance::class)->findBy([
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
        $listEcheances = $elements ;
        $response = $this->renderView("agenda/detailsDateAganda.html.twig", [
            "agendas" => $agendas,
            "listEcheances" => $listEcheances
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
        {
            unlink($filename) ;
        }
        return new JsonResponse($result) ;
    }
}
