<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private $session ;
    private $entityManager ;
    private $appService ;

    public function __construct(AppService $appService, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->session = $session ;
        $this->entityManager = $entityManager ;
        $this->appService = $appService ;
    }

    /**
     * @Route("/login/{error}/{type}", name="app_login", defaults = {"error" : null,"type" : null})
     */
    public function login($error, $type): Response
    {
        return $this->render('security/login.html.twig', ['error' => $error,'type' => $type ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/auth/login", name="auth_login")
     */
    public function authentificationLogin(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $error = null ;
        $type = "success" ;

        $csrfToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('authenticate', $csrfToken)) {
            $error = 'CSRF token invalide';
            $type = "danger" ;
            return $this->redirectToRoute('app_login',[
                "error" => $error,
                "type" => $type
            ]);
        }
        try
        {
            $username = $request->request->get("username") ; 
            $user = $this->entityManager->getRepository(User::class)->findBy([
                "username" => strtoupper($username)
            ]) ;

            if(empty($user))
            {
                $error = "Le nom d'utilisateur n'existe pas";
                $type = "warning" ;
                return $this->redirectToRoute('app_login',[
                    "error" => $error,
                    "type" => $type
                ]);
            }

            $password = $request->request->get("password") ; 
            $userObject = null ;
            if(count($user) > 1)
            {
                foreach ($user as $userElement) {
                    $isPasswordValid = $passwordEncoder->isPasswordValid($userElement,$password);
                    if ($isPasswordValid) {
                        $userObject = $userElement ;
                        break ;
                    }
                }

                if(is_null($userObject))
                {
                    $error = "Le mot de passe entré est incorrect";
                    $type = "danger" ;
                    return $this->redirectToRoute('app_login',[
                        "error" => $error,
                        "type" => $type
                    ]);
                }
            }
            else
            {
                $userObject = $user[0] ;
                if($userObject->isDisabled())
                {
                    return $this->redirectToRoute('app_login',[
                        "error" => "Utilisateur désactivé",
                        "type" => "warning"
                    ]);
                }

                $isPasswordValid = $passwordEncoder->isPasswordValid($userObject,$password);
                
                if (!$isPasswordValid) {
                    $error = "Le mot de passe entré est incorrect";
                    $type = "danger" ;
                    return $this->redirectToRoute('app_login',[
                        "error" => $error,
                        "type" => $type
                    ]);
                }
            }
        }
        catch(Exception $e)
        {
            $error = "Désolé, problème de connexion au serveur. Veuiller réessayer s'il vous plait ...";
            $type = "danger" ;

            return $this->redirectToRoute('app_login',[
                "error" => $error,
                "type" => $type
            ]);
        }

        $roles = $userObject->getRoles()[0] ;

        if($roles == "ADMIN")
        {
            $role = $roles ;
            $route = "app_admin" ;
        }
        else if($roles == "MANAGER")
        {
            $role = $roles ;
            $route = "app_home" ;
        }
        else
        {
            $role = $roles ;
            $route = "app_home" ;
        }

        $data = [
            "username" => strtoupper($username),
            "email" => $userObject->getEmail(),
            "agence" => $userObject->getAgence()->getId(),
            "role" =>$role,
            "csrf_token" => $csrfToken
        ];  
        
        $this->session->set("user", $data) ;

        return $this->redirectToRoute($route);
    }

    /**
     * @Route("/login/problem/{error}/{type}", name="problem_occured", defaults = {"error" : null,"type" : null})
     */
    public function problemOccuredLogin($error, $type)
    {
        return $this->render('security/problem.html.twig', ['error' => $error,'type' => $type ]);
    }
}
