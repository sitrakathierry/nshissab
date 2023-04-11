<?php

namespace App\Service;

use App\Entity\Menu;
use App\Entity\MenuUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nexmo\Client\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Vonage\Client\Credentials\Basic;
use Vonage\Client;
use Vonage\SMS\Message\SMS ;
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

                    for($j = 0; $j < count($childMenu) ; $j++)
                    {
                        $subMenuChild = $this->entityManager
                            ->getRepository(MenuUser::class)
                            ->allMenu($childMenu[$j]['id'],$userClass->getId()) ;
                        
                        if(!empty($subMenuChild))
                        {
                            $menus[$id]["submenu"][$i]["submenu"][$j]["submenu"] = $subMenuChild ; 
                        }
                    }
                }  
            }
        }

        $id++ ;
        if(isset($menuUsers[$id]) && !empty($menuUsers[$id]))
            $this->getMenu($menuUsers, $id,$menus) ;
    }

    public function getMenuUser(&$menuUsers,&$id,&$menus)
    {
        array_push($menus,$menuUsers[$id]) ;

        $subMenus = $this->entityManager
                        ->getRepository(MenuUser::class)
                        ->allMenuUser($menuUsers[$id]['id']) ;
        
        if(!empty($subMenus))
        {
            $endItem = end($menus) ;
            $endItem["submenu"] = $subMenus ; 
            $menus[$id] = $endItem ;
            $childMenus = $menus[$id]["submenu"] ; 
            for ($i=0; $i < count($childMenus); $i++) { 
                $childMenu = $this->entityManager
                        ->getRepository(MenuUser::class)
                        ->allMenuUser($childMenus[$i]["id"]) ;
                    
                if(!empty($childMenu))
                {
                    $menus[$id]["submenu"][$i]["submenu"] = $childMenu ; 

                    for($j = 0; $j < count($childMenu) ; $j++)
                    {
                        $subMenuChild = $this->entityManager
                            ->getRepository(MenuUser::class)
                            ->allMenuUser($childMenu[$j]['id']) ;
                        
                        if(!empty($subMenuChild))
                        {
                            $menus[$id]["submenu"][$i]["submenu"][$j]["submenu"] = $subMenuChild ; 
                        }
                    }
                }  
            }
        }

        $id++ ;
        if(isset($menuUsers[$id]) && !empty($menuUsers[$id]))
            $this->getMenuUser($menuUsers, $id,$menus) ;
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

    public function generateUserMenu(&$menus,$filename)
    {

        if(!file_exists($filename))
        {
            $menuUsers = $this->entityManager
                            ->getRepository(MenuUser::class)
                            ->allMenuUser(null) ;
            $id = 0;
            $this->getMenuUser($menuUsers,$id,$menus) ;
            $json = json_encode($menus) ;
            file_put_contents($filename, $json); 
        }
    }

    function afficher_menu($menu_array,&$menu)
    {
        $menu .= "<ul class='list-unstyled content_list overflow-auto px-3 pb-3 list_item_menu' id='menuCollapse'>";
        // dd($menu_array) ;
        foreach ($menu_array as $item) {
            $menu .= "<li class='py-1 px-3 w-100 menu_item rounded border font-weight-bold'>
                            <a href=".$this->router->generate($item->route).">
                                <i class='fa ".$item->icone."'></i>&nbsp;
                                <span class='text-uppercase'>".$item->nom. "</span>
                            </a>";
            if (isset($item->submenu)) {
                $this->afficher_menu($item->submenu,$menu);
            }
            $menu .= '
                <i class="fa fa-plus float-right ml-2 mt-1 menuPlus" data-toggle="collapse" data-target="#menuCollapse{{i}}" aria-expanded="true" aria-controls="menuCollapse{{i}}"></i>
                <i class="far fa-check-circle float-right ml-2 mt-1 menuCheck"></i>
            </li>';
        }
        $menu .= "</ul>";
    }

    public function generateListeMenu()
    {
        $listes = $this->entityManager->getRepository(Menu::class)->findBy(["statut" => True]) ;

        $listeMenu = [] ;
        $pathListeMenu = "files/json/listeMenu.json" ;
        foreach ($listes as $liste) {
            array_push($listeMenu,[
                "id" => $liste->getId(),
                "nom" => $liste->getNom()
            ]) ;
        }

        file_put_contents($pathListeMenu, json_encode($listeMenu)) ;
    }

    public function sendSms()
    {
        $basic  = new Basic("4c66ffd6", "XCiCWpI9qBqffTNA");
        $client = new Client($basic);

        $response = $client->sms()->send(
            new SMS("261343641200", "HIKAM", 'Bonjour Cher ami, bienvenue sur shissab (Hahah)')
        );
        
        $message = $response->current();
        
        if ($message->getStatus() == 0)
        {
            return "The message was sent successfully";
        } 
        else 
        {
            return "The message failed with status: " . $message->getStatus() ;
        }
    }

    public function verificationElement($data= [], $dataMessage = [])
    {
        $allow = True ;
        $type = "green" ;
        $message = "Information enregistré avec succès" ;
        for ($i=0; $i < count($data); $i++) { 
            if(!is_numeric($data[$i]))
            {
                if(empty($data[$i]))
                {
                    $allow = False ;
                    $type="orange" ;
                    $message = $dataMessage[$i]." vide" ;
                    break;
                }
            }
            else
            {
                if(empty($data[$i]))
                {
                    $allow = False ;
                    $type="orange" ;
                    $message = $dataMessage[$i]." vide" ;
                    break;
                }
                else if(intval($data[$i]) <= 0)
                {
                    $allow = False ;
                    $type="red" ;
                    $message = $dataMessage[$i]." doit être supérieur à 0" ;
                    break;
                }
            }
        } 

        $result = [] ;
        $result["allow"] = $allow ;
        $result["type"] = $type ;
        $result["message"] = $message ;

        return $result ;
    }
}
