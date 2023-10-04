<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\CaissePanier;
use App\Entity\PrdHistoEntrepot;
use App\Entity\PrdVariationPrix;
use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{
    private $entityManager;
    private $session ;
    private $appService ;
    private $urlGenerator ;

    public function __construct(EntityManagerInterface $entityManager,SessionInterface $session, AppService $appService)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->appService = $appService ;
        $this->appService->checkUrl() ;
    }

    /**
     * @Route("/home", name="app_home")
     */
    public function index(): Response
    {
        $username = $this->session->get("user")["username"] ;
        // $agence = $this->entityManager->getRepository(Agence::class)->find($this->session->get("user")["agence"]) ; 
        // $user = $this->entityManager->getRepository(User::class)->findOneBy([
        //     "username" => "TEST",
        //     "agence" => $agence
        //     ]) ;
        // dd($this->appService->hashPassword($user, "Admin@18")) ;
        return $this->render('home/index.html.twig', [
           "username" => $username 
        ]);
    }

    /**
     * @Route("/home/datas/update", name="home_datas_update")
     */
    public function homeUpdateData(): Response
    {
        $variationPrixs = $this->entityManager->getRepository(PrdVariationPrix::class)->findAll() ;
        
        foreach($variationPrixs as $variationPrix)
        {
            $histoEntrepots = $this->entityManager->getRepository(PrdHistoEntrepot::class)->findBy([
                "variationPrix" => $variationPrix  
            ]) ;

            if(count($histoEntrepots) > 1)
            {
                $elements = [] ;
                foreach ($histoEntrepots as $histoEntrepot) {
                    # code...
                    $item = [] ;

                    $item["id"] = $histoEntrepot->getId() ;
                    $item["identrepot"] = $histoEntrepot->getEntrepot()->getId() ;
                    $item["indice"] = $histoEntrepot->getIndice() ;
                    $item["idVariation"] = $histoEntrepot->getVariationPrix()->getId() ;

                    array_push($elements,$item) ;
                }

                $indices = array(); // Tableau pour stocker les indices uniques

                foreach ($elements as $item) {
                    $indices[] = $item["indice"];
                }

                $uniqueIndices = array_unique($indices);

                if(count($uniqueIndices) > 1)
                {
                    dd($elements) ;
                }
            }
        }

        // CAISSE PANIER
        // $caissePaniers = $this->entityManager->getRepository(CaissePanier::class)->findAll() ;
        
        // foreach($caissePaniers as $caissePanier)
        // {
        //     $caissePanier->setVariationPrix($caissePanier->getHistoEntrepot()->getVariationPrix()) ;
        //     $this->entityManager->flush() ;
        // }

        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/home/datas/variation/insert/{idVar}/{idHEnt}", name="home_datas_variation_insert")
    */
    public function homeInsertDataVariation($idVar,$idHEnt): Response
    {
        $variationPrix = $this->entityManager->getRepository(PrdVariationPrix::class)->find($idVar) ;
        $histoEntrepot = $this->entityManager->getRepository(PrdHistoEntrepot::class)->find($idHEnt) ;

        $newVariation = new PrdVariationPrix() ;
    
        $newVariation->setProduit($variationPrix->getProduit()) ;
        $newVariation->setPrixVente($variationPrix->getPrixVente()) ;
        $newVariation->setIndice(null) ;
        $newVariation->setStock($histoEntrepot->getStock()) ;
        $newVariation->setStockAlert($variationPrix->getStockAlert()) ;
        $newVariation->setStatut(True) ;
        $newVariation->setCreatedAt(new \DateTimeImmutable) ;
        $newVariation->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($newVariation) ;
        $this->entityManager->flush() ;

        $histoEntrepot->setVariationPrix($newVariation) ;
        $histoEntrepot->setUpdatedAt(new \DateTimeImmutable) ;
        $this->entityManager->flush() ;

        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/home/datas/indice/update", name="home_datas_indice_update")
    */
    public function homeUpdateDataIndice(): Response
    {
        $variationPrixs = $this->entityManager->getRepository(PrdVariationPrix::class)->findAll() ;
        foreach($variationPrixs as $variationPrix)
        {
            $histoEntrepot = $this->entityManager->getRepository(PrdHistoEntrepot::class)->findOneBy([
                "variationPrix" => $variationPrix    
            ]) ;
            $variationPrix->setIndice($histoEntrepot->getIndice()) ;
            $this->entityManager->flush() ;
        }

        return $this->redirectToRoute('app_home');
    }


    /**
     * @Route("/home/datas/variation/update/{idVar}/{idHEnt}", name="home_datas_variation_update")
    */
    public function homeUpdateDataVariation($idVar,$idHEnt): Response
    {
        $variationPrix = $this->entityManager->getRepository(PrdVariationPrix::class)->find($idVar) ;
        $histoEntrepot = $this->entityManager->getRepository(PrdHistoEntrepot::class)->find($idHEnt) ;

        $variationPrix->setStock($variationPrix->getStock() + $histoEntrepot->getStock()) ;
        $this->entityManager->flush() ;

        $histoEntrepot->setVariationPrix($variationPrix) ;
        $histoEntrepot->setUpdatedAt(new \DateTimeImmutable) ;
        $this->entityManager->flush() ;

        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/home/refresh/{key}", name="home_refresh")
     */
    public function refreshFile($key)
    {
        $this->appService->homeRefreshAllFiles($key) ;

        return $this->redirectToRoute('app_home');
    }
}
