<?php

namespace App\Service;

use App\Entity\MenuUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppService extends AbstractController
{
    private $router ;
    private $requestStack ;
    private $entityManager ;
    private $session ;
    private $encoder ; 
    public function __construct(SessionInterface $session,RouterInterface $router,RequestStack $requestStack, EntityManagerInterface $entityManager,UserPasswordEncoderInterface $encoder)
    {
        $this->router = $router ;
        $this->requestStack = $requestStack ;
        $this->entityManager = $entityManager;
        $this->session = $session ;
        $this->encoder = $encoder ; 
    }
    public function getHappyMessage(): string
    {
        $messages = [
            'You did it! You updated the system! Amazing!',
            'That was one of the coolest updates I\'ve seen all day!',
            'Great work! Keep going!',
        ];

        $index = array_rand($messages);

        return $messages[$index];
    }

    public function checkUrl()
    {
        $allowUrl = true ;
        if(is_null($this->getUser()))
        {
            $allowUrl = false ;
        }
        else
        {
            $currentRoute = $this->router->match($this->requestStack->getCurrentRequest()->getPathInfo())['_route'];
            // dd($currentRoute) ;
        }
        
        $allowUrl = true ;
        return $allowUrl; 
    }

    public function getMenu(&$menuUsers,&$id,&$menus)
    {
        array_push($menus,$menuUsers[$id]) ;
        $user = $this->session->get("user")  ; 
        $userClass = $this->entityManager
                            ->getRepository(User::class)
                            ->findOneBy(array("email" => $user['email'])) ;

        $subMenus = $this->entityManager
                        ->getRepository(MenuUser::class)
                        ->allMenu($menuUsers[$id]['id'],$userClass->getId()) ;
        
        if(!empty($subMenus))
        {
            $endItem = end($menus) ;
            $endItem["submenu"] = $subMenus ; 
            $menus[$id] = $endItem ;
            $childMenus = $menus[$id]["submenu"] ; 
            for ($i=0; $i < count($childMenus); $i++) { 
                $childMenu = $this->entityManager
                        ->getRepository(MenuUser::class)
                        ->allMenu($childMenus[$i]["id"],$userClass->getId()) ;
                    
                if(!empty($childMenu))
                {
                    $menus[$id]["submenu"][$i]["submenu"] = $childMenu ; 
                }  
            }
        }

        $id++ ;
        if(isset($menuUsers[$id]) && !empty($menuUsers[$id]))
            $this->getMenu($menuUsers, $id,$menus) ;
    }

    function generatePassword() {
        $lowercaseChars = 'abcdefghijklmnopqrstuvwxyz';
        $uppercaseChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        $numbers = '0123456789';
      
        $password = '';
      
        // Ajouter une lettre minuscule aléatoire
        $password .= $lowercaseChars[rand(0, strlen($lowercaseChars) - 1)];
      
        // Ajouter une lettre majuscule aléatoire
        $password .= $uppercaseChars[rand(0, strlen($uppercaseChars) - 1)];
      
        // Ajouter un caractère spécial aléatoire
        $password .= $specialChars[rand(0, strlen($specialChars) - 1)];
      
        // Ajouter un chiffre aléatoire
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
      
        // Ajouter des caractères aléatoires jusqu'à ce que la longueur minimale de 8 caractères soit atteinte
        while (strlen($password) < 8) {
          $randomChar = rand(0, 3); // Choisir un chiffre aléatoire entre 0 et 3
      
          if ($randomChar == 0) {
            $password .= $lowercaseChars[rand(0, strlen($lowercaseChars) - 1)];
          } else if ($randomChar == 1) {
            $password .= $uppercaseChars[rand(0, strlen($uppercaseChars) - 1)];
          } else if ($randomChar == 2) {
            $password .= $specialChars[rand(0, strlen($specialChars) - 1)];
          } else if ($randomChar == 3) {
            $password .= $numbers[rand(0, strlen($numbers) - 1)];
          }
        }
      
        return $password;
      }

    public function hashPassword(User $user,string $plainPassword): string
    {
        $encodedPassword = $this->encoder->encodePassword($user, $plainPassword);
        return $encodedPassword;
    }

}