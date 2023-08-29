<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\Client;
use App\Entity\CltHistoClient;
use App\Entity\CltSociete;
use App\Entity\CltTypes;
use App\Entity\CltTypeSociete;
use App\Entity\CltUrgence;
use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
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
    private $nomAgence ;
    public function __construct(EntityManagerInterface $entityManager,SessionInterface $session, AppService $appService)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->appService = $appService ;
        $this->appService->checkUrl() ;
        $this->user = $this->session->get("user") ;
        $this->agence = $this->entityManager->getRepository(Agence::class)->find($this->user["agence"]) ; 
        $this->filename = "files/systeme/client/" ;
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->nameUser = strtolower($this->user["username"]) ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"] 
        ]) ;
        $this->nomAgence = strtoupper($this->agence->getNom()) ;
    }
    #[Route('/client/creation', name: 'clt_creation')]
    public function clientCreation(): Response
    {
        $types = $this->entityManager->getRepository(CltTypes::class)->findAll() ;

        return $this->render('client/creation.html.twig', [
            "filename" => "client",
            "titlePage" => "Création Client",
            "with_foot" => true,
            "nomAgence" => $this->nomAgence ,
            "types" => $types ,
        ]);
    }

    #[Route('/client/consultation', name: 'clt_consultation')]
    public function clientConsultation(): Response
    {
        $filename = $this->filename."client(agence)/".$this->nameAgence ;

        if(!file_exists($filename))
            $this->appService->generateCltClient($filename, $this->agence) ;

        $clients = json_decode(file_get_contents($filename)) ;

        return $this->render('client/consultation.html.twig', [
            "filename" => "client",
            "titlePage" => "Liste des Clients",
            "with_foot" => false,
            "clients" => $clients,
        ]);
    }

    #[Route('/client/information/get', name: 'clt_client_information_get')]
    public function clientGetInformation(Request $request): Response
    {
        $idType = $request->request->get("idType") ;
        if(empty($idType))
            return new Response("") ;

        $type = $this->entityManager->getRepository(CltTypes::class)->find($idType) ;

        $response = "";
        if($type->getReference() == "MORAL")
        {
            $typeSocietes = $this->entityManager->getRepository(CltTypeSociete::class)->findAll() ;
            
            $response = $this->renderView("client/getClientMorale.html.twig",[
                "typeSocietes" => $typeSocietes ,
            ]) ;
        }
        else if($type->getReference() == "PHYSIQUE")
        {
            $response = $this->renderView("client/getClientPhysique.html.twig") ;
        }

        return new Response($response) ;
    }

    #[Route('/client/information/save', name: 'clt_client_information_save')]
    public function clientSaveInformation(Request $request)
    {
        $clt_type = $request->request->get("clt_type") ;

        $result = $this->appService->verificationElement([
            $clt_type
        ], [
            "Statut"
            ]) ;

        if(!$result["allow"])
            return new JsonResponse($result) ;

        $type = $this->entityManager->getRepository(CltTypes::class)->find($clt_type) ;

        if($type->getReference() == "MORAL")
        {
            $clt_soc_nom = $request->request->get("clt_soc_nom") ;
            $clt_soc_nom_gerant = $request->request->get("clt_soc_nom_gerant") ;
            $clt_soc_nom_adresse = $request->request->get("clt_soc_nom_adresse") ;
            $clt_soc_telephone = $request->request->get("clt_soc_telephone") ;
            $clt_soc_fax = $request->request->get("clt_soc_fax") ;
            $clt_soc_email = $request->request->get("clt_soc_email") ;
            $clt_soc_domaine = $request->request->get("clt_soc_domaine") ;
            $clt_soc_num_registre = $request->request->get("clt_soc_num_registre") ;
            $clt_soc_type_societe = $request->request->get("clt_soc_type_societe") ;
            
            $clt_lien_nom = $request->request->get("clt_lien_nom") ;
            $clt_lien_email = $request->request->get("clt_lien_email") ;
            $clt_lien_telephone = $request->request->get("clt_lien_telephone") ;

            $result = $this->appService->verificationElement([
                $clt_soc_nom,
                $clt_soc_nom_adresse,
                $clt_soc_telephone,
            ], [
                "Nom",
                "Adresse",
                "Teléphone",
            ]) ;
    
            if(!$result["allow"])
                return new JsonResponse($result) ;

            $typeSociete = $this->entityManager->getRepository(CltTypeSociete::class)->find($clt_soc_type_societe) ;

            $societe = new CltSociete() ;

            $societe->setAgence($this->agence) ;
            $societe->setTypeSociete($typeSociete) ;
            $societe->setNom($clt_soc_nom) ;
            $societe->setNomGerant($clt_soc_nom_gerant) ;
            $societe->setAdresse($clt_soc_nom_adresse) ;
            $societe->setTelFixe($clt_soc_telephone) ;
            $societe->setFax($clt_soc_fax) ;
            $societe->setEmail($clt_soc_email) ;
            $societe->setDomaine($clt_soc_domaine) ;
            $societe->setNumRegistre($clt_soc_num_registre) ;

            $this->entityManager->persist($societe) ;
            $this->entityManager->flush() ;

            if(!empty($clt_lien_nom))
            {
                $urgence = new CltUrgence() ;
    
                $urgence->setNom($clt_lien_nom) ;
                $urgence->setTelephone($clt_lien_telephone) ;
                $urgence->setAdresse(NULL) ;
                $urgence->setEmail($clt_lien_email) ;
                $urgence->setLienParente(NULL) ;
                $urgence->setObservation(NULL) ;
                
                $this->entityManager->persist($urgence) ;
                $this->entityManager->flush() ;
            }
            else
            {
                $urgence = NULL ;
            }

            $client = NULL ;
        }
        else if($type->getReference() == "PHYSIQUE")
        {

            $clt_client_nom = $request->request->get("clt_client_nom") ;
            $clt_client_nin = $request->request->get("clt_client_nin") ;
            $clt_client_adresse = $request->request->get("clt_client_adresse") ;
            $clt_client_quartier = $request->request->get("clt_client_quartier") ;
            $clt_client_telephone = $request->request->get("clt_client_telephone") ;
            $clt_client_email = $request->request->get("clt_client_email") ;
            $clt_client_sexe = $request->request->get("clt_client_sexe") ;
            $clt_client_situation = $request->request->get("clt_client_situation") ;
            $clt_client_date_naiss = $request->request->get("clt_client_date_naiss") ;
            $clt_client_lieu_naiss = $request->request->get("clt_client_lieu_naiss") ;
            $clt_client_profession = $request->request->get("clt_client_profession") ;

            $clt_lien_nom = $request->request->get("clt_lien_nom") ;
            $clt_lien_telephone = $request->request->get("clt_lien_telephone") ;
            $clt_lien_email = $request->request->get("clt_lien_email") ;
            $clt_lien_adresse = $request->request->get("clt_lien_adresse") ;
            $clt_lien_parente = $request->request->get("clt_lien_parente") ;
            $clt_lien_obs = $request->request->get("clt_lien_obs") ;

            $result = $this->appService->verificationElement([
                $clt_client_nom,
                $clt_client_adresse,
                $clt_client_telephone,
            ], [
                "Nom",
                "Adresse",
                "Teléphone",
            ]) ;
    
            if(!$result["allow"])
                return new JsonResponse($result) ;

            $client = new Client() ;

            $client->setAgence($this->agence) ;
            $client->setNom($clt_client_nom) ;
            $client->setNin($clt_client_nin) ;
            $client->setAdresse($clt_client_adresse) ;
            $client->setQuartier($clt_client_quartier) ;
            $client->setTelephone($clt_client_telephone) ;
            $client->setEmail($clt_client_email) ;
            $client->setSexe($clt_client_sexe) ;
            $client->setSituation($clt_client_situation) ;
            $client->setLieuTravail(NULL) ;
            $client->setDateNaissance(empty($clt_client_date_naiss) ? NULL : \DateTime::createFromFormat("d/m/Y",$clt_client_date_naiss)) ;
            $client->setLieuNaissance($clt_client_lieu_naiss) ;
            $client->setPrefession($clt_client_profession) ;

            $this->entityManager->persist($client) ;
            $this->entityManager->flush() ;


            if(!empty($clt_lien_nom))
            {
                $urgence = new CltUrgence() ;
    
                $urgence->setNom($clt_lien_nom) ;
                $urgence->setTelephone($clt_lien_telephone) ;
                $urgence->setAdresse($clt_lien_adresse) ;
                $urgence->setEmail($clt_lien_email) ;
                $urgence->setLienParente($clt_lien_parente) ;
                $urgence->setObservation($clt_lien_obs) ;
                
                $this->entityManager->persist($urgence) ;
                $this->entityManager->flush() ;
            }
            else
            {
                $urgence = NULL ;
            }

            $societe = NULL ;
        }

        $histoClient = new CltHistoClient() ;

        $histoClient->setAgence($this->agence) ;
        $histoClient->setClient($client) ;
        $histoClient->setSociete($societe) ;
        $histoClient->setType($type) ;
        $histoClient->setUrgence($urgence) ;
        $histoClient->setStatut(True) ;
        $histoClient->setCreatedAt(new \DateTimeImmutable) ;
        $histoClient->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($histoClient) ;
        $this->entityManager->flush() ;

        $filename = $this->filename."client(agence)/".$this->nameAgence ;

        if(file_exists($filename))
            unlink($filename) ;

        return new JsonResponse($result) ;
    }
    
}
