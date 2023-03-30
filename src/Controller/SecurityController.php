<?php

namespace App\Controller;

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

    public function __construct(SessionInterface $session)
    {
        $this->session = $session ;
    }
    /**
     * @Route("/login/{error}", name="app_login", defaults = {"error" : null})
     */
    public function login($error): Response
    {
        return $this->render('security/login.html.twig', ['error' => $error]);
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
        $csrfToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('authenticate', $csrfToken)) {
            $error = 'CSRF token invalide';
        }
        // $url = $this->generateUrl('app_login',["error" => $error ]);

        $data = [
            "username" => "shissab",
            "email" => "shissab@admin.sm",
            "csrf_token" => $csrfToken
        ] ;

        $this->session->set("user", $data) ;

        // return $this->redirectToRoute('app_login',["error" => $error]);

        return $this->redirectToRoute('app_admin');
        
    }
}
