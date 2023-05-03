<?php

namespace App\Service;

use App\Entity\Menu;
use App\Entity\MenuUser;
use App\Entity\PrdCategories;
use App\Entity\PrdEntrepot;
use App\Entity\PrdFournisseur;
use App\Entity\PrdHistoEntrepot;
use App\Entity\PrdPreferences;
use App\Entity\Produit;
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
    private $urlGenerator ;
    public function __construct(SessionInterface $session,RouterInterface $router,RequestStack $requestStack, EntityManagerInterface $entityManager,UserPasswordEncoderInterface $encoder, UrlGeneratorInterface $urlGenerator)
    {
        $this->router = $router ;
        $this->requestStack = $requestStack ;
        $this->entityManager = $entityManager;
        $this->session = $session ;
        $this->encoder = $encoder ; 
        $this->urlGenerator = $urlGenerator ;
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

    public function currentRoute()
    {
        $currentRoute = $this->router->match($this->requestStack->getCurrentRequest()->getPathInfo())['_route'];

        return $currentRoute ;
    }

    public function checkUrl()
    {
        $allowUrl = true ;
        $user = $this->session->get("user") ;
        if(!isset($user))
        {
            $allowUrl = false ;
        }
        else
        {
            $currentRoute = $this->router->match($this->requestStack->getCurrentRequest()->getPathInfo())['_route'];
            $blockedRoute = 
            [
                "admin_add_societe",
                "admin_saveSociete",
                "getRandomPass",
                "admin_listSociete",
                "menu_attribution",
                "admin_save_attribution",
                "admin_menu_creation",
                "disp_edit_menu",
                "admin_validCreation",
                "menu_corbeille",
                "restore_menu_corbeille",
                "manager_agence",
                "app_admin"
            ] ;
            if($user["role"] != "ADMIN")
            {
                for ($i=0; $i < count($blockedRoute); $i++) { 
                    if($currentRoute == $blockedRoute[$i])
                    {
                        $allowUrl = false ;
                        break ;
                    }
                }
            }
        }

        if(!$allowUrl)
        {
            $url = $this->urlGenerator->generate('app_logout');
            header('location:'.$url) ;
            exit(); 
        }
    }

    public function requestMenu($role,User $user,$parent)
    {
        if($role == "MANAGER")
        {
            $infoMenu = $this->entityManager
                        ->getRepository(MenuUser::class)
                        ->allMenuAgence($parent, $user->getAgence()->getId()) ;
        }
        else
        {
            $infoMenu = $this->entityManager
                    ->getRepository(MenuUser::class)
                    ->allMenu($parent,$user->getId()) ;
        }
        return $infoMenu ;
    }

    public function getMenu(&$menuUsers,&$id,&$menus,$inUser = null)
    {
        array_push($menus,$menuUsers[$id]) ;
        if(!is_null($inUser))
            $user = $inUser ;
        else
            $user = $this->session->get("user")  ; 

        $userClass = $this->entityManager
                            ->getRepository(User::class)
                            ->findOneBy(array("email" => $user['email'])) ;

        $roleUser = $userClass->getRoles()[0] ;

        $subMenus = $this->requestMenu($roleUser,$userClass,$menuUsers[$id]['id']) ;
        
        if(!empty($subMenus))
        {
            $endItem = end($menus) ;
            $endItem["submenu"] = $subMenus ; 
            $menus[$id] = $endItem ;
            $childMenus = $menus[$id]["submenu"] ; 
            for ($i=0; $i < count($childMenus); $i++) { 
                $childMenu = $this->requestMenu($roleUser,$userClass,$childMenus[$i]["id"]) ;
                    
                if(!empty($childMenu))
                {
                    $menus[$id]["submenu"][$i]["submenu"] = $childMenu ; 

                    for($j = 0; $j < count($childMenu) ; $j++)
                    {
                        $subMenuChild = $this->requestMenu($roleUser,$userClass,$childMenu[$j]['id']) ;

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
            $this->getMenu($menuUsers, $id,$menus,$user) ;
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

    public function generatePassword() 
    {
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
            if(!empty($menuUsers))
            {
                $this->getMenuUser($menuUsers,$id,$menus) ;
                $json = json_encode($menus) ;
                file_put_contents($filename, $json); 
            }
                
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
        $listes = $this->entityManager->getRepository(Menu::class)->findBy([
            "statut" => True,
            "is_admin" => NULL
        ]) ;

        $listeMenu = [] ;
        $pathListeMenu = "files/json/listeMenu.json" ;
        foreach ($listes as $liste) {
            $parent = !is_null( $liste->getMenuParent()) ? $liste->getMenuParent()->getNom() : "NULL" ;
            array_push($listeMenu,[
                "id" => $liste->getId(),
                "nom" => $liste->getNom(),
                "parent" => $parent
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

    public function generateStockCategorie($filename,$agence)
    {
        $categories = $this->entityManager->getRepository(PrdCategories::class)->findBy([
            "agence" => $agence
        ]) ;
        $elements = [] ;
        
        foreach ($categories as $cat) {
            $element = [] ;
            $element["id"] = $cat->getId() ;
            $element["nom"] = $cat->getNom() ;
            $element["image"] = $cat->getImages() ;
            $element["agence"] = $cat->getAgence()->getId() ;
            array_push($elements,$element) ;
        } 

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateStockEntrepot($filename, $agence)
    {
        $entrepots = $this->entityManager->getRepository(PrdEntrepot::class)->findBy([
            "agence" => $agence
        ]) ;
        $elements = [] ;
        
        foreach ($entrepots as $entrepot) {
            $element = [] ;
            $element["id"] = $entrepot->getId() ;
            $element["nom"] = $entrepot->getNom() ;
            $element["adresse"] = $entrepot->getAdresse() ;
            $element["telephone"] = $entrepot->getTelephone() ;
            $element["agence"] = $entrepot->getAgence()->getId() ;
            array_push($elements,$element) ;
        } 

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateStockPreferences($filename,$user)
    {
        $preferences = $this->entityManager->getRepository(PrdPreferences::class)->findBy([
            "user" => $user,
            "statut" => True
        ]) ;

        $elements = [] ;
        
        if(!empty($preferences))
        {
            foreach ($preferences as $preference) {
                $element = [] ;
                $element["id"] = $preference->getId() ;
                $element["nom"] = $preference->getCategorie()->getNom() ;
                $element["categorie"] = $preference->getCategorie()->getId() ;
    
                array_push($elements,$element) ;
            } 
        }
        
        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateStockFournisseur($filename,$agence)
    {
        $fournisseurs = $this->entityManager->getRepository(PrdFournisseur::class)->findBy([
            "agence" => $agence
        ]) ;
        
        $elements = [] ;

        foreach ($fournisseurs as $fournisseur) {
            $element = [] ;
            $element["id"] = $fournisseur->getId() ;
            $element["nom"] = $fournisseur->getNom() ;
            $element["nomContact"] = $fournisseur->getNomContact() ;
            $element["telBureau"] = $fournisseur->getTelBureau() ;
            $element["telMobile"] = $fournisseur->getTelMobile() ;
            $element["adresse"] = $fournisseur->getAdresse() ;
            $element["email"] = $fournisseur->getEmail() ;
            $element["agence"] = $fournisseur->getAgence()->getId() ;

            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateProduitStockGeneral($filename,$agence)
    {
        $stockGenerales = $this->entityManager->getRepository(Produit::class)->findBy([
            "agence" => $agence
        ]) ;
        
        $elements = [] ;

        foreach ($stockGenerales as $stockGeneral) {
            $element = [] ;
            $element["id"] = $stockGeneral->getId() ;
            $element["idC"] = $stockGeneral->getPreference()->getId() ;
            $element["codeProduit"] = $stockGeneral->getCodeProduit() ;
            $element["categorie"] = $stockGeneral->getPreference()->getCategorie()->getNom() ;
            $element["nom"] = $stockGeneral->getNom() ;
            $element["stock"] = $stockGeneral->getStock() ;
            $element["agence"] = $stockGeneral->getAgence()->getId() ;
            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateStockInEntrepot($filename,$agence)
    {
        $stockEntrepots = $this->entityManager->getRepository(PrdHistoEntrepot::class)->findBy([
            "agence" => $agence
        ]) ;
        
        $elements = [] ;

        foreach ($stockEntrepots as $stockEntrepot) {
            $element = [] ;
            $element["id"] = $stockEntrepot->getId() ;
            $element["idE"] = $stockEntrepot->getEntrepot()->getId() ;
            $element["idC"] = $stockEntrepot->getVariationPrix()->getProduit()->getPreference()->getId() ;
            $element["idP"] = $stockEntrepot->getVariationPrix()->getProduit()->getId() ;
            $element["entrepot"] = $stockEntrepot->getEntrepot()->getNom() ;
            $element["code"] = $stockEntrepot->getVariationPrix()->getProduit()->getCodeProduit() ;
            $element["indice"] = !empty($stockEntrepot->getIndice()) ? $stockEntrepot->getIndice() : "-" ;
            $element["categorie"] = $stockEntrepot->getVariationPrix()->getProduit()->getPreference()->getCategorie()->getNom() ;
            $element["nom"] = $stockEntrepot->getVariationPrix()->getProduit()->getNom() ;
            $element["stock"] = $stockEntrepot->getStock() ;
            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function recherche($item, $search = []) {
        if (count($search) > 1) {
            $condition = true ;
            foreach ($search as $key => $value) {
                if(!empty($value))
                    $condition = $condition && (strpos(strtolower($item->$key), strtolower($value)) !== false) ;  
            }
            return $condition;
        } else {
            $key = key($item);
            return isset($item->$key) && strpos($item->$key, $search[$key]) !== false;
        }
    } 
    public function searchData($data, $search = [])
    {
        $resultats = array_filter($data, function($item) use($search) {
            return $this->recherche($item, $search);
        });
        $vide = True ;
        foreach ($search as $key => $value) {
            if(!empty($value))
            {
                $vide = False ;
                break ;
            }
        }
        if(empty($resultats) && $vide)
            return $data ;
        return $resultats ;
    }

    function check_duplicates_recursive($arr) {
        // Si le tableau ne contient qu'un élément, il n'y a pas de doublon
        if (count($arr) <= 1) {
            return false;
        }
        
        // Sélectionne le premier élément du tableau
        $elem = array_shift($arr);
        
        // Vérifie si l'élément est présent dans le reste du tableau
        if (in_array($elem, $arr)) {
            return true;
        }
        
        // Sinon, répète la même opération sur le reste du tableau
        return $this->check_duplicates_recursive($arr);
    } 

    public function detecter_doublons($tableau) {
        $keys = array_keys($tableau[0]);
        $enregistrements = array();
        $doublons = array();
        
        foreach ($tableau as $enregistrement) {
            $enregistrement_str = '';
            foreach ($keys as $key) {
                $enregistrement_str .= $enregistrement[$key];
            }
            
            if (in_array($enregistrement_str, $enregistrements)) {
                $doublons[] = $enregistrement;
            }
            else {
                $enregistrements[] = $enregistrement_str;
            }
        }
        
        return $doublons;
    }
}
