<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private $session ;
    private $entityManager ;

    public function __construct(SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->session = $session ;
        $this->entityManager = $entityManager ;
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
    public function authentificationLogin(Request $request)
    {
        $error = null ;
        $allow = True ;
        $type = "success" ;
        $csrfToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('authenticate', $csrfToken)) {
            $error = 'CSRF token invalide';
            $type = "danger" ;
            $allow = False ;
        }

        $username = $request->request->get("username") ; 
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => strtoupper($username)
        ]) ;

        if(is_null($user))
        {
            $error = "Le nom d'utilisateur n'existe pas";
            $type = "warning" ;
            $allow = False ;
        }

        if(!$allow)
        {
            return $this->redirectToRoute('app_login',[
                "error" => $error,
                "type" => $type
            ]);
        }

        $password = $request->request->get("password") ; 
        $hash = $user->getPassword() ;

        $passVerify = password_verify($password,$hash) ;

        if(!$passVerify)
        {
            $error = "Le mot de passe entré est incorrect";
            $type = "danger" ;
            $allow = False ;
        }
        
        if(!$allow)
        {
            return $this->redirectToRoute('app_login',[
                "error" => $error,
                "type" => $type
            ]);
        }
        
        $roles = $user->getRoles()[0] ;

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
            "email" => $user->getEmail(),
            "role" =>$role,
            "csrf_token" => $csrfToken
        ] ;  
        
        $this->session->set("user", $data) ;

        return $this->redirectToRoute($route);
    }
}
