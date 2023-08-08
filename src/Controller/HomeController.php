<?php

namespace App\Controller;

use App\Entity\CaissePanier;
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
        // $user = $this->entityManager->getRepository(User::class)->findOneBy(["username" => "SHISSAB"]) ;
        // dd($this->appService->hashPassword($user, "HikamSM#23")) ;
        return $this->render('home/index.html.twig', [
            
        ]);
    }

    /**
     * @Route("/home/datas/update", name="home_datas_update")
     */
    public function homeUpdateData(): Response
    {
        $caissePaniers = $this->entityManager->getRepository(CaissePanier::class)->findAll() ;
        
        foreach($caissePaniers as $caissePanier)
        {
            $caissePanier->setVariationPrix($caissePanier->getHistoEntrepot()->getVariationPrix()) ;
            $this->entityManager->flush() ;
        }

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
