<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{
    private $entityManager;
    private $session ;
    private $appService ;

    public function __construct(EntityManagerInterface $entityManager,SessionInterface $session, AppService $appService)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->appService = $appService ;
    }

    /**
     * @Route("/home", name="app_home")
     */
    public function index(): Response
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl)
        {
            $url = $this->generateUrl('app_login');
            return new RedirectResponse($url);
        } 
        // $user = $this->entityManager->getRepository(User::class)->findOneBy(["username" => "SHISSAB"]) ;
        // dd($this->appService->hashPassword($user, "HikamSM#23")) ;
        return $this->render('home/index.html.twig', [
            
        ]);
    }
}
