<?php 

namespace App\Service;

use App\Controller\PrestationController;
use App\Entity\LctContrat;
use App\Entity\LctStatut;
use Doctrine\ORM\EntityManager;

class PrestationService
{
    private $appService ;

    public function __construct(AppService $appService)
    {
        $this->appService = $appService ;
    }

    public function checkContrat(EntityManager $entityManager, PrestationController $prestCtrl)
    {
        $statut = $entityManager->getRepository(LctStatut::class)->findOneBy([
            "reference" => "ENCR"
        ]) ;

        $contrats = $entityManager->getRepository(LctContrat::class)->findBy([
            "agence" => $this->appService->getAgence(),
            "statut" => $statut,
            "statutGen" => True,
        ]) ;

        $dateNow = date("d/m/Y") ;
        foreach ($contrats as $contrat) {
            $dateFin = $contrat->getDateFin()->format("d/m/Y") ;
            
            $plusPetit = $this->appService->compareDates($dateFin,$dateNow,"P") || $this->appService->compareDates($dateFin,$dateNow,"P") ;
            if($plusPetit)
            {
                if($contrat->getRenouvellement()->getReference() != "TCT")
                {
                    $newStatut = $entityManager->getRepository(LctStatut::class)->findOneBy([
                        "reference" => "EXP"
                    ]) ;
    
                    $contrat->setStatut($newStatut) ;
                    $entityManager->flush() ;

                    $filename = "files/systeme/prestations/location/contrat(agence)/".$this->appService->getnameAgence() ;
                    if(file_exists($filename))
                        unlink($filename) ;
                }
                else
                {
                    $prestCtrl->prestRenouvContratLocation(null,$contrat->getId()) ;
                }
            }
        }
    }
}

// 032 99 253 46
