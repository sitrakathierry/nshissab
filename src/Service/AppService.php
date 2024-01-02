<?php

namespace App\Service;

use App\Entity\AchBonCommande;
use App\Entity\AchDetails;
use App\Entity\AchHistoPaiement;
use App\Entity\AchMarchandise;
use App\Entity\AchStatut;
use App\Entity\AchStatutBon;
use App\Entity\AchType;
use App\Entity\AgdAcompte;
use App\Entity\AgdCategorie;
use App\Entity\AgdEcheance;
use App\Entity\Agence;
use App\Entity\Agenda;
use App\Entity\BtpElement;
use App\Entity\BtpEnoncee;
use App\Entity\CaisseCommande;
use App\Entity\CaissePanier;
use App\Entity\ChkCheque;
use App\Entity\CltHistoClient;
use App\Entity\CmdBonCommande;
use App\Entity\CmpBanque;
use App\Entity\CmpCompte;
use App\Entity\CmpOperation;
use App\Entity\CrdDetails;
use App\Entity\CrdFinance;
use App\Entity\CrdStatut;
use App\Entity\Depense;
use App\Entity\Devise;
use App\Entity\FactDetails;
use App\Entity\FactPaiement;
use App\Entity\FactType;
use App\Entity\Facture;
use App\Entity\IntLibelle;
use App\Entity\IntMateriel;
use App\Entity\IntMouvement;
use App\Entity\LctBail;
use App\Entity\LctBailleur;
use App\Entity\LctContrat;
use App\Entity\LctLocataire;
use App\Entity\LctPaiement;
use App\Entity\LctRepartition;
use App\Entity\LctStatutLoyer;
use App\Entity\LvrDetails;
use App\Entity\LvrLivraison;
use App\Entity\Menu;
use App\Entity\MenuUser;
use App\Entity\ModModelePdf;
use App\Entity\PrdApprovisionnement;
use App\Entity\PrdCategories;
use App\Entity\PrdDeduction;
use App\Entity\PrdEntrepot;
use App\Entity\PrdFournisseur;
use App\Entity\PrdHistoEntrepot;
use App\Entity\PrdPreferences;
use App\Entity\PrdSolde;
use App\Entity\PrdType;
use App\Entity\PrdVariationPrix;
use App\Entity\Produit;
use App\Entity\SavAnnulation;
use App\Entity\SavDetails;
use App\Entity\SavMotif;
use App\Entity\Service;
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
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
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
    private $user ;
    private $agence ;
    private $nameAgence ;

    public function __construct(SessionInterface $session,RouterInterface $router,RequestStack $requestStack, EntityManagerInterface $entityManager,UserPasswordEncoderInterface $encoder, UrlGeneratorInterface $urlGenerator)
    {
        $this->router = $router ;
        $this->requestStack = $requestStack ;
        $this->entityManager = $entityManager;
        $this->session = $session ;
        $this->encoder = $encoder ; 
        $this->urlGenerator = $urlGenerator ;
        $this->user = $this->session->get("user") ;
        if(!is_null($this->user))
        {
            $this->agence = $this->entityManager->getRepository(Agence::class)->find($this->user["agence"]) ; 
            $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        }  
    }

    public function getnameAgence()
    {
        return $this->nameAgence ;
    }

    public function getAgence()
    {
        return $this->agence ;
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
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');
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
        if($role == "AGENT")
        {
            $infoMenu = $this->entityManager
                        ->getRepository(MenuUser::class)
                        ->allMenuUserAgent($parent, $user->getAgence()->getId(),$user->getId()) ;
        }
        else if($role == "MANAGER")
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

    public function chiffrementCesar($texte_clair, $decalage) {
        $texte_chiffre = '';
        
        // Assurez-vous que le décalage est un entier
        $decalage = intval($decalage);
    
        // Convertir le décalage en une valeur positive dans l'intervalle [0, 25]
        $decalage = $decalage % 26;
    
        for ($i = 0; $i < strlen($texte_clair); $i++) {
            $char = $texte_clair[$i];
    
            if (ctype_alpha($char)) { // Vérifier si le caractère est une lettre
                $asciiOffset = ord(ctype_upper($char) ? 'A' : 'a');
                $chiffre_ascii = (($char - $asciiOffset + $decalage) % 26) + $asciiOffset;
                $texte_chiffre .= chr($chiffre_ascii);
            } else {
                // Conserver les caractères qui ne sont pas des lettres inchangés
                $texte_chiffre .= $char;
            }
        }
    
        return $texte_chiffre;
    }

    function dechiffrementCesar($texte_chiffre, $decalage) {
        // Utilisez le décalage négatif pour déchiffrer le texte
        return $this->chiffrementCesar($texte_chiffre, -$decalage);
    }

    public function encodeChiffre($chiffre) {
        $result = dechex($chiffre) ;
        return base64_encode($result) ;
        // return $this->chiffrementCesar($result,7) ; 
    }

    public function decoderChiffre($chiffrement) {
        // $result = $this->dechiffrementCesar(strval($chiffrement),7) ;
        $result = base64_decode($chiffrement) ;
        return hexdec($result) ;
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

    public function sendSms($numero,$entete, $contenu)
    {
        $basic  = new Basic("4c66ffd6", "XCiCWpI9qBqffTNA");
        $client = new Client($basic);

        $response = $client->sms()->send(
            new SMS($numero, $entete, $contenu)
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
                else if(intval($data[$i]) < 0)
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
            "agence" => $agence,
            "statut" => True 
        ]) ;

        $elements = [] ;
        
        foreach ($categories as $cat) {
            $cat->setNom(strtoupper($cat->getNom())) ;
            $this->entityManager->flush() ;

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
            "statut" => True,
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

    public static function comparerParNom($a, $b) {
        return strcmp($a['nom'], $b['nom']); // Comparaison des noms
    }
    
    public function generateStockPreferences($filename,$user)
    {
        $preferences = $this->entityManager->getRepository(PrdPreferences::class)->findBy([
            "user" => $user,
            "statut" => True
        ]) ;

        $elements = [] ;
        
        if(!is_null($preferences))
        {
            foreach ($preferences as $preference) {
                $element = [] ;
                $element["id"] = $preference->getId() ;
                $element["nom"] = $preference->getCategorie()->getNom() ;
                $element["categorie"] = $preference->getCategorie()->getId() ;
    
                array_push($elements,$element) ;
            } 
        }
        
        usort($elements, [self::class, 'comparerParNom']);

        file_put_contents($filename,json_encode($elements)) ;
    }

    public static function comparaisonDates($a, $b) {
        $dateA = \DateTime::createFromFormat('d/m/Y', $a['date']);
        $dateB = \DateTime::createFromFormat('d/m/Y', $b['date']);
        return $dateB <=> $dateA;
    }

    public function generatePrdListeApprovisionnement($filename, $agence)
    {
        $appros = $this->entityManager->getRepository(PrdApprovisionnement::class)->findBy([
            "agence" => $agence
        ]) ;
        
        $elements = [] ;
        
        foreach ($appros as $appro) {
            $element = [] ;

            $nomProduit = $appro->getVariationPrix()->getProduit()->getNom() ;
            $codeProduit = $appro->getVariationPrix()->getProduit()->getCodeProduit() ;
            $nomType = is_null($appro->getVariationPrix()->getProduit()->getType()) ? "NA" :$appro->getVariationPrix()->getProduit()->getType()->getNom() ;
            $prixVente = is_null($appro->getPrixVente()) ? $appro->getVariationPrix()->getPrixVente() : $appro->getPrixVente() ; 
            
            $element["id"] = $appro->getId() ;
            $element["date"] = is_null($appro->getDateAppro()) ? $appro->getCreatedAt()->format("d/m/Y") : $appro->getDateAppro()->format("d/m/Y") ;
            $element["entrepot"] = $appro->getHistoEntrepot()->getEntrepot()->getNom() ;
            $element["idEntrepot"] = $appro->getHistoEntrepot()->getId() ;
            $element["produit"] = $codeProduit." | ".$nomType." | ".$nomProduit ;
            $element["prixVente"] = $prixVente ;
            $element["quantite"] = $appro->getQuantite() ;
            $element["total"] = $appro->getQuantite() * $prixVente ;
            $element["dateExpiration"] = is_null($appro->getExpireeLe()) ? "-" : $appro->getExpireeLe()->format("d/m/Y") ;
            $element["nomProduit"] = $nomProduit ;
            $element["codeProduit"] = $codeProduit ;
            $element["nomType"] = $nomType ;
            $element["indice"] = is_null($appro->getVariationPrix()->getIndice()) ? "-" : $appro->getVariationPrix()->getIndice() ;
            $element["variation"] = $appro->getVariationPrix()->getId() ;
            $element["stock"] = $appro->getQuantite() ;

            array_push($elements,$element) ;
        } 

        usort($elements, [self::class, 'comparaisonDates']);

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateStockFournisseur($filename,$agence)
    {
        $fournisseurs = $this->entityManager->getRepository(PrdFournisseur::class)->findBy([
            "agence" => $agence,
            "statut" => True,
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
            "agence" => $agence,
            "statut" => True,
        ]) ;
        
        $elements = [] ;

        foreach ($stockGenerales as $stockGeneral) {
            $element = [] ;

            $element["id"] = $stockGeneral->getId() ;
            $element["encodedId"] = $this->encodeChiffre($stockGeneral->getId()) ;
            $element["idC"] = $stockGeneral->getPreference()->getId() ;
            $element["codeProduit"] = $stockGeneral->getCodeProduit() ;
            $element["categorie"] = $stockGeneral->getPreference()->getCategorie()->getNom() ;
            $element["nom"] = $stockGeneral->getNom() ;
            $element["stock"] = $stockGeneral->getStock() ;
            $element["tvaType"] = is_null($stockGeneral->getTvaType()) ? "-" : $stockGeneral->getTvaType()->getId() ;
            $element["agence"] = $stockGeneral->getAgence()->getId() ;
            $element["type"] = is_null($stockGeneral->getType()) ? "NA" : $stockGeneral->getType()->getId() ;
            $element["nomType"] = is_null($stockGeneral->getType()) ? "NA" : $stockGeneral->getType()->getNom() ;
            $element["images"] = is_null($stockGeneral->getImages()) ? "-" : $stockGeneral->getImages() ;

            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generatePrdVariationProduit($filename,$idProduit)
    {
        $produit = $this->entityManager->getRepository(Produit::class)->find($idProduit) ;

        $variations = $this->entityManager->getRepository(PrdVariationPrix::class)->findBy([
            "produit" => $produit,
            "statut" => True
        ]) ;

        $elements = [] ;

        foreach($variations as $variation)
        {
            $histoEntrepots = $this->entityManager->getRepository(PrdHistoEntrepot::class)->findBy([
                "variationPrix" => $variation,
                "statut" => True
            ]) ;

            $item = [];
            
            $indice = is_null($variation->getIndice()) ? "-" : $variation->getIndice() ;
            $cle = $indice."|".$variation->getPrixVente() ;
            foreach($histoEntrepots as $histoEntrepot)
            {
                if (array_key_exists($cle, $item))
                {
                    $item[$cle]["stock"] += $histoEntrepot->getStock() ;
                    $item[$cle]["entrepot"] .= ", ".$histoEntrepot->getEntrepot()->getNom()  ;

                    $elements[$cle] = $item[$cle] ;
                }
                else
                {
                    $solde = $this->entityManager->getRepository(PrdSolde::class)->findOneBy([
                        "variationPrix" => $variation,
                        "statut" => True,
                    ]) ;

                    $item[$cle] = [] ;
    
                    $item[$cle]["entrepot"] = $histoEntrepot->getEntrepot()->getNom()  ;
                    $item[$cle]["prix"] = $variation->getPrixVente() ;
                    $item[$cle]["prixVente"] = $variation->getPrixVente() ;
                    $item[$cle]["stock"] = $histoEntrepot->getStock() ;
                    $item[$cle]["code"] = $produit->getCodeProduit()."/".$indice ;
                    $item[$cle]["codeProduit"] = $produit->getCodeProduit() ;
                    $item[$cle]["indice"] = $indice ;
                    $item[$cle]["id"] = $variation->getId() ;
                    $item[$cle]["solde"] = is_null($solde) ? "-" : $solde->getCalculee() ;
                    $item[$cle]["soldeType"] = is_null($solde) ? "-" : $solde->getType()->getCalcul() ;
                    $item[$cle]["soldeQte"] = is_null($solde) ? "-" : $solde->getSolde() ;
                    $item[$cle]["soldeDate"] = is_null($solde) ? "-" : $solde->getDateLimite()->format("d/m/Y") ;
                    
                    $elements[$cle] = $item[$cle] ;
                }
            }
        }

        $elements = array_values($elements);

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generatePrdType($filename, $agence)
    {
        $types = $this->entityManager->getRepository(PrdType::class)->findBy([
            "agence" => $agence,
            "statut" => True
        ]) ;
        
        $elements = [] ;

        foreach ($types as $type) {
            $element = [] ;
            $element["id"] = $type->getId() ;
            $element["nom"] = $type->getNom() ;
            $element["agence"] = $type->getAgence()->getId() ;
            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generatePrdStockType($filename,$agence)
    {
        $stockGenerales = $this->entityManager->getRepository(Produit::class)->findBy([
            "agence" => $agence,
            "statut" => True,
            "anneeData" => date('Y'),
        ]) ;

        $types = $this->entityManager->getRepository(PrdType::class)->findBy([
            "agence" => $agence,
            "statut" => True
        ]) ;

        $stocks = [] ;
        $stocks["Non Assignee"]["stock"] = 0 ;
        $stocks["Non Assignee"]["encodedId"] = "NA" ;

        foreach ($types as $type) {
            $stocks[$type->getNom()]["stock"] = 0 ;
            $stocks[$type->getNom()]["encodedId"] = $this->encodeChiffre($type->getId()) ;
        }

        foreach ($stockGenerales as $stockGeneral) {
            $stockType = $stockGeneral->getType() ;

            if(is_null($stockType))
            {
                $stocks["Non Assignee"]["stock"] += $stockGeneral->getStock() ;
            }
            else
            {
                $stocks[$stockType->getNom()]["stock"]  += $stockGeneral->getStock() ;
            }
        }
        file_put_contents($filename,json_encode($stocks)) ;
    }

    public function generateProduitParategorie($filename,$agence, $user)
    {
        $stockGenerales = $this->entityManager->getRepository(Produit::class)->findBy([
            "agence" => $agence,
            "statut" => True,
        ]) ;

        $preferences = $this->entityManager->getRepository(PrdPreferences::class)->findBy([
            "user" => $user,
            "statut" => True
        ]) ;

        $stockCats = [] ;

        foreach($preferences as $preference)
        {
            $nomPref = strtolower($preference->getCategorie()->getNom()) ;
            $stockCats[$nomPref] = [] ;
            $stockCats[$nomPref]["stock"] = 0 ;
            $stockCats[$nomPref]["encodedId"] = $this->encodeChiffre($preference->getId()) ;
        }

        foreach($stockGenerales as $stockGenerale)
        {
            $prefProd = $stockGenerale->getPreference() ;
            if(!isset($stockCats[strtolower($prefProd->getCategorie()->getNom())]))
                $stockCats[strtolower($prefProd->getCategorie()->getNom())]["stock"] = $stockGenerale->getStock() ;
            else
                $stockCats[strtolower($prefProd->getCategorie()->getNom())]["stock"]  += $stockGenerale->getStock() ;
        }

        file_put_contents($filename,json_encode($stockCats)) ;
    }

    public function generateProduitParamTypeTva($path,$filename,$agence)
    {
        unlink($path) ; 
        if(!file_exists($path))
            $this->generateProduitStockGeneral($path, $agence) ;

        $stockGenerales = json_decode(file_get_contents($path)) ;
        
        $elements = [] ;

        foreach ($stockGenerales as $stockGenerale) {
            $element = [] ;
            $element["id"] = $stockGenerale->id ;
            $element["idC"] = $stockGenerale->idC ;
            $element["produit"] = $stockGenerale->codeProduit." | ".$stockGenerale->nom." | ".$stockGenerale->stock ;
            $element["categorie"] = $stockGenerale->categorie ;
            $element["tvaType"] = $stockGenerale->tvaType ;
            $element["agence"] = $stockGenerale->agence ; 
            array_push($elements,$element) ;
        } 

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateBonCommande($filename,$agence)
    {
        $bonCommandes = $this->entityManager->getRepository(CmdBonCommande::class)->findBy([
            "agence" => $agence
        ]) ;

        $elements = [] ;

        foreach ($bonCommandes as $bonCommande) {
            $factureDetails = $this->entityManager->getRepository(FactDetails::class)->findBy([
                "statut" => True,
                "facture" => $bonCommande->getFacture()
            ]) ;

            foreach ($factureDetails as $factureDetail) {
                $element = [] ;
                if($bonCommande->getFacture()->getClient()->getType()->getId() == 2)
                    $client = $bonCommande->getFacture()->getClient()->getClient()->getNom() ;
                else
                    $client = $bonCommande->getFacture()->getClient()->getSociete()->getNom() ;

                $tva = (($factureDetail->getPrix() * $factureDetail->getTvaVal()) / 100) * $factureDetail->getQuantite() ;
                $total = $factureDetail->getPrix() * $factureDetail->getQuantite()  ;

                $remiseVal = $this->getFactureRemise($factureDetail,$total) ;

                $total = $total - $remiseVal ;

                $typeRemise = is_null($factureDetail->getRemiseType()) ? "" : $factureDetail->getRemiseType()->getNotation() ;
                $typeRemise = ($typeRemise == "%") ? $typeRemise : "" ;
                $typeRemiseG = is_null($bonCommande->getFacture()->getRemiseType()) ? "" : $bonCommande->getFacture()->getRemiseType()->getNotation() ;
                $typeRemiseG = ($typeRemiseG == "%") ? $typeRemiseG : "" ;
                $element["id"] = $bonCommande->getId() ;
                $element["agence"] = $bonCommande->getAgence()->getId() ;
                $element["date"] = $bonCommande->getDate()->format('d/m/Y') ;
                $element["numBon"] = $bonCommande->getNumBonCmd() ;
                $element["client"] = $client ;
                $element["designation"] = $factureDetail->getDesignation() ;
                $element["qte"] = $factureDetail->getQuantite() ;
                $element["prix"] = $factureDetail->getPrix() ;
                $element["tva"] = ($tva == 0) ? "-" : $tva ; ;
                $element["remise"] = $factureDetail->getRemiseVal()." ".$typeRemise ;
                $element["total"] = $total ;
                $element["statut"] = $bonCommande->getStatut()->getNom() ;
                $element["refStatut"] = $bonCommande->getStatut()->getreference() ;
                $element["remiseG"] = $bonCommande->getFacture()->getRemiseVal()." ".$typeRemiseG ;
                $element["totalTva"] = $bonCommande->getFacture()->getTvaVal() ;
                $element["totalTtc"] = $bonCommande->getFacture()->getTotal() ;

                array_push($elements,$element) ;
            }
        } 

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateCommande($filename, $agence)
    {
        $bonCommandes = $this->entityManager->getRepository(CmdBonCommande::class)->findBy([
            "agence" => $agence
        ]) ;
        
        $elements = [] ;

        foreach ($bonCommandes as $bonCommande) {
            if($bonCommande->getFacture()->getClient()->getType()->getId() == 2)
                $client = $bonCommande->getFacture()->getClient()->getClient()->getNom() ;
            else
                $client = $bonCommande->getFacture()->getClient()->getSociete()->getNom() ;

            $element = [] ;
            $element["id"] = $bonCommande->getId() ;
            $element["numBon"] = $bonCommande->getNumBonCmd() ;
            $element["client"] = $client ;
            $element["facture"] = $bonCommande->getFacture()->getId() ;
            $element["statut"] = $bonCommande->getStatut()->getreference() ;
            $element["agence"] = $bonCommande->getAgence()->getId() ;
            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateLctRelevePaiementLoyer($filename, $contratId)
    {
        $contrat = $this->entityManager->getRepository(LctContrat::class)->find($contratId) ;

        $pourcentage = is_null($contrat->getPourcentage()) ? 0 : $contrat->getPourcentage() ;

        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        
        $statutLoyerPaye = $this->entityManager->getRepository(LctStatutLoyer::class)->findOneBy([
            "reference" => "PAYE"
        ]) ;

        $statutLoyerAcompte = $this->entityManager->getRepository(LctStatutLoyer::class)->findOneBy([
            "reference" => "ACOMPTE"
        ]) ;

        $repartitions = $this->entityManager->getRepository(LctRepartition::class)->findBy([
            "contrat" => $contrat,
            "statut" => $statutLoyerPaye
        ]) ;

        $lastRepartition = $this->entityManager->getRepository(LctRepartition::class)->findOneBy([
            "contrat" => $contrat,
            "statut" => $statutLoyerAcompte
        ],["id" => "DESC"]) ;

        $childs = [] ;
        
        $totalReleve = 0 ;
        foreach ($repartitions as $repartition) {
            $item = [] ;

            $statutRepart = $repartition->getStatut()->getReference() ; 
            if($statutRepart == "CAUTION")
                continue ;

            $montant = is_null($repartition->getMontant()) ? 0 : $repartition->getMontant() ;
            $commission =  ($pourcentage * $montant) / 100 ;
            $versement = '<button value="'.$repartition->getId().'" data-commission="'.($montant - $commission).'" class="btn btn-outline-success lct_check_versement btn-sm font-smaller"><i class="fa fa-hand-holding-dollar"></i></button>' ;
            $selection = '<button data-value="'.$repartition->getId().'" class="btn btn-outline-info lct_select_impression btn-sm font-smaller"><i class="fa fa-check"></i></button>' ;
            
            if(!is_null($repartition->getVersement()))
            {
                $versement = '<b class="text-success">OK</b>' ;
            }

            $item["id"] = $repartition->getId() ;
            $item["designation"] = "Paiement. ".$repartition->getDesignation() ;
            $item["debutLimite"] = is_null($repartition->getDateDebut()) ? "" : $repartition->getDateDebut()->format("d/m/Y") ;
            $item["dateLimite"] = is_null($repartition->getDateLimite()) ? "" : $repartition->getDateLimite()->format("d/m/Y") ;
            $item["datePaiement"] = $repartition->getPaiement()->getDate()->format("d/m/Y") ;
            $item["mois"] = is_null($repartition->getMois()) ? "" : $tabMois[$repartition->getMois() - 1]  ;
            $item["annee"] = $repartition->getAnnee() ;
            $item["dateDebut"] = is_null($repartition->getDateDebut()) ? "NONE" : $repartition->getDateDebut()->format("d/m/Y") ;
            $item["montant"] = $repartition->getMontant() ;
            $item["statut"] = $repartition->getStatut()->getReference() ;
            $item["commission"] = $commission." (".$pourcentage."%)";
            $item["commissionVal"] = $commission;
            $item["versement"] = $versement ;
            $item["versementVal"] = $montant - $commission ;
            $item["selection"] = $selection;
            
            $totalReleve += $repartition->getMontant() ; 
            array_push($childs,$item) ;
        }

        if(!is_null($lastRepartition))
        {
            $textDesignation = "Acompte. ".$lastRepartition->getDesignation() ;
            $montant = is_null($lastRepartition->getMontant()) ? 0 : $lastRepartition->getMontant();
            $commission =  ($pourcentage * $montant) / 100 ;
            $versement = "-" ;
            $selection = "-" ;

            $lastItem = [
                "id" => $lastRepartition->getId(),
                "designation" => $textDesignation,
                "debutLimite" => is_null($lastRepartition->getDateDebut()) ? "" : $lastRepartition->getDateDebut()->format("d/m/Y") ,
                "dateLimite" => is_null($lastRepartition->getDateLimite()) ? "" : $lastRepartition->getDateLimite()->format("d/m/Y") ,
                "datePaiement" => $lastRepartition->getPaiement()->getDate()->format("d/m/Y"),
                "mois" => is_null($lastRepartition->getMois()) ? "" : $tabMois[$lastRepartition->getMois() - 1] ,
                "annee" => $lastRepartition->getAnnee(),
                "dateDebut" => is_null($lastRepartition->getDateDebut()) ? "NONE" : $lastRepartition->getDateDebut()->format("d/m/Y"),
                "montant" => $lastRepartition->getMontant(),
                "statut" => $lastRepartition->getStatut()->getReference(),
                "commission" => $commission." (".$pourcentage."%)",
                "commissionVal" => $commission,
                "versement" => $versement,
                "versementVal" => 0,
                "selection" => $selection
            ] ;

            $totalReleve += $lastRepartition->getMontant() ; 
            array_push($childs,$lastItem) ;

            
        }

        $frequence = is_null($contrat->getFrequenceRenouv()) ? 1 : $contrat->getFrequenceRenouv() ; 

        $response = [] ;
        $moisDebut = $contrat->getDateDebut()->format("m") ;
        $anneeDebut = $contrat->getDateDebut()->format("Y") ;
        $indexMois = intval($moisDebut) - 1 ;

        if($contrat->getCycle()->getReference() == "CMOIS")
        {
            if($contrat->getForfait()->getReference() == "FMOIS")
            {
                $dateDebut = $contrat->getDateDebut()->format("d/m/Y") ;

                if($contrat->getPeriode()->getReference() == "M")
                    $duree = $contrat->getDuree() * $frequence; 
                else if($contrat->getPeriode()->getReference() == "A")
                    $duree = $contrat->getDuree() * $frequence * 12 ;

                $dateAvant = $this->calculerDateAvantNjours($dateDebut,30) ; 
                $dateGenere = $contrat->getModePaiement()->getReference() == "DEBUT" ? $dateAvant : $dateDebut ;
                $tableauMois = $this->genererTableauMois($dateGenere,$duree, $contrat->getDateLimite(),null) ;
                
                $count = count($tableauMois);
                
                for ($i=0; $i < $count; $i++) { 
                    if ($i + 1 < $count && $tableauMois[$i]["indexMois"] > $tableauMois[$i + 1]["indexMois"]) {
                        // dd("passe".$i) ;
                        $tableauMois[$i + 1]["annee"] = $tableauMois[$i]["annee"] + 1;
                    }

                    $tableauMois[$i]["id"] = "-" ;
                    $tableauMois[$i]["designation"] = "LOYER ".$contrat->getBail()->getNom()." | ".$contrat->getBail()->getLieux() ;
                    $tableauMois[$i]["datePaiement"] = "-" ;
                    $tableauMois[$i]["commission"] = "-" ;
                    $tableauMois[$i]["commissionVal"] = 0 ;
                    $tableauMois[$i]["versement"] = "-" ;
                    $tableauMois[$i]["versementVal"] = 0 ;
                    $tableauMois[$i]["selection"] = "-" ;
                    $tableauMois[$i]["mois"] = $tabMois[$indexMois] ;
                    $tableauMois[$i]["annee"] = $anneeDebut ;
                    
                    foreach ($childs as $child) {
                        if($child["debutLimite"] == $tableauMois[$i]["debutLimite"])
                        {
                            $tableauMois[$i]["id"] = $child["id"] ;
                            $tableauMois[$i]["datePaiement"] = $child["datePaiement"] ;
                            $tableauMois[$i]["commission"] = $child["commission"] ;
                            $tableauMois[$i]["commissionVal"] = $child["commissionVal"] ;
                            $tableauMois[$i]["versement"] = $child["versement"] ;
                            $tableauMois[$i]["versementVal"] = $child["versementVal"] ;
                            $tableauMois[$i]["selection"] = $child["selection"] ;
                            $tableauMois[$i]["montant"] = $child["montant"] ;
                            $tableauMois[$i]["designation"] = $child["designation"] ;
                            break;
                        }
                    }
                    
                    $indexMois++ ;
                    if($indexMois >= 12 )
                    {
                        $indexMois = 0 ;
                        $anneeDebut++ ;
                    }
                }

                $response = $tableauMois ;
            } 
        }
        else if($contrat->getCycle()->getReference() == "CJOUR")
        { 
            if($contrat->getForfait()->getReference() == "FJOUR")
            {

                $dateDebut = $contrat->getDateDebut()->format("d/m/Y") ;
                
                $dateAvant = $this->calculerDateAvantNjours($dateDebut,1) ;
                $dateGenere = $dateAvant ;
                $duree = $contrat->getDuree() * $frequence;
                $tableauMois = $this->genererTableauJour($dateGenere,$duree) ;

                for ($i=0; $i < count($tableauMois); $i++) { 
                    $tableauMois[$i]["id"] = "-" ;
                    $tableauMois[$i]["designation"] = "LOYER ".$contrat->getBail()->getNom()." | ".$contrat->getBail()->getLieux() ;
                    $tableauMois[$i]["datePaiement"] = "-" ;
                    $tableauMois[$i]["commission"] = "-" ;
                    $tableauMois[$i]["commissionVal"] = 0 ;
                    $tableauMois[$i]["versement"] = "-" ;
                    $tableauMois[$i]["versementVal"] = 0 ;
                    $tableauMois[$i]["selection"] = "-" ;
                    $tableauMois[$i]["mois"] = $tabMois[$indexMois] ;
                    $tableauMois[$i]["annee"] = $anneeDebut ;

                    foreach ($childs as $child) {
                        if($child["debutLimite"] == $tableauMois[$i]["debutLimite"])
                        {
                            $tableauMois[$i]["id"] = $child["id"] ;
                            $tableauMois[$i]["datePaiement"] = $child["datePaiement"] ;
                            $tableauMois[$i]["commission"] = $child["commission"] ;
                            $tableauMois[$i]["commissionVal"] = $child["commissionVal"] ;
                            $tableauMois[$i]["versement"] = $child["versement"] ;
                            $tableauMois[$i]["versementVal"] = $child["versementVal"] ;
                            $tableauMois[$i]["selection"] = $child["selection"] ;
                            $tableauMois[$i]["montant"] = $child["montant"] ;
                            $tableauMois[$i]["designation"] = $child["designation"] ;
                            break;
                        }
                    }
                    $indexMois++ ;
                    if($indexMois >= 12 )
                    {
                        $indexMois = 0 ;
                        $anneeDebut++ ;
                    }
                }

                $response = $tableauMois ;
            }
        } 

        if($contrat->getForfait()->getReference() == "FORFAIT")
        {
            $response =  $childs ;
        }

        file_put_contents($filename,json_encode($response)) ;
    }

    public function getAgenceDevise($agence) 
    {
        $element = [] ;

        $element["symbole"] = is_null($agence->getDevise()) ? "" : $agence->getDevise()->getSymbole() ;
        $element["lettre"] = is_null($agence->getDevise()) ? "" : $agence->getDevise()->getLettre() ;

        return $element ;
    }

    public static function comparaisonMultiple($a, $b) {
        // Comparaison par entrepot
        $result = strcmp($a['nomType'], $b['nomType']);
        
        if ($result !== 0) {
            return $result;
        }
        
        // Comparaison par categorie
        $result = strcmp($a['categorie'], $b['categorie']);
        
        if ($result !== 0) {
            return $result;
        }
        
        // Comparaison par nomType
        return strcmp($a['entrepot'], $b['entrepot']);
    }

    public function generateStockInEntrepot($filename,$agence)
    {
        $stockEntrepots = $this->entityManager->getRepository(PrdHistoEntrepot::class)->findBy([
            "agence" => $agence,
            "statut" => True,
        ],[
            "entrepot" => "ASC"
        ]) ;
        
        $elements = [] ;

        foreach ($stockEntrepots as $stockEntrepot) {
            $element = [] ;
            $element["id"] = $stockEntrepot->getId() ;
            $element["idE"] = $stockEntrepot->getEntrepot()->getId() ;
            $element["idC"] = $stockEntrepot->getVariationPrix()->getProduit()->getPreference()->getId() ;
            $element["idP"] = $stockEntrepot->getVariationPrix()->getProduit()->getId() ;
            $element["encodedIdVar"] = $this->encodeChiffre($stockEntrepot->getVariationPrix()->getId()) ;
            $element["entrepot"] = $stockEntrepot->getEntrepot()->getNom() ;
            $element["code"] = $stockEntrepot->getVariationPrix()->getProduit()->getCodeProduit() ;
            $element["indice"] = !empty($stockEntrepot->getVariationPrix()->getIndice()) ? $stockEntrepot->getVariationPrix()->getIndice() : "-" ;
            $element["categorie"] = $stockEntrepot->getVariationPrix()->getProduit()->getPreference()->getCategorie()->getNom() ;
            $element["nom"] = $stockEntrepot->getVariationPrix()->getProduit()->getNom() ;
            $element["nomType"] = is_null($stockEntrepot->getVariationPrix()->getProduit()->getType()) ? "NA" : $stockEntrepot->getVariationPrix()->getProduit()->getType()->getNom() ;
            $element["stock"] = $stockEntrepot->getStock() ;
            $element["prixVente"] = $stockEntrepot->getVariationPrix()->getPrixVente() ;
            array_push($elements,$element) ;
        }

        usort($elements, [self::class,'comparaisonMultiple']);

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generatePrdGenEntrepot($filename,$agence)
    {
        $entrepots = $this->entityManager->getRepository(PrdEntrepot::class)->findBy([
            "agence" => $agence,
            "statut" => True,
        ]) ;

        $elements = [] ;
        
        foreach($entrepots as $entrepot)
        {
            $stockEntrepots = $this->entityManager->getRepository(PrdHistoEntrepot::class)->findBy([
                "entrepot" => $entrepot,
                "statut" => True
            ]) ;

            $element = [] ;
            
            $element["id"] = $entrepot->getId() ;
            $element["encodedId"] = $this->encodeChiffre($entrepot->getId()) ;
            $element["nom"] = $entrepot->getNom() ;
            $element["stock"] = 0 ;
            foreach ($stockEntrepots as $stockEntrepot) {
                $element["stock"] += $stockEntrepot->getStock() ;
            }
            array_push($elements,$element) ;
        }
        file_put_contents($filename,json_encode($elements)) ;
    }

    public static function comparaisonCaissePanier($a, $b) {

        $dateA = \DateTime::createFromFormat('d/m/Y', $a['date']);
        $dateB = \DateTime::createFromFormat('d/m/Y', $b['date']);

        return  $dateB <=> $dateA;
    }
 
    public function generateCaissePanierCommande($filename,$agence)
    {
        $panierCommandes = $this->entityManager->getRepository(CaissePanier::class)->findBy([
            "agence" => $agence,
            "statut" => True,
        ],["id" => "DESC"]) ;

        $elements = [] ;

        foreach ($panierCommandes as $panierCommande) {
            $element = [] ;

            if($panierCommande->getCommande()->getDate()->format('Y') != date('Y'))
                continue ;

            $element["id"] = $panierCommande->getCommande()->getId() ;
            $element["date"] = $panierCommande->getCommande()->getDate()->format('d/m/Y') ;
            $element["currentDate"] = $panierCommande->getCommande()->getDate()->format('d/m/Y') ;
            $element["dateDeclaration"] = $panierCommande->getCommande()->getDate()->format('d/m/Y') ;
            $element["dateDebut"] = $panierCommande->getCommande()->getDate()->format('d/m/Y') ;
            $element["dateFin"] = $panierCommande->getCommande()->getDate()->format('d/m/Y') ;
            $element["annee"] = $panierCommande->getCommande()->getDate()->format('Y') ;
            $element["mois"] = $panierCommande->getCommande()->getDate()->format('m') ;
            $element["numCommande"] = $panierCommande->getCommande()->getNumCommande() ;
            $element["codeProduit"] = $panierCommande->getVariationPrix()->getProduit()->getCodeProduit() ;
            $element["nom"] = $panierCommande->getVariationPrix()->getProduit()->getNom() ;
            $element["quantite"] = $panierCommande->getQuantite()   ;
            $element["prix"] = $panierCommande->getPrix() ;
            $element["indice"] = is_null($panierCommande->getVariationPrix()->getIndice()) ? "-" : $panierCommande->getVariationPrix()->getIndice() ;
            $element["idP"] = $panierCommande->getVariationPrix()->getProduit()->getId() ;
            $element["tva"] = $panierCommande->getTva() ;
            $element["totalTva"] = $panierCommande->getCommande()->getTva() ;
            $element["montantRecu"] = $panierCommande->getCommande()->getMontantRecu() ;
            $element["montantPayee"] = $panierCommande->getCommande()->getMontantPayee() ;
            $element["remiseVal"] = is_null($panierCommande->getCommande()->getRemiseValeur()) ? 0 : $panierCommande->getCommande()->getRemiseValeur() ;
            $element["remiseType"] = is_null($panierCommande->getCommande()->getRemiseType()) ? "-" : $panierCommande->getCommande()->getRemiseType()->getCalcul() ;
            $element["user"] = $panierCommande->getCommande()->getUser()->getId() ;

            array_push($elements,$element) ;
        }

        usort($elements, [self::class, 'comparaisonCaissePanier']);

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateCaisseCommande($filename, $agence) 
    {
        $commandes = $this->entityManager->getRepository(CaisseCommande::class)->findBy([
            "agence" => $agence,
            "statut" => True
        ],["date" => "DESC"]) ;
        
        $elements = [] ;

        foreach ($commandes as $commande) {
            $element = [] ;
            $element["id"] = $commande->getId() ;
            $element["agence"] = $commande->getAgence()->getId() ;
            $element["user"] = $commande->getUser()->getId() ;
            $element["numCommande"] = $commande->getNumCommande() ;
            $element["montantRecu"] = $commande->getMontantRecu() ;
            $element["montantPayee"] = $commande->getMontantPayee();
            $element["totalTva"] = $commande->getTva();
            $element["montant"] = $commande->getMontantPayee();
            $element["date"] = $commande->getDate()->format('d/m/Y') ;
            array_push($elements,$element) ; 
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateModModelePdf($filename,$user)
    {
        $modelePdfs = $this->entityManager->getRepository(ModModelePdf::class)->findBy([
            "user" => $user,
            "statut" => True,
        ],
        [ "id" => "DESC" ]) ;
        
        $elements = [] ;
        foreach ($modelePdfs as $modelePdf) {
            $element = [] ;
            $element["id"] = $modelePdf->getId() ;
            $element["encodedId"] = $this->encodeChiffre($modelePdf->getId()) ;
            $element["user"] = $modelePdf->getUser()->getId() ;
            $element["nom"] = $modelePdf->getNom() ;
            $element["type"] = $modelePdf->getType() == "ENTETE" ? "Entête de page" : "Bas de page" ;
            $element["refType"] = $modelePdf->getType() ;
            $element["statut"] = $modelePdf->isStatut();

            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateFacture($filename, $agence)
    {
        $factures = $this->entityManager->getRepository(Facture::class)->findBy([
            "agence" => $agence,
            "statut" => True,
        ],[
            "id" => "DESC"
            ]
        ) ; 

        $elements = [] ;

        foreach ($factures as $facture) {
            $element = [] ;
            $specification = "NONE" ; 
            $element["id"] = $facture->getId() ;
            $element["idC"] = $facture->getClient()->getId() ;
            $element["idT"] = $facture->getType()->getId() ;
            $element["idM"] = $facture->getModele()->getId() ;
            $element["mois"] = $facture->getDate()->format('m') ;
            $element["annee"] = $facture->getDate()->format('Y') ;
            $element["date"] = $facture->getDate()->format('d/m/Y') ;
            $element["currentDate"] = $facture->getDate()->format('d/m/Y') ;
            $element["dateDebut"] = $facture->getDate()->format('d/m/Y') ;
            $element["dateFin"] = $facture->getDate()->format('d/m/Y') ;
            $element["agence"] = $facture->getAgence()->getId() ;
            $element["user"] = $facture->getUser()->getId() ;
            $element["numFact"] = $facture->getNumFact() ;
            $element["modele"] = $facture->getModele()->getNom() ;
            $element["type"] = $facture->getType()->getNom() ;
            $element["refType"] = $facture->getType()->getReference() ;
            $element["dateCreation"] = $facture->getCreatedAt()->format('d/m/Y')  ;
            $element["dateFacture"] = $facture->getDate()->format('d/m/Y')  ;
            $element["client"] = $this->getFactureClient($facture)["client"] ;
            $element["total"] = $facture->getTotal() ;
            $element["specification"] = $specification ;
            $element["nature"] = "FACTURE";

            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateParamGeneral($filename, $agence)
    {
        $devises = $this->entityManager->getRepository(Devise::class)->findBy([
            "agence" => $agence,
            "statut" => True
        ]) ;

        $elements = [] ;

        foreach ($devises as $devise) {
            $element = [] ;
            $element["id"] = $devise->getId() ;
            $element["agence"] = $devise->getAgence()->getId() ;
            $element["symbole"] = $devise->getSymbole() ;
            $element["lettre"] = $devise->getLettre() ;
            $element["montantBase"] = $devise->getMontantBase() ;
            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function objectToArray($objects)
    {
        $elements = [] ;
        foreach($objects as $item)
        {
            $elements[] = (array)$item ;
        }

        return $elements ;
    }

    public function generateInterneMateriel($filename,$agence) 
    {
        $materiels = $this->entityManager->getRepository(IntMateriel::class)->findBy([
            "agence" => $agence,
            "statut" => True
        ]) ;

        $elements = [] ;

        foreach ($materiels as $materiel) {
            $element = [] ;

            $element["id"] = $materiel->getId() ;
            $element["agence"] = $materiel->getAgence()->getId() ;
            $element["nom"] = $materiel->getNom() ;
            $element["libelle"] = $materiel->getLibelle()->getNom() ;
            $element["quantite"] = $materiel->getQuantite() ;
            $element["unite"] = $materiel->getUnite() ;
            $element["stock"] = $materiel->getStock() ;

            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateInterneLibelle($filename,$agence)
    {
        $libelles = $this->entityManager->getRepository(IntLibelle::class)->findBy([
            "agence" => $agence,
        ]) ;

        $elements = [] ;

        foreach ($libelles as $libelle) {
            $element = [] ;

            $element["id"] = $libelle->getId() ;
            $element["agence"] = $libelle->getAgence()->getId() ;
            $element["nom"] = $libelle->getNom() ;

            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateInterneMouvement($filename,$agence)
    {
        $mouvements = $this->entityManager->getRepository(IntMouvement::class)->findBy([
            "agence" => $agence,
        ]) ;

        $elements = [] ;

        foreach ($mouvements as $mouvement) {
            $element = [] ;

            $element["id"] = $mouvement->getId() ;
            $element["agence"] = $mouvement->getAgence()->getId() ;
            $element["date"] = $mouvement->getDate()->format("d/m/Y") ;
            $element["designation"] = $mouvement->getDesignation() ;
            $element["stock"] = $mouvement->getStock() ;
            $element["refType"] = $mouvement->getType()->getReference() ;

            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateBonLivraison($filename, $agence)
    {
        $lvrDetails = $this->entityManager->getRepository(LvrDetails::class)->findBy([
            "statut" => True,
            "agence" => $agence
        ]) ;

        $elements = [] ;

        foreach ($lvrDetails as $lvrDetail) {
            if($lvrDetail->getLivraison()->getTypeSource() =="Facture")
            {
                $facture = $this->entityManager->getRepository(Facture::class)->find($lvrDetail->getLivraison()->getSource()) ;
                if($facture->getClient()->getType()->getId() == 2)
                    $client = $facture->getClient()->getClient()->getNom() ;
                else
                    $client = $facture->getClient()->getSociete()->getNom() ;
            }
            else
            {
                $bonCommande = $this->entityManager->getRepository(CmdBonCommande::class)->find($lvrDetail->getLivraison()->getSource()) ;
                if($bonCommande->getFacture()->getClient()->getType()->getId() == 2)
                    $client =   $bonCommande->getFacture()->getClient()->getClient()->getNom() ;
                else
                    $client = $bonCommande->getFacture()->getClient()->getSociete()->getNom() ;
            }
            
            $element = [] ;
            $element["id"] = $lvrDetail->getLivraison()->getId() ;
            $element["numBonLvr"] = $lvrDetail->getLivraison()->getNumLivraison() ;
            $element["source"] = $lvrDetail->getLivraison()->getSource() ;
            $element["typeSource"] = $lvrDetail->getLivraison()->getTypeSource() ;
            $element["client"] = $client ;
            $element["designation"] = $lvrDetail->getFactureDetail()->getDesignation() ;
            $element["quantite"] = $lvrDetail->getFactureDetail()->getQuantite() ;
            $element["agence"] = $lvrDetail->getAgence()->getId() ;
            $element["date"] = $lvrDetail->getLivraison()->getDate()->format('d/m/Y') ;
            $element["lieu"] = $lvrDetail->getLivraison()->getLieu() ;
            $element["statut"] = $lvrDetail->getLivraison()->getStatut()->getNom() ;
            $element["refStatut"] = $lvrDetail->getLivraison()->getStatut()->getreference() ;
            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function genererSavMotif($filename, $agence)
    {
        $motifs = $this->entityManager->getRepository(SavMotif::class)->findBy([
            "statut" => True,
            "agence" => $agence
        ]) ;

        $elements = [] ;

        foreach ($motifs as $motif) {
            $element = [] ;
            $element["id"] = $motif->getId() ;
            $element["agence"] = $motif->getAgence()->getId() ;
            $element["nom"] = $motif->getNom() ;
            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateSavAnnulation($filename, $agence)
    {
        $annulations = $this->entityManager->getRepository(SavAnnulation::class)->findBy([
            "statut" => True,
            "agence" => $agence,
        ]) ;

        $elements = [] ;

        foreach ($annulations as $annulation) {
            $facture = $annulation->getFacture() ;
            if(!is_null($facture))
            {
                $client = $this->getFactureClient($facture) ;
                $nomClient = $client["client"] ;
                $idClient = $facture->getClient()->getId() ;
                $idFacture = $facture->getId() ;
            }
            else
            {
                $nomClient = "-" ;
                $idClient = "-" ;
                $idFacture = "-" ;
            }

            $total = $annulation->getMontant() ;

            $retenu = 0 ;
            if($annulation->getPourcentage() == 0)
            {
                $retenu = "-" ;
                $signe = "" ;
                $remboursee = $annulation->getMontant() ;
            }
            else
            {
                $retenu = ($annulation->getMontant() * $annulation->getPourcentage()) / 100 ;
                $signe = "(".$annulation->getPourcentage()."%)" ;
                $remboursee = $annulation->getMontant() - $retenu ;
            }

            $element = [] ;
            $element["id"] = $annulation->getId() ;
            $element["agence"] = $annulation->getAgence()->getId() ;
            $element["user"] = $annulation->getUser()->getId() ;
            $element["date"] = $annulation->getDate()->format('d/m/Y') ;
            $element["annee"] = $annulation->getDate()->format('Y') ;
            $element["lieu"] = $annulation->getLieu() ;
            $element["typeAffiche"] = is_null($annulation->getFacture()) ? "CAISSE" : "FACTURE" ;
            $element["numero"] = $annulation->getNumFact() ;
            $element["client"] = $nomClient ;
            $element["idC"] = $idClient ;
            $element["idF"] = $idFacture ;
            $element["type"] = $annulation->getType()->getNom() ;
            $element["motif"] = $annulation->getMotif()->getNom()  ;
            $element["spec"] = $annulation->getSpecification()->getNom() ;
            $element["refSpec"] = $annulation->getSpecification()->getReference() ;
            $element["total"] = $total ;
            $element["retenu"] = $retenu ;
            $element["signe"] = $signe ;
            $element["remboursee"] = $remboursee ;

            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateCredit($filename, $agence,$refPaiement)
    {
        $paiement = $this->entityManager->getRepository(FactPaiement::class)->findOneBy([
            "reference" => $refPaiement
            ]) ;

        $finances = $this->entityManager->getRepository(CrdFinance::class)->findBy([
                "agence" => $agence,
                "paiement" => $paiement
        ],["id" => "DESC"]) ;

        $elements = [] ;
        foreach ($finances as $finance) {

            if($finance->getCreatedAt()->format('Y') != date('Y'))
            {
                if($finance->getStatut()->getReference() != 'ECR')
                {
                    continue;
                }
            }
                
            $client = $this->getFactureClient($finance->getFacture())["client"] ;
            $facture = $finance->getFacture() ;
            $totalPayee = $this->entityManager->getRepository(CrdDetails::class)->getFinanceTotalPayee($finance->getId()) ;

            $factureDetails = $this->entityManager->getRepository(FactDetails::class)->findBy([
                "statut" => True,
                "facture" => $finance->getFacture()
            ]) ;

            foreach ($factureDetails as $factureDetail) {
                $element = [] ;

                $tva = (($factureDetail->getPrix() * $factureDetail->getTvaVal()) / 100) * $factureDetail->getQuantite() ;
                $total = $factureDetail->getPrix() * $factureDetail->getQuantite()  ;

                $remiseVal = $this->getFactureRemise($factureDetail,$total) ;
                
                $total = $total - $remiseVal ;

                $typeRemise = is_null($factureDetail->getRemiseType()) ? "" : $factureDetail->getRemiseType()->getNotation() ;
                $typeRemise = ($typeRemise == "%") ? $typeRemise : "" ;
                $typeRemiseG = is_null($finance->getFacture()->getRemiseType()) ? "" : $finance->getFacture()->getRemiseType()->getNotation() ;
                $typeRemiseG = ($typeRemiseG == "%") ? $typeRemiseG : "" ;
                $element["id"] = $finance->getId() ;
                $element["idStatut"] = $finance->getStatut()->getId() ;
                $element["refPaiement"] = $finance->getPaiement()->getReference() ;
                $element["idC"] = $facture->getClient()->getId() ;
                $element["mois"] = $facture->getDate()->format('m')   ;
                $element["annee"] = $facture->getDate()->format('Y')   ;
                $element["currentDate"] = $facture->getDate()->format('d/m/Y')  ;
                $element["dateDebut"] = $facture->getDate()->format('d/m/Y')   ;
                $element["dateFin"] = $facture->getDate()->format('d/m/Y')   ;
                $element["dateFacture"] = $facture->getDate()->format('d/m/Y')  ;
                $element["agence"] = $finance->getAgence()->getId() ;
                $element["date"] = $finance->getFacture()->getDate()->format('d/m/Y') ;
                $element["numFnc"] = $finance->getNumFnc() ;
                $element["client"] = $client ;
                $element["designation"] = $factureDetail->getDesignation() ;
                $element["qte"] = $factureDetail->getQuantite() ;
                $element["prix"] = $factureDetail->getPrix() ;
                $element["tva"] = ($tva == 0) ? "-" : $tva ; ;
                $element["remise"] = $factureDetail->getRemiseVal()." ".$typeRemise ;
                $element["total"] = $total ;
                $element["statut"] = $finance->getStatut()->getNom() ;
                $element["refStatut"] = $finance->getStatut()->getreference() ;
                $element["remiseG"] = $finance->getFacture()->getRemiseVal()." ".$typeRemiseG ;
                $element["totalTva"] = $finance->getFacture()->getTvaVal() ;
                $element["totalTtc"] = $finance->getFacture()->getTotal() ;
                $element["totalPayee"] = $totalPayee["total"] ;

                array_push($elements,$element) ;
            }
        }

        file_put_contents($filename,json_encode($elements)) ;
        
    }

    public function generatePrestationService($filename, $agence)
    {
        $services = $this->entityManager->getRepository(Service::class)->findBy([
            "statut" => True,
            "agence" => $agence
        ],
        [
            "nom" => "ASC"
            ]) ;

        $elements = [] ;

        foreach ($services as $service) {
            $element = [] ;
            $element["id"] = $service->getId() ;
            $element["agence"] = $service->getAgence()->getId() ;
            $element["nom"] = $service->getNom() ;
            $element["description"] = $service->getDescription() ;
            array_push($elements,$element) ;
        }

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateAgenda($filename, $agence)
    {
        $agendas = $this->entityManager->getRepository(Agenda::class)->findBy([
            "agence" => $agence
            ]) ;
        // Statut de l'agenda sur evènement et rendez-vous
            // -   En cours : 1
            // -   Terminé : 0

        $elements = [] ;

        foreach ($agendas as $agenda) {
            $element = [] ;
            $element["date"] = $agenda->getDate()->format('Y-m-d') ;
            $markup = '' ;
            $refType = $agenda->getType()->getReference() ;
            $statut = $agenda->isStatut() ;
            $icone = $refType == "EVT" ? "fa-star" : "fa-clock" ;
            if($statut)
            {
                $markup = "<span class=\"badge bg-purple m-1 font-smaller p-1 text-white\"><i class=\"fa $icone\"></i></span>" ;
            }
            else
            {
                $markup = "<span class=\"badge bg-dark m-1 font-smaller p-1 text-white\"><i class=\"fa $icone\"></i></span>" ;

            }
            $element["markup"] = $markup ;
            array_push($elements,$element) ;
        }

        // AGENDA FINANCIERE : CREDIT

        $refCategorie = "CRD" ;

        $categorie = $this->entityManager->getRepository(AgdCategorie::class)->findOneBy([
            "reference" => $refCategorie
            ]) ;

        $echeances = $this->entityManager->getRepository(AgdEcheance::class)->findBy([
            "agence" => $this->agence,
            "categorie" => $categorie
            ]) ;
        
        foreach ($echeances as $echeance) {
            $element = [] ;
            $element["date"] = $echeance->getDate()->format('Y-m-d') ;
            $markup = '' ;

            // Tous les statut sont : 
            //     - En cours : 1
            //     - Soldé : 0
            //     - En souffrance : NULL

            $statutEcheance = $echeance->isStatut() ;

            if($statutEcheance)
            {
                $markup = "<span class=\"badge bg-info m-1 font-smaller p-1 text-white\"><i class=\"fa fa-percent\"></i></span>" ;
            }
            else if(is_null($statutEcheance))
            {
                $markup = "<span class=\"badge bg-danger m-1 font-smaller p-1 text-white\"><i class=\"fa fa-percent\"></i></span>" ;
            }
            else
            {
                $markup = "<span class=\"badge bg-dark m-1 font-smaller p-1 text-white\"><i class=\"fa fa-percent\"></i></span>" ;
            }
            $element["markup"] = $markup ;
            array_push($elements,$element) ;
        }

        // GESTION DE L'ACOMPTE SUR L'AGENDA

        $agendaAcomptes = $this->entityManager->getRepository(AgdAcompte::class)->findBy([
            "agence" => $agence
        ]) ;

        foreach ($agendaAcomptes as $agendaAcompte) {
            $element = [] ;
            $element["date"] = $agendaAcompte->getDate()->format('Y-m-d') ;
            $markup = '' ;

            // Tous les statut sont : 
            //     - En cours : 1
            //     - Soldé : 0
            //     - En souffrance : NULL

            $statutAgdAcompte = $agendaAcompte->isStatut() ;

            if($statutAgdAcompte)
            {
                $markup = "<span class=\"badge bg-info m-1 font-smaller p-1 text-white\"><i class=\"fa fa-layer-group\"></i></span>" ;
            }
            else if(is_null($statutAgdAcompte))
            {
                $markup = "<span class=\"badge bg-danger m-1 font-smaller p-1 text-white\"><i class=\"fa fa-layer-group\"></i></span>" ;
            }
            else
            {
                $markup = "<span class=\"badge bg-dark m-1 font-smaller p-1 text-white\"><i class=\"fa fa-layer-group\"></i></span>" ;
            }
            $element["markup"] = $markup ;
            array_push($elements,$element) ;
        }

        $items = $elements ;

        // Group the markup by date using array_reduce
        $mergedMarkup = array_reduce($items, function ($result, $item) {
            $date = $item['date'];
            $markup = $item['markup'];

            if (isset($result[$date])) {
                $result[$date]['markup'] .= $markup;
            } else {
                $result[$date] = $item;
            }

            return $result;
        }, []);

        $agendaResult = [] ;
        foreach ($mergedMarkup as $mark) {
            $newMarkUp = [] ;
            $newMarkUp['date'] = $mark['date'] ;
            $newMarkUp['markup'] = "<div class=\"d-flex w-100 flex-column align-items-center justify-content-center\">
                <b>[day]</b>
                <div class=\"d-flex w-100 flex-row align-items-center justify-content-center\">
                    ".$mark['markup']."
                </div>
            </div>" ;
            array_push($agendaResult,$newMarkUp) ;
        }   

        file_put_contents($filename,json_encode($agendaResult)) ;

    }

    public function generatePrestBatiment($filename,$agence)
    {
        $elements = $this->entityManager->getRepository(BtpElement::class)->findBy([
            "statut" => True,
            "agence" => $agence
        ]) ;

        $items = [] ;

        foreach ($elements as $element) {
            $item = [] ;
            $item["id"] = $element->getId() ;
            $item["agence"] = $element->getAgence()->getId() ;
            $item["designation"] = $element->getNom() ;
            $item["mesure"] = $element->getMesure()->getNotation() ;
            array_push($items,$item) ;
        }

        file_put_contents($filename,json_encode($items)) ;
    }

    public function generateEnonceePrestBatiment($filename,$agence)
    {
        $enoncees = $this->entityManager->getRepository(BtpEnoncee::class)->findBy([
            "statut" => True,
            "agence" => $agence
        ]) ;

        $items = [] ;

        foreach ($enoncees as $enoncee) {
            $item = [] ;
            $item["id"] = $enoncee->getId() ;
            $item["agence"] = $enoncee->getAgence()->getId() ;
            $item["nom"] = $enoncee->getNom() ;
            array_push($items,$item) ;
        }

        file_put_contents($filename,json_encode($items)) ;
    }

    public function generateLocationBailleur($filename, $agence) 
    {
        $bailleurs = $this->entityManager->getRepository(LctBailleur::class)->findBy([
            "statut" => True,
            "agence" => $agence
            ]) ;

        $items = [] ;

        foreach ($bailleurs as $bailleur) {
            $item = [] ;
            $item["id"] = $bailleur->getId() ;
            $item["agence"] = $bailleur->getAgence()->getId() ;
            $item["nom"] = $bailleur->getNom() ;
            $item["telephone"] = $bailleur->getTelephone() ;
            $item["adresse"] = $bailleur->getAdresse() ;
            array_push($items,$item) ;
        }

        file_put_contents($filename,json_encode($items)) ;
    }

    public function generateLocationLocataire($filename, $agence) 
    {
        $locataires = $this->entityManager->getRepository(LctLocataire::class)->findBy([
            "statut" => True,
            "agence" => $agence
            ]) ;

        $items = [] ;

        foreach ($locataires as $locataire) {
            $item = [] ;
            $item["id"] = $locataire->getId() ;
            $item["agence"] = $locataire->getAgence()->getId() ;
            $item["nom"] = $locataire->getNom() ;
            $item["telephone"] = $locataire->getTelephone() ;
            $item["adresse"] = $locataire->getAdresse() ;
            $item["email"] = $locataire->getEmail() ;
            array_push($items,$item) ;
        }

        file_put_contents($filename,json_encode($items)) ;
    }
    

    public function generateLocationContrat($filename, $agence) 
    {
        $contrats = $this->entityManager->getRepository(LctContrat::class)->findBy([
            "agence" => $agence,
            "statutGen" => True,
            ]) ;

        $items = [] ;

        foreach ($contrats as $contrat) {
            $item = [] ;
            if($contrat->getPeriode()->getReference() == "J")
            {
                $periode = "Jour(s)" ;
            }
            if($contrat->getPeriode()->getReference() == "M")
            {
                $periode = "Mois" ;
            }
            else if($contrat->getPeriode()->getReference() == "A")
            {
                $periode = "An(s)" ;
            }

            $item["id"] = $contrat->getId() ;
            $item["encodedId"] = $this->encodeChiffre($contrat->getId()) ;
            $item["agence"] = $contrat->getAgence()->getId() ;
            $item["numContrat"] = $contrat->getNumContrat() ;
            $item["dateContrat"] = $contrat->getDateContrat()->format("d/m/Y") ;
            $item["bailleur"] = $contrat->getBailleur()->getNom() ;
            $item["bailleurId"] = $contrat->getBailleur()->getId() ;
            $item["bail"] = $contrat->getBail()->getNom() ;
            $item["bailId"] = $contrat->getBail()->getId() ;
            $item["locataire"] = $contrat->getLocataire()->getNom() ;
            $item["locataireId"] = $contrat->getLocataire()->getId() ;
            $item["cycle"] = $contrat->getCycle()->getNom() ;
            $item["dateDebut"] = $contrat->getDateDebut()->format("d/m/Y") ;
            $item["dateFin"] = $contrat->getDateFin()->format("d/m/Y") ;
            $item["frequence"] = is_null($contrat->getFrequenceRenouv()) ? "Aucun" : $contrat->getFrequenceRenouv()-1;
            $item["dureeContrat"] = $contrat->getDuree()." ".$periode ;
            $item["montantContrat"] = $contrat->getMontantContrat() ;
            $item["statut"] = $contrat->getStatut()->getNom() ;
            $item["refStatut"] = $contrat->getStatut()->getReference() ;
            array_push($items,$item) ;
        }

        file_put_contents($filename,json_encode($items)) ;
    }

    public function generateLctCommisionContrat($filename, $agence)
    {
        $contrats = $this->entityManager->getRepository(LctContrat::class)->findBy([
            "agence" => $agence,
            "statutGen" => True,
        ]) ;

        $tabContrats = [] ;

            foreach ($contrats as $contrat) {
                if((is_null($contrat->getPourcentage()) || empty($contrat->getPourcentage())) || $contrat->getStatut()->getReference() != "ENCR")
                    continue ;

                if($contrat->getForfait()->getReference() == "FORFAIT")
                {
                    if($contrat->getPeriode()->getReference() == "J")
                {
                    $periode = " Jour(s)" ;
                }
                if($contrat->getPeriode()->getReference() == "M")
                {
                    $periode = " Mois" ;
                }
                else if($contrat->getPeriode()->getReference() == "A")
                {
                    $periode = " An(s)" ;
                }

                $lastRecordPaiement = $this->entityManager->getRepository(LctPaiement::class)->findOneBy(["contrat" => $contrat], ['id' => 'DESC']);
                $datePaiement = !is_null($lastRecordPaiement) ? ($lastRecordPaiement->getDate()->format("d/m/Y")) : "-" ;
                
                $elemC = [] ;

                $elemC["id"] = $contrat->getId() ;
                $elemC["numContrat"] = $contrat->getNumContrat() ;
                $elemC["bail"] = $contrat->getBail()->getNom()." | ".$contrat->getBail()->getLieux() ;
                $elemC["bailId"] = $contrat->getBail()->getId() ;
                $elemC["locataire"] = $contrat->getLocataire()->getNom() ;
                $elemC["locataireId"] = $contrat->getLocataire()->getId() ;
                $elemC["cycle"] = $contrat->getCycle()->getNom() ;
                $elemC["duree"] = $contrat->getDuree().$periode ;
                $elemC["datePaiement"] = $datePaiement ;
                $elemC["commission"] = ($contrat->getMontantContrat() * $contrat->getPourcentage()) / 100;

                array_push($tabContrats,$elemC) ;

                continue ;
            }

            $repartitions = $this->entityManager->getRepository(LctRepartition::class)->findBy([
                "contrat" => $contrat 
            ]) ;
    
            $childs = [] ;
            
            $totalReleve = 0 ;
    
            foreach ($repartitions as $repartition) {
                $item = [] ;
    
                $statutRepart = $repartition->getStatut()->getReference() ; 
                if($statutRepart == "CAUTION")
                    continue ;
    
                $item["dateDebut"] = is_null($repartition->getDateDebut()) ? "NONE" : $repartition->getDateDebut()->format("d/m/Y") ;
                $item["montant"] = $repartition->getMontant() ;
                $item["statut"] = $repartition->getStatut()->getReference() ;

                $totalReleve += $repartition->getMontant() ; 
                array_push($childs,$item) ;
            }
    
            $resultat = array_reduce($childs, function($carry, $contenu) {
                $dateDebut = $contenu['dateDebut'];
                
                if (!isset($carry[$dateDebut])) {
                    $carry[$dateDebut] = $contenu;
                } else {
                    $carry[$dateDebut]['montant'] += $contenu['montant'];
                }
                
                return $carry;
            }, []);
            
            $childs = array_values($resultat); 
            $newChilds = [] ;
            
            foreach ($childs as $child) {
                $elem = [] ;
                
                $elem["dateDebut"] = $child["dateDebut"] ;
                $elem["montant"] = $child["montant"] ;
                
                if($child["montant"] == $contrat->getMontantForfait())
                {
                    array_push($newChilds,$elem) ;
                }
                
            }

            $commission = 0 ;
            foreach ($newChilds as $newChild) {
                $commission += ($newChild["montant"] * $contrat->getPourcentage()) / 100 ;
            }

            if($contrat->getPeriode()->getReference() == "J")
            {
                $periode = " Jour(s)" ;
            }
            if($contrat->getPeriode()->getReference() == "M")
            {
                $periode = " Mois" ;
            }
            else if($contrat->getPeriode()->getReference() == "A")
            {
                $periode = " An(s)" ;
            }

            $lastRecordPaiement = $this->entityManager->getRepository(LctPaiement::class)->findOneBy(["contrat" => $contrat], ['id' => 'DESC']);
            $datePaiement = !is_null($lastRecordPaiement) ? ($lastRecordPaiement->getDate()->format("d/m/Y")) : "-" ;
            
            $elemC = [] ;

            $elemC["id"] = $contrat->getId() ;
            $elemC["numContrat"] = $contrat->getNumContrat() ;
            $elemC["bail"] = $contrat->getBail()->getNom()." | ".$contrat->getBail()->getLieux() ;
            $elemC["bailId"] = $contrat->getBail()->getId() ;
            $elemC["locataire"] = $contrat->getLocataire()->getNom() ;
            $elemC["locataireId"] = $contrat->getLocataire()->getId() ;
            $elemC["cycle"] = $contrat->getCycle()->getNom() ;
            $elemC["duree"] = $contrat->getDuree().$periode ;
            $elemC["datePaiement"] = $datePaiement ;
            $elemC["commission"] = $commission ;

            array_push($tabContrats,$elemC) ;
        }

        file_put_contents($filename,json_encode($tabContrats)) ;
    }

    public function generateLocationBails($filename, $agence)
    {
        $bailleurs = $this->entityManager->getRepository(LctBailleur::class)->findBy([
            "agence" => $agence,
            "statut" => True,
        ]) ;

        $tabBails = [] ;
            
        foreach ($bailleurs as $bailleur) {
            $bails = $this->entityManager->getRepository(LctBail::class)->findBy([
                "bailleur" => $bailleur,
                "statut" => True,
            ]) ;
            if(is_null($bails))
                continue ;
            foreach($bails as $bail) {
                $myitem = [] ;

                $myitem["id"] = $bail->getId() ; 
                $myitem["nom"] = $bail->getNom() ; 
                $myitem["adresse"] = $bail->getLieux() ; 

                array_push($tabBails,$myitem) ;
            }
        }

        file_put_contents($filename,json_encode($tabBails)) ;
    }

    public function generateCmpBanque($filename, $agence) 
    {
        $banques = $this->entityManager->getRepository(CmpBanque::class)->findBy([
            "agence" => $agence,
            "statut" => True,
            ]) ;

        $items = [] ;

        foreach ($banques as $banque) {
            $item = [] ;

            $item["id"] = $banque->getId() ;
            $item["agence"] = $banque->getAgence()->getId() ;
            $item["nom"] = $banque->getNom() ;
            array_push($items,$item) ;
        }

        file_put_contents($filename,json_encode($items)) ;
    }

    public function generateCmpCompte($filename, $agence) 
    {
        $comptes = $this->entityManager->getRepository(CmpCompte::class)->findBy([
            "agence" => $agence,
            "statut" => True,
            ]) ;

        $items = [] ;

        foreach ($comptes as $compte) {
            $item = [] ;

            $item["id"] = $compte->getId() ;
            $item["agence"] = $compte->getAgence()->getId() ;
            $item["banque"] = $compte->getBanque()->getNom() ;
            $item["numero"] = $compte->getNumero() ;
            $item["solde"] = $compte->getSolde() ;
            array_push($items,$item) ;
        }

        file_put_contents($filename,json_encode($items)) ;
    }

    public function generateCmpOperation($filename, $agence) 
    {
        $operations = $this->entityManager->getRepository(CmpOperation::class)->findBy([
            "agence" => $agence,
            "statut" => True,
        ],["date" => "DESC"]) ;

        $items = [] ;

        foreach ($operations as $operation) {
            $item = [] ;

            $item["id"] = $operation->getId() ;
            $item["agence"] = $operation->getAgence()->getId() ;
            $item["banque"] = $operation->getBanque()->getNom() ;
            $item["idBanque"] = $operation->getBanque()->getId() ;
            $item["compte"] = $operation->getCompte()->getNumero() ;
            $item["dur-compte"] = $operation->getCompte()->getNumero() ;
            $item["categorie"] = $operation->getCategorie()->getNom() ;
            $item["idCategorie"] = $operation->getCategorie()->getId() ;
            $item["refCategorie"] = $operation->getCategorie()->getReference() ;
            $item["type"] = $operation->getType()->getNom() ;
            $item["numeroMode"] = is_null($operation->getNumeroMode()) ? "-" : $operation->getNumeroMode() ;
            $item["editeurMode"] = is_null($operation->getEditeurMode()) ? "-" : $operation->getEditeurMode() ;
            $item["numero"] = $operation->getNumero() ;
            $item["montant"] = $operation->getMontant() ;
            $item["personne"] = $operation->getPersonne() ;
            $item["dur-personne"] = $operation->getPersonne() ;
            $item["date"] = $operation->getDate()->format("d/m/Y") ;
            $item["currentDate"] = $operation->getDate()->format("d/m/Y") ;
            $item["dateDeclaration"] = $operation->getDate()->format("d/m/Y") ;
            $item["dateDebut"] = $operation->getDate()->format("d/m/Y") ;
            $item["dateFin"] = $operation->getDate()->format("d/m/Y") ;
            $item["annee"] = $operation->getDate()->format("Y") ;
            $item["mois"] = $operation->getDate()->format("m") ;
            array_push($items,$item) ;
        }

        file_put_contents($filename,json_encode($items)) ;
    }

    public function generateAchMarchandise($filename, $agence) 
    {
        $marchandises = $this->entityManager->getRepository(AchMarchandise::class)->findBy([
            "agence" => $agence,
            "statutGen" => True,
            ]) ;

        $items = [] ;

        foreach ($marchandises as $marchandise) {
            $item = [] ;

            $item["id"] = $marchandise->getId() ;
            $item["agence"] = $marchandise->getAgence()->getId() ;
            $item["designation"] = $marchandise->getDesignation() ;
            $item["prix"] = $marchandise->getPrix();
            array_push($items,$item) ;
        }

        file_put_contents($filename,json_encode($items)) ;
    }

    public function generateCltClient($filename, $agence)
    {
        $histoClients = $this->entityManager->getRepository(CltHistoClient::class)->findBy([
            "agence" => $agence,
            "statut" => True,    
        ]) ;

        $items = [] ;

        foreach ($histoClients as $histoClient) {
            $item = [] ;

            $item["id"] = $histoClient->getId() ;
            $item["encodedId"] = $this->encodeChiffre($histoClient->getId()) ;
            $item["agence"] = $histoClient->getAgence()->getId() ;
            $item["numClient"] = str_pad($histoClient->getId(), 4, "0", STR_PAD_LEFT) ;
            $item["nom"] = is_null($histoClient->getClient()) ? $histoClient->getSociete()->getNom() : $histoClient->getClient()->getNom();
            $item["type"] = $histoClient->getType()->getNom() ;
            $item["adresse"] = is_null($histoClient->getClient()) ? $histoClient->getSociete()->getAdresse() : $histoClient->getClient()->getAdresse();
            $item["telephone"] = is_null($histoClient->getClient()) ? $histoClient->getSociete()->getTelFixe() : $histoClient->getClient()->getTelephone();
            array_push($items,$item) ;
        }

        file_put_contents($filename,json_encode($items)) ;
    }

    public function generateChkCheque($filename,$agence)
    {
        $cheques = $this->entityManager->getRepository(ChkCheque::class)->findBy([
            "agence" => $agence,
            "statutGen" => True,
        ]) ;

        $items = [] ;

        foreach ($cheques as $cheque) {
            $item = [] ;

            $item["id"] = $cheque->getId() ;
            $item["encodedId"] = $this->encodeChiffre($cheque->getId()) ;
            $item["agence"] = $cheque->getAgence()->getId() ;
            $item["nomChequier"] = $cheque->getNomChequier() ;
            $item["banque"] = $cheque->getBanque()->getNom() ;
            $item["type"] = $cheque->getType()->getNom() ; 
            $item["idType"] = $cheque->getType()->getId() ; 
            $item["dateCheque"] = $cheque->getDateCheque()->format("d/m/Y") ;
            $item["numCheque"] = $cheque->getNumCheque() ;
            $item["date"] = $cheque->getDateDeclaration()->format("d/m/Y") ;
            $item["currentDate"] = $cheque->getDateDeclaration()->format("d/m/Y") ;
            $item["dateDeclaration"] = $cheque->getDateDeclaration()->format("d/m/Y") ;
            $item["dateDebut"] = $cheque->getDateDeclaration()->format("d/m/Y") ;
            $item["dateFin"] = $cheque->getDateDeclaration()->format("d/m/Y") ;
            $item["annee"] = $cheque->getDateDeclaration()->format("Y") ;
            $item["mois"] = $cheque->getDateDeclaration()->format("m") ;
            $item["montant"] = $cheque->getMontant() ;
            $item["statut"] = $cheque->getStatut()->getNom() ; 
            $item["refStatut"] = $cheque->getStatut()->getReference() ; 

            array_push($items,$item) ;
        }

        file_put_contents($filename,json_encode($items)) ;
    }

    public function generateDepListeDepense($filename, $agence) 
    {
        $depenses = $this->entityManager->getRepository(Depense::class)->findBy([
            "agence" => $agence,
            "statutGen" => True,
        ],
        [
            "moisFacture" => "ASC",
            "anneeFacture" => "ASC",
        ]) ;

        $items = [] ;

        foreach ($depenses as $depense) {
            $item = [] ;

            $item["id"] = $depense->getId() ;
            $item["encodedId"] = $this->encodeChiffre($depense->getId()) ;
            $item["agence"] = $depense->getAgence()->getId() ;
            $item["dateDeclaration"] = $depense->getDateDeclaration()->format("d/m/Y") ;
            $item["date"] = str_pad($depense->getMoisFacture(), 2, "0", STR_PAD_LEFT)."/".$depense->getAnneeFacture() ;
            $item["element"] = $depense->getElement() ;
            $item["dur-element"] = $depense->getElement() ;
            $item["beneficiaire"] = $depense->getNomConcerne() ;
            $item["dur-beneficiaire"] = $depense->getNomConcerne() ;
            $item["numFacture"] = $depense->getNumFacture() ;
            $item["moisFacture"] = $depense->getMoisFacture() ;
            $item["anneeFacture"] = $depense->getAnneeFacture() ;
            $item["currentDate"] = $depense->getDateDeclaration()->format("d/m/Y") ;
            $item["dateDebut"] = $depense->getDateDeclaration()->format("d/m/Y") ;
            $item["dateFin"] = $depense->getDateDeclaration()->format("d/m/Y") ;
            $item["moisDepense"] = $depense->getDateDeclaration()->format("m") ;
            $item["anneeDepense"] = $depense->getDateDeclaration()->format("Y") ;
            $item["service"] = $depense->getService()->getNom() ;
            $item["idService"] = $depense->getService()->getId() ;
            $item["motif"] = $depense->getMotif()->getNom() ;
            $item["refMotif"] = $depense->getMotif()->getReference() ;
            $item["modePaiement"] = $depense->getModePaiement()->getNom() ;
            $item["refMode"] = $depense->getModePaiement()->getReference() ;
            $item["montant"] = $depense->getMontantDep();
            $item["statut"] = $depense->getStatut()->getNom();

            array_push($items,$item) ;
        }

        usort($items, [self::class, 'comparaisonDates']);

        file_put_contents($filename,json_encode($items)) ;
    }

    public static function comparaisonCommandeAchat($a, $b) {
        // Comparaison par date
        $dateA = \DateTime::createFromFormat('d/m/Y', $a['date']);
        $dateB = \DateTime::createFromFormat('d/m/Y', $b['date']);
        $result = $dateB <=> $dateA ;

        if ($result !== 0) {
            return $result;
        }
        
        // Comparaison par numero
        return intval($a['numero']) <=> intval($b['numero']) ;
    }

    public function generateAchListBonCommande($filename, $agence) 
    {
        $bonCommandes = $this->entityManager->getRepository(AchBonCommande::class)->findBy([
            "agence" => $agence,
            "statutGen" => True
        ]) ;

        $elements = [] ;

        foreach ($bonCommandes as $bonCommande) {
            $achatDetails = $this->entityManager->getRepository(AchDetails::class)->findBy([
                "statutGen" => True,
                "bonCommande" => $bonCommande
            ]) ;

            $totalPaiement = $this->entityManager->getRepository(AchHistoPaiement::class)->getTotalPaiement($bonCommande->getId()) ; 

            foreach ($achatDetails as $achatDetail) {


                $element = [] ;

                $element["id"] = $bonCommande->getId() ;
                $element["idDetail"] = $achatDetail->getId() ;
                $element["encodedId"] = $this->encodeChiffre($bonCommande->getId()) ;
                $element["agence"] = $bonCommande->getAgence()->getId() ;
                $element["date"] = $bonCommande->getDate()->format('d/m/Y') ;
                $element["lieu"] = $bonCommande->getLieu() ;
                $element["fournisseur"] = $bonCommande->getFournisseur()->getNom() ;
                $element["type"] = $bonCommande->getType()->getNom() ;
                $element["refType"] = $bonCommande->getType()->getReference() ;
                $element["description"] = $bonCommande->getDescription() ;
                $element["numero"] = $bonCommande->getNumero() ;
                $element["designation"] = $achatDetail->getDesignation() ;
                $element["reference"] = is_null($achatDetail->getReference()) ? "" : $achatDetail->getReference() ;
                $element["quantite"] = $achatDetail->getQuantite() ;
                $element["prix"] = $achatDetail->getPrix() ;
                $element["totalLigne"] = $achatDetail->getPrix() * $achatDetail->getQuantite() ;
                $element["totalTtc"] = $bonCommande->getMontant() ;
                $element["totalPaiement"] = is_null($totalPaiement["credit"]) ? 0 : $totalPaiement["credit"] ;
                $element["statut"] = $achatDetail->getStatut()->getNom() ;
                $element["refStatut"] = $achatDetail->getStatut()->getreference() ;
                $element["statutBon"] = $bonCommande->getStatutBon()->getNom() ;
                $element["refStatutBon"] = $bonCommande->getStatutBon()->getreference() ;

                array_push($elements,$element) ;
            }
        } 

        usort($elements, [self::class, 'comparaisonCommandeAchat']);

        file_put_contents($filename,json_encode($elements)) ;
    }

    public function generateAchCommande($filename, $agence)
    {
        $bonCommandes = $this->entityManager->getRepository(AchBonCommande::class)->findBy([
            "agence" => $agence,
            "statutGen" => True
        ]) ;

        $items = [] ;

        foreach ($bonCommandes as $bonCommande) {
            $item = [] ;

            $item["id"] = $bonCommande->getId() ;
            $item["agence"] = $bonCommande->getAgence()->getId() ;
            $item["date"] = $bonCommande->getDate()->format("d/m/Y") ;
            $item["montant"] = $bonCommande->getMontant();
            $item["operation"] = "Achat";
            $item["refOperation"] = "ACHAT" ;
            $item["refJournal"] = "CREDIT" ;

            array_push($items,$item) ;
        }

        file_put_contents($filename,json_encode($items)) ;
    }

    public function updateStatutFinance($finance)
    {
        $totalFacture = $finance->getFacture()->getTotal() ; 

        $totalPayee = $this->entityManager->getRepository(CrdDetails::class)->getFinanceTotalPayee($finance->getId()) ; 

        if($totalFacture == $totalPayee["total"])
        {
            $paiement = $finance->getPaiement()->getReference() ;
            if($paiement == "CR")
                $statut = "SLD" ;
            else
                $statut = "TRM" ;

            $crdStatut = $this->entityManager->getRepository(CrdStatut::class)->findOneBy([
                    "reference" => $statut
                ]) ;

            $finance->setStatut($crdStatut) ;
            $this->entityManager->flush() ;
        }

    }

    public function recherche($item, $search = []) {
        // if (count($search) > 1) {
            $condition = true ;
            foreach ($search as $key => $value) {
                if(!empty($value))
                {
                    if($key == "dateDebut")
                    {
                        $dateString1 = $value;
                        $dateString2 = $item->$key;

                        // Conversion des chaînes de caractères en objets DateTime
                        $dateSearch = \DateTime::createFromFormat('d/m/Y', $dateString1)->setTime(0, 0, 0);
                        $dateValue = \DateTime::createFromFormat('d/m/Y', $dateString2)->setTime(0, 0, 0);

                        $condition = $condition && ($dateSearch <= $dateValue) ;
                    }  
                    else if($key == "dateFin")
                    {
                        $dateString1 = $value;
                        $dateString2 = $item->$key;

                        // Conversion des chaînes de caractères en objets DateTime
                        $dateSearch = \DateTime::createFromFormat('d/m/Y', $dateString1)->setTime(0, 0, 0);
                        $dateValue = \DateTime::createFromFormat('d/m/Y', $dateString2)->setTime(0, 0, 0);

                        $condition = $condition && ($dateSearch >= $dateValue) ;
                    }  
                    else if($key == "dateFacture" || $key == "currentDate")
                    {
                        $dateString1 = $value;
                        $dateString2 = $item->$key;

                        // Conversion des chaînes de caractères en objets DateTime
                        $dateSearch = \DateTime::createFromFormat('d/m/Y', $dateString1)->setTime(0, 0, 0);
                        $dateValue = \DateTime::createFromFormat('d/m/Y', $dateString2)->setTime(0, 0, 0);

                        $condition = $condition && ($dateSearch == $dateValue) ;
                    }
                    else
                    {
                        $spec = explode("-",$key) ;
                        if(strtolower($spec[0]) == "dur")
                        {
                            // Utilisation de la fonction substr pour obtenir une sous-chaîne du début du mot
                            $debutMot = substr(strtolower($item->$key), 0, strlen(strtolower($value)));
                            
                            $condition = $condition && strcasecmp($debutMot, strtolower($value)) === 0 ;
                        }
                        else
                        {
                            if(is_numeric($item->$key))
                            {
                                $condition = $condition && ($item->$key == $search[$key]) ;
                            }
                            else
                            {
                                $condition = $condition && (strpos($item->$key, $search[$key]) !== false) ;
                            }
                        }
                    }
                }
            }
            return $condition;
        // } else {
        //     $key = key($item);
        //     return strpos($item->$key, $search[$key]) !== false;
        // }
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

    // U est l'unité de la partie entière
    // D est l'unité de la partie décimale
    public function NumberToLetter($nombre, $U = null, $D = null) 
    {
        $toLetter = [
            0 => "zéro",
            1 => "un",
            2 => "deux",
            3 => "trois",
            4 => "quatre",
            5 => "cinq",
            6 => "six",
            7 => "sept",
            8 => "huit",
            9 => "neuf",
            10 => "dix",
            11 => "onze",
            12 => "douze",
            13 => "treize",
            14 => "quatorze",
            15 => "quinze",
            16 => "seize",
            17 => "dix-sept",
            18 => "dix-huit",
            19 => "dix-neuf",
            20 => "vingt",
            30 => "trente",
            40 => "quarante",
            50 => "cinquante",
            60 => "soixante",
            70 => "soixante-dix",
            80 => "quatre-vingt",
            90 => "quatre-vingt-dix",
        ];
        
        $numberToLetter='';
        $nombre = strtr((string)$nombre, [" "=>""]);
        $nb = floatval($nombre);

        if( strlen($nombre) > 15 ) return "dépassement de capacité";
        if( !is_numeric($nombre) ) return "Nombre non valide";
        if( ceil($nb) != $nb ){
            $nb = explode('.',$nombre);
            return $this->NumberToLetter($nb[0]) . ($U ? " $U et " : " virgule ") . $this->NumberToLetter($nb[1]) . ($D ? " $D" : "");
        }

        $n = strlen($nombre);
        switch( $n ){
            case 1:
                $numberToLetter = $toLetter[$nb];
                break;
            case 2:
                if(  $nb > 19  ){
                    $quotient = floor($nb / 10);
                    $reste = $nb % 10;
                    if(  $nb < 71 || ($nb > 79 && $nb < 91)  ){
                        if(  $reste == 0  ) $numberToLetter = $toLetter[$quotient * 10];
                        if(  $reste == 1  ) $numberToLetter = $toLetter[$quotient * 10] . "-et-" . $toLetter[$reste];
                        if(  $reste > 1   ) $numberToLetter = $toLetter[$quotient * 10] . "-" . $toLetter[$reste];
                    }else $numberToLetter = $toLetter[($quotient - 1) * 10] . "-" . $toLetter[10 + $reste];
                }else $numberToLetter = $toLetter[$nb];
                break;

            case 3:
                $quotient = floor($nb / 100);
                $reste = $nb % 100;
                if(  $quotient == 1 && $reste == 0   ) $numberToLetter = "cent";
                if(  $quotient == 1 && $reste != 0   ) $numberToLetter = "cent" . " " . $this->NumberToLetter($reste);
                if(  $quotient > 1 && $reste == 0    ) $numberToLetter = $toLetter[$quotient] . " cents";
                if(  $quotient > 1 && $reste != 0    ) $numberToLetter = $toLetter[$quotient] . " cent " . $this->NumberToLetter($reste);
                break;
            case 4 :
            case 5 :
            case 6 :
                $quotient = floor($nb / 1000);
                $reste = $nb - $quotient * 1000;
                if(  $quotient == 1 && $reste == 0   ) $numberToLetter = "mille";
                if(  $quotient == 1 && $reste != 0   ) $numberToLetter = "mille" . " " . $this->NumberToLetter($reste);
                if(  $quotient > 1 && $reste == 0    ) $numberToLetter = $this->NumberToLetter($quotient) . " mille";
                if(  $quotient > 1 && $reste != 0    ) $numberToLetter = $this->NumberToLetter($quotient) . " mille " . $this->NumberToLetter($reste);
                break;
            case 7:
            case 8:
            case 9:
                $quotient = floor($nb / 1000000);
                $reste = $nb % 1000000;
                if(  $quotient == 1 && $reste == 0  ) $numberToLetter = "un million";
                if(  $quotient == 1 && $reste != 0  ) $numberToLetter = "un million" . " " . $this->NumberToLetter($reste);
                if(  $quotient > 1 && $reste == 0   ) $numberToLetter = $this->NumberToLetter($quotient) . " millions";
                if(  $quotient > 1 && $reste != 0   ) $numberToLetter = $this->NumberToLetter($quotient) . " millions " . $this->NumberToLetter($reste);
                break;
            case 10:
            case 11:
            case 12:
                $quotient = floor($nb / 1000000000);
                $reste = $nb - $quotient * 1000000000;
                if(  $quotient == 1 && $reste == 0  ) $numberToLetter = "un milliard";
                if(  $quotient == 1 && $reste != 0  ) $numberToLetter = "un milliard" . " " . $this->NumberToLetter($reste);
                if(  $quotient > 1 && $reste == 0   ) $numberToLetter = $this->NumberToLetter($quotient) . " milliards";
                if(  $quotient > 1 && $reste != 0   ) $numberToLetter = $this->NumberToLetter($quotient) . " milliards " . $this->NumberToLetter($reste);
                break;
            case 13:
            case 14:
            case 15:
                $quotient = floor($nb / 1000000000000);
                $reste = $nb - $quotient * 1000000000000;
                if(  $quotient == 1 && $reste == 0  ) $numberToLetter = "un billion";
                if(  $quotient == 1 && $reste != 0  ) $numberToLetter = "un billion" . " " . $this->NumberToLetter($reste);
                if(  $quotient > 1 && $reste == 0   ) $numberToLetter = $this->NumberToLetter($quotient) . " billions";
                if(  $quotient > 1 && $reste != 0   ) $numberToLetter = $this->NumberToLetter($quotient) . " billions " . $this->NumberToLetter($reste);
                break;
        }
        /*respect de l'accord de quatre-vingt*/
        if( substr($numberToLetter, strlen($numberToLetter)-12, 12 ) == "quatre-vingt" ) $numberToLetter .= "s";

        return $numberToLetter;
    }

    public function homeRefreshAllFiles($key)
    {
        $racine = "files/systeme/" ;

        $files = [
            "caisse" => [
                "commande(agence)",
                "panierCommande(agence)"
            ],
            "commande" => [
                "bonCommande(agence)",
                "commande(agence)"
            ],
            "facture" => [
                "facture(agence)",
                "factureParent"
            ],
            "livraison" => [
                "bonLivraison(agence)"
            ],
            "parametres" => [
                "general(agence)",
                "produitTypeTva(agence)"
            ],
            "sav" => [
                "annulation(agence)",
                "motif(agence)"
            ],
            "stock" => [
                "approvisionnement(agence)",
                "categorie(agence)",
                "entrepot(agence)",
                "fournisseur(agence)",
                "produit(agence)",
                "stock_entrepot(agence)",
                "stock_general(agence)"
            ],
            "credit" => [
                "credit(agence)",
                "acompte(agence)"
            ],
            "agenda" => [
                "agenda(agence)"
            ],
            "prestations" => [
                "service(agence)"
            ]
        ];

        if($key != "all")
        {
            foreach ($files[$key] as $file) {
                $filename = $racine.$key."/".$file."/".$this->nameAgence ;
                if(file_exists($filename))
                    unlink($filename) ;
            }
        }
        else
        {
            foreach($files as $indice => $value)
            {
                foreach ($files[$indice] as $file) {
                    $filename = $racine.$indice."/".$file."/".$this->nameAgence ;
                    if(file_exists($filename))
                        unlink($filename) ;
                }
            }
        }

        
    }

    public function getFactureClient($facture)
    {
        $result = [] ;
        if($facture->getClient()->getType()->getId() == 2)
            $result["client"] = $facture->getClient()->getClient()->getNom() ;
        else
            $result["client"] = $facture->getClient()->getSociete()->getNom() ;

        return $result ; 
    }

    public function getFactureRemise($facture,$totalHt)
    {
        if(!is_null($facture->getRemiseType()))
        {
            $remiseVal = is_null($facture->getRemiseVal()) ? 0 : floatval($facture->getRemiseVal()) ; 
            if($facture->getRemiseType()->getCalcul() == 100)
            {
                $remiseG = ($totalHt * $remiseVal) / 100 ; 
            }
            else
            {
                $remiseG = $remiseVal ;
            }
        }
        else
        {
            $remiseG = 0 ;
        }
        return $remiseG ;
    }

    public function getCaisseRemise($caisse,$totalHt)
    {
        if(!is_null($caisse->getRemiseType()))
        {
            $remiseVal = is_null($caisse->getRemiseValeur()) ? 0 : floatval($caisse->getRemiseValeur()) ; 
            if($caisse->getRemiseType()->getCalcul() == 100)
            {
                $remiseG = ($totalHt * $remiseVal) / 100 ; 
            }
            else
            {
                $remiseG = $remiseVal ;
            }
        }
        else
        {
            $remiseG = 0 ;
        }
        return $remiseG ;
    }

    public function formatAnnulationToFacture($annulations)
    {
        $elements = [] ;

        foreach ($annulations as $annulation) {
            $element = [] ;
 
            $facture = $this->entityManager->getRepository(Facture::class)->find($annulation->idF) ; 
            
            if(is_null($facture))
                continue;

            $element["id"] = $annulation->id ;
            $element["idC"] = $facture->getClient()->getId() ;
            $element["idT"] = $facture->getType()->getId() ;
            $element["idM"] = $facture->getModele()->getId()  ;
            $element["mois"] = $facture->getDate()->format('m')   ;
            $element["annee"] = $facture->getDate()->format('Y')   ;
            $element["date"] = $facture->getDate()->format('d/m/Y')  ;
            $element["currentDate"] = $facture->getDate()->format('d/m/Y')  ;
            $element["dateDebut"] = $facture->getDate()->format('d/m/Y')   ;
            $element["dateFin"] = $facture->getDate()->format('d/m/Y')   ;
            $element["agence"] = $facture->getAgence()->getId() ;
            $element["user"] = $facture->getUser()->getId() ;
            $element["numFact"] = $annulation->numero;
            $element["modele"] = $facture->getModele()->getNom() ;
            $element["type"] = $facture->getType()->getNom() ;
            $element["dateCreation"] = $annulation->date  ;
            $element["dateFacture"] = $facture->getDate()->format('d/m/Y')  ;
            $element["client"] = $this->getFactureClient($facture)["client"] ;
            $element["total"] = $annulation->total;
            $element["specification"] = $annulation->refSpec;;
            $element["nature"] = "ANL" ; 

            array_push($elements,$element) ;
        }

        return $elements ;
    }

    public function compareDates($date1, $date2, $condition) {
        $date1Obj = \DateTime::createFromFormat('d/m/Y', $date1) ;
        $date2Obj = \DateTime::createFromFormat('d/m/Y', $date2) ;
        
        switch ($condition) {
            case 'G':  // cas où la date1 est supérieure à la date2
                return $date1Obj > $date2Obj;
            case 'P': // cas où la date1 est inférieure à la date2
                return $date1Obj < $date2Obj;
            case 'E': // cas où la date1 est égale à la date2
                return $date1Obj == $date2Obj;
            default:
                return false;
        }
    }

    public function checkAllDateAgenda()
    {
        $agendas = $this->entityManager->getRepository(Agenda::class)->findBy([
            "agence" => $this->agence
        ]) ;
        
        if(!is_null($agendas))
            $this->validCompareDate($agendas) ;

        $echeances = $this->entityManager->getRepository(AgdEcheance::class)->findBy([
            "agence" => $this->agence
        ]) ;

        if(!is_null($echeances))
            $this->validCompareDate($echeances) ;

        $agendaAcomptes = $this->entityManager->getRepository(AgdAcompte::class)->findOneBy([
            "agence" => $this->agence
        ]) ;
        
        if(!is_null($agendaAcomptes))
            $this->validCompareDate($agendaAcomptes) ;
    }

    public function validCompareDate($object)
    {
        $dateActuel = date('d/m/Y') ;
        foreach ($object as $object) {
            $dateAgdAcompte = $object->getDate()->format('d/m/Y') ; 
        
            $compareInf = $this->compareDates($dateAgdAcompte,$dateActuel,"P") ;
        
            if($compareInf)
            {
                if($object->isStatut())
                {
                    $object->setStatut(NULL) ;
                    $this->entityManager->flush() ;
                }
            }
        }
    }

    public function convertirFormatDate($dateString) {
        // Séparer la date en jour, mois et année
        $dateParts = explode('/', $dateString);
        $jour = $dateParts[0];
        $mois = $dateParts[1];
        $annee = $dateParts[2];
    
        // Créer un nouvel objet DateTime avec le format aaaa-mm-jj
        $date = new \DateTime("$annee-$mois-$jour");
    
        // Obtenir les composants de la date au format 'aaaa-mm-jj'
        $anneeConvertie = $date->format('Y');
        $moisConverti = $date->format('m');
        $jourConverti = $date->format('d');
    
        // Retourner la date convertie au format 'aaaa-mm-jj'
        return $anneeConvertie . '-' . $moisConverti . '-' . $jourConverti;
    }

    public function calculerDateAvantNjours($dateInitiale, $nbJours) {
        // Convertir la date initiale en objet DateTime
        $date = new \DateTime($this->convertirFormatDate($dateInitiale));
        
        // Calculer la date N jours avant la date initiale
        $dateAvantNJours = $date->modify("-$nbJours days");
        
        // Conversion de la date en format souhaité (jj/mm/aaaa)
        $jour = $dateAvantNJours->format('d');
        $mois = $dateAvantNJours->format('m');
        $annee = $dateAvantNJours->format('Y');
        
        // Formattage de la date
        $dateFormatee = str_pad($jour, 2, "0", STR_PAD_LEFT) . '/' . str_pad($mois, 2, "0", STR_PAD_LEFT) . '/' . $annee;
        
        return $dateFormatee;
    }

    public function calculerDateApresNjours($dateInitiale, $nbJours) {
        // Convertir la date initiale en objet DateTime
        $date = new \DateTime($this->convertirFormatDate($dateInitiale));
        
        // Calculer la date après le nombre de jours spécifié        
        $dateApresNJours = $date->modify("+$nbJours days");

        // Conversion de la date en format souhaité (jj/mm/aaaa)
        $jour = $dateApresNJours->format('d');
        $mois = $dateApresNJours->format('m');
        $annee = $dateApresNJours->format('Y');
        
        // Formattage de la date
        $dateFormatee = str_pad($jour, 2, "0", STR_PAD_LEFT) . '/' . str_pad($mois, 2, "0", STR_PAD_LEFT) . '/' . $annee;
        
        // return $dateApresNJours;
        return $dateFormatee;
    }

    public function genererTableauMois($dateInitiale, $nombreMois, $dateLimite,$moisExist) {
        $tableauDates = array();
        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        $date = \DateTime::createFromFormat('d/m/Y', $dateInitiale);
        $mois = null;
        for ($i = 0; $i < $nombreMois; $i++) {
            // Calculer la date après le nombre de jours spécifié
            $dateApresNJours = $this->calculerDateApresNjours($dateInitiale, 30 * ($i + 1));
     
            $annee = intval(explode("/",$dateApresNJours)[2]);
            if($mois == null)
            {
                $mois = intval(explode("/",$dateApresNJours)[1]); 
                if(!is_null($moisExist))
                {
                    if( $mois >= 12 )
                    {
                        $mois = 1 ;
                    }
                    else
                    {
                        $mois = $moisExist ;
                    }
                }
                    
            }
            else if( $mois >= 12 + 1 )
            {
                $mois = 1 ;
                // return $tableauDates;
            }
            
            // Ajouter la date au tableau
            $finLimite = $this->calculerDateApresNjours($dateApresNJours,$dateLimite) ;
            $resultCompare = $this->compareDates($finLimite,date("d/m/Y"),"P") || $this->compareDates($finLimite,date("d/m/Y"),"E") ;

            $statut = $resultCompare ? "En Alerte" : "-" ;

            // if(!$resultCompare)
                // return $tableauDates ;

            $tableauDates[] = [
                "debutLimite" => $dateApresNJours,
                "finLimite" => $finLimite, 
                "mois" => $tabMois[$mois - 1],
                "indexMois" => $mois,
                "annee" => $annee,
                "statut" =>'<span class="text-danger font-weight-bold">'.strtoupper($statut).'</span>',
            ] ;
            
            $mois++ ;
        }
        
        return $tableauDates;
    }

    public function genererTableauJour($dateInitiale, $nombreJour) {
        $tableauDates = array();
        $tabMois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        $date = \DateTime::createFromFormat('d/m/Y', $dateInitiale);
        for ($i = 0; $i < $nombreJour; $i++) {
            // Calculer la date après le nombre de jours spécifié
            $dateApresNJours = $this->calculerDateApresNjours($dateInitiale,$i + 1);
            // Extraire le mois de la date
            // $date->modify("+1 days");
            $annee = intval(explode("/",$dateApresNJours)[2]);
            $mois = intval(explode("/",$dateApresNJours)[1]); 
            // Ajouter la date au tableau
            // $finLimite = $this->calculerDateApresNjours($dateApresNJours,$dateLimite) ;
            
            $resultCompare = $this->compareDates($dateApresNJours,date("d/m/Y"),"P") || $this->compareDates($dateApresNJours,date("d/m/Y"),"E") ;

            $statut = $resultCompare ? "En Alerte" : "-" ;

            // if(!$resultCompare)
                // return $tableauDates ;

            $tableauDates[] = [
                "debutLimite" => $dateApresNJours,
                // "finLimite" => $finLimite,
                "mois" => $tabMois[$mois - 1],
                "indexMois" => $mois,
                "annee" => $annee,
                "statut" =>'<span class="text-danger font-weight-bold">'.strtoupper($statut).'</span>',
            ];
        }
        
        return $tableauDates;
    }

    public function synchronisationGeneral()
    {
        /* DEBUT MISE A JOUR DEDUCTION */

        // $enrDeductions = $this->entityManager->getRepository(PrdDeduction::class)->findAll() ;

        // foreach($enrDeductions as $enrDeduction)
        // {
        //     $variationPrix = $enrDeduction->getHistoEntrepot()->getVariationPrix() ; 
        //     $agenceDeduc = $enrDeduction->getHistoEntrepot()->getAgence() ; 

        //     $enrDeduction->setAgence($agenceDeduc) ;
        //     $enrDeduction->setVariationPrix($variationPrix) ;
        //     $this->entityManager->flush() ;
        // }

        /* FIN MISE A JOUR DEDUCTION */

        $produitFalses = $this->entityManager->getRepository(Produit::class)->findBy([
            "agence" => $this->agence,
            "statut" => False
        ]) ; 

        foreach($produitFalses as $produitFalse)
        {
            $variationPrixTrues = $this->entityManager->getRepository(PrdVariationPrix::class)->findBy([
                "produit" => $produitFalse,
            ]) ; 

            foreach ($variationPrixTrues as $variationPrixTrue) {
                $variationPrixTrue->setStatut(False) ;
                $this->entityManager->flush() ;

                $histoEntrepotTrues = $this->entityManager->getRepository(PrdHistoEntrepot::class)->findBy([
                    "variationPrix" => $variationPrixTrue,
                ]) ; 

                foreach ($histoEntrepotTrues as $histoEntrepotTrue) {
                    $histoEntrepotTrue->setStatut(False) ;
                    $this->entityManager->flush() ;
                }
            }
        }

        $produits = $this->entityManager->getRepository(Produit::class)->findBy([
            "agence" => $this->agence,
            "toUpdate" => True,
            "statut" => True
        ]) ;

        foreach($produits as $produit)
        {
            $variationPrixs = $this->entityManager->getRepository(PrdVariationPrix::class)->findBy([
                "produit" => $produit,
                "statut" => True
            ]) ; 

            $stockAddProduit = 0 ;
            foreach ($variationPrixs as $variationPrix) {
                $histoEntrepots = $this->entityManager->getRepository(PrdHistoEntrepot::class)->findBy([
                    "variationPrix" => $variationPrix,
                    "statut" => True
                ],["stock" => "DESC"]) ; 

                $stockAddVariation = 0 ;
                foreach ($histoEntrepots as $histoEntrepot) {
                    // $approvisionnements = $this->entityManager->getRepository(PrdApprovisionnement::class)->find([
                    //     "variationPrix" => $variationPrix
                    // ]) ;
                    $stockTotalEntrepot = $this->entityManager->getRepository(PrdApprovisionnement::class)->stockTotalVariationPrix([
                        "variationPrix" => $variationPrix->getId(),
                        "histoEntrepot" => $histoEntrepot->getId(),
                    ]) ;

                    $stockAddVariation += $stockTotalEntrepot["stockTotalEntrepot"] ;

                    $histoEntrepot->setStock($stockTotalEntrepot["stockTotalEntrepot"]) ;
                    $this->entityManager->flush() ;
                }
                
                $variationPrix->setStock($stockAddVariation) ;
                $this->entityManager->flush() ;

                $stockAddProduit += $stockAddVariation ;
            }

            $produit->setStock($stockAddProduit) ;
            $produit->setToUpdate(null) ;
            $this->entityManager->flush() ;
        }

        if(empty($produits))
            return ;

        $typeFacture = $this->entityManager->getRepository(FactType::class)->findBy([
            "reference" => "DF"
        ]) ; 

        $factureDefinitives = $this->entityManager->getRepository(Facture::class)->findBy([
            "type" => $typeFacture,
            "agence" => $this->agence,
            "ticketCaisse" => null,
            "statut" => True
        ]) ; 

        $histoEntrepotActifs = $this->entityManager->getRepository(PrdHistoEntrepot::class)->findBy([
            "statut" => True,
            "agence" => $this->agence
        ]) ; 

        foreach($histoEntrepotActifs as $histoEntrepotActif)
        {
            $deductionEntrepot = $this->entityManager->getRepository(PrdDeduction::class)->getSommeDeductionEntrepot([
                "histoEntrepot" => $histoEntrepotActif->getId()
            ]) ;

            $totalHistoEntrepot = floatval($histoEntrepotActif->getStock()) ;

            if($deductionEntrepot["sommeStock"] > $totalHistoEntrepot)
            {
                $histoEntrepotActif->setStock(0) ;
                $this->entityManager->flush() ;
            }
            else
            {
                $histoEntrepotActif->setStock(floatval($histoEntrepotActif->getStock()) - floatval($deductionEntrepot["sommeStock"])) ;
                $this->entityManager->flush() ;
            }

            $produitEntrepotActif = $histoEntrepotActif->getVariationPrix()->getProduit() ;

            $histoEntrepotActif->getVariationPrix()->setStock($histoEntrepotActif->getVariationPrix()->getStock() - $deductionEntrepot["sommeStock"]) ;
            $produitEntrepotActif->setStock($produitEntrepotActif->getStock() - $deductionEntrepot["sommeStock"]) ;
            $this->entityManager->flush() ;
        }

        foreach($produits as $produit)
        {
            $variationPrixs = $this->entityManager->getRepository(PrdVariationPrix::class)->findBy([
                "produit" => $produit,
                "statut" => True
            ]) ; 
            $stockRemoveVariation = 0 ;
            foreach ($variationPrixs as $variationPrix) {
                foreach ($factureDefinitives as $factureDefinitive) {
                    $detailFactureVariation = $this->entityManager->getRepository(FactDetails::class)->stockTotalFactureVariation([
                        "facture" => $factureDefinitive->getId(),
                        "variationPrix" => $variationPrix->getId(),
                        "statut" => True
                    ]) ;
                    $stockRemoveVariation += $detailFactureVariation["totalFactureVariation"] ;
                }

                $caisseVariation = $this->entityManager->getRepository(CaissePanier::class)->stockTotalCaisseVariationPrix([
                    "variationPrix" => $variationPrix->getId(),
                    "statut" => True
                ]) ; 

                $stockRemoveVariation += $caisseVariation["totalCaisseVariation"] ;

                // REPARTITION de la diminution
    
                $histoEntrepots = $this->entityManager->getRepository(PrdHistoEntrepot::class)->findBy([
                    "variationPrix" => $variationPrix,
                    "statut" => True
                ],["stock" => "DESC"]) ; 
    
                $repartitionDeduction = $stockRemoveVariation ;

                foreach ($histoEntrepots as $histoEntrepot) {
                    $totalStockEntrepot = $histoEntrepot->getStock() ;
                    if($repartitionDeduction > $totalStockEntrepot)
                    {
                        $histoEntrepot->setStock(0) ;
                        $this->entityManager->flush() ;
    
                        $repartitionDeduction -= $histoEntrepot->getStock() ;
                    }
                    else
                    {
                        $histoEntrepot->setStock($histoEntrepot->getStock() - $repartitionDeduction) ;
                        $this->entityManager->flush() ;
                    }
                }
    
                $variationPrix->setStock($variationPrix->getStock() - $stockRemoveVariation) ;
                $this->entityManager->flush() ;
            }

            $produit->setStock($produit->getStock() - $stockRemoveVariation) ;
            $this->entityManager->flush() ;

            $filename = "files/systeme/stock/variationProduit(agence)/vartPrd_".$produit->getId()."_".$this->nameAgence  ;
            if(file_exists($filename))
                unlink($filename) ;
        }


        $produitActives = $this->entityManager->getRepository(Produit::class)->findBy([
            "agence" => $this->agence,
            "statut" => True
        ]) ;

        foreach($produitActives as $produitActive)
        {
            $variationPrixActives = $this->entityManager->getRepository(PrdVariationPrix::class)->findBy([
                "produit" => $produit,
                "statut" => True
            ]) ; 

            foreach ($variationPrixActives as $variationPrixActive)
            {
                $histoEntrepotActives = $this->entityManager->getRepository(PrdHistoEntrepot::class)->findBy([
                    "variationPrix" => $variationPrix,
                    "statut" => True
                ]) ; 

                foreach($histoEntrepotActives as $histoEntrepotActive)
                {
                    if($histoEntrepotActive->getStock() <= 0)
                    {
                        $histoEntrepotActive->setStock(0) ;
                        $this->entityManager->flush() ;
                    }
                }

                if($variationPrixActive->getStock() <= 0)
                {
                    $variationPrixActive->setStock(0) ;
                    $this->entityManager->flush() ;
                }
            }

            if($produitActive->getStock() <= 0)
            {
                $produitActive->setStock(0) ;
                $this->entityManager->flush() ;
            }
        }

        $dataFilenames = [
            "files/systeme/stock/stock_general(agence)/".$this->nameAgence,
            "files/systeme/stock/stock_entrepot(agence)/".$this->nameAgence,
            "files/systeme/stock/type(agence)/".$this->nameAgence,
            "files/systeme/stock/stockType(agence)/".$this->nameAgence ,
            "files/systeme/stock/stockGEntrepot(agence)/".$this->nameAgence ,
            "files/systeme/stock/stockParCategorie(agence)/".$this->nameAgence
        ] ;

        foreach ($dataFilenames as $dataFilename) {
            if(file_exists($dataFilename))
                unlink($dataFilename) ;
        }
    }

    public function synchronisationServiceApresVente($params = [])
    {
        
        foreach($params as $param)
        {
            // SYNCRO AVEC ANNULATION CAISSE
            if($param == "CAISSE")
            {
                $commandes = $this->entityManager->getRepository(CaisseCommande::class)->findBy([
                    "agence" => $this->agence,
                    "statut" => True,
                    "synchro" => null
                ]) ;

                foreach($commandes as $commande)
                {
                    $annulationCaisse = $this->entityManager->getRepository(SavAnnulation::class)->findOneBy([
                        "caisse" => $commande
                    ]) ;

                    if(is_null($annulationCaisse))
                        continue;

                    $panierCommandes = $this->entityManager->getRepository(CaissePanier::class)->findBy([
                        "commande" => $commande,
                        "statut" => True
                    ]) ;

                    if(empty($panierCommandes))
                    {
                        $commande->setMontantPayee(0) ;
                        $commande->setStatut(False) ;
                        $this->entityManager->flush() ;
                    }
                    else
                    {
                        $totalMontantCaisse = 0 ;
                        foreach($panierCommandes as $panierCommande)
                        {
                            $totalMontantCaisse += ($panierCommande->getPrix() * $panierCommande->getQuantite()) ;
                        }
                        
                        $commande->setMontantPayee($totalMontantCaisse) ;
                        $commande->setStatut(True) ;
                        $this->entityManager->flush() ;
                    }

                    $commande->setSynchro(True) ;
                    $this->entityManager->flush() ;
                }
            }
            else if($param == "FACTURE") // SYNCRO AVEC ANNULATION FACTURE
            {
                $factures = $this->entityManager->getRepository(Facture::class)->findBy([
                    "agence" => $this->agence,
                    "statut" => True,
                    "synchro" => null
                ]) ;

                foreach($factures as $facture)
                {
                    $annulationFacture = $this->entityManager->getRepository(SavAnnulation::class)->findOneBy([
                        "facture" => $facture
                    ]) ;

                    if(is_null($annulationFacture))
                        continue;

                    $factureDetails = $this->entityManager->getRepository(FactDetails::class)->findBy([
                        "facture" => $facture,
                        "statut" => True
                    ]) ;

                    // if($facture->getNumFact() == "DF-002/23")
                    //     dd($factureDetails) ;

                    if(empty($factureDetails))
                    {
                        $facture->setTvaVal(0) ;
                        $facture->setTotal(0) ;
                        $facture->setStatut(False) ;
                        $this->entityManager->flush() ;
                    }
                    else
                    {
                        $totalHTFacture = 0 ;
                        $totalTvaFacture = 0 ;
                        
                        foreach($factureDetails as $factureDetail)
                        {
                            $remise = 0 ;
                            $quantite = $factureDetail->getQuantite() ;
                            $prixUnitaire = $factureDetail->getPrix() ;
                            $totalLigne = ($quantite * $prixUnitaire) ;
                            $totalHTLigne = ($quantite * $prixUnitaire) ;
                            $totalTvaLigne = is_null($factureDetail->getTvaVal()) ? 0 : $factureDetail->getTvaVal() ;
    
                            if(!is_null($factureDetail->getRemiseType()))
                            {
                                if($factureDetail->getRemiseType()->getCalcul() == 1)
                                {
                                    $remise = $factureDetail->getRemiseVal() ;
                                }
                                else if($factureDetail->getRemiseType()->getCalcul() == 100)
                                {
                                    $remise = ($totalLigne * $factureDetail->getRemiseVal()) / 100  ;
                                }
                            }
    
                            $totalLigne -= $remise ;
    
                            $totalHTFacture += $totalLigne ;
                            $totalTvaFacture += ($totalHTLigne * $totalTvaLigne) / 100 ;
    
    
                        }
                        
                        $remiseFacture = 0 ;
                        if(!is_null($facture->getRemiseType()))
                        {
                            if($facture->getRemiseType()->getCalcul() == 1)
                            {
                                $remiseFacture = $facture->getRemiseVal() ;
                            }
                            else if($facture->getRemiseType()->getCalcul() == 100)
                            {
                                $remiseFacture = ($totalLigne * $facture->getRemiseVal()) / 100  ;
                            }
                        }
    
                        $totalHTFacture -= $remiseFacture ;
                        $facture->setTvaVal($totalTvaFacture) ;
                        $facture->setTotal($totalHTFacture) ;
                        $facture->setStatut(True) ;
                        $this->entityManager->flush() ;
                    }

                    $facture->setSynchro(True) ;
                    $this->entityManager->flush() ;
                }
            }
        }

        $dataFilenames = [
            "files/systeme/caisse/commande(agence)/".$this->nameAgence ,
            "files/systeme/caisse/panierCommande(agence)/".$this->nameAgence,
            "files/systeme/facture/facture(agence)/".$this->nameAgence,
            "files/systeme/sav/annulation(agence)/".$this->nameAgence,
        ] ;

        foreach ($dataFilenames as $dataFilename) {
            if(file_exists($dataFilename))
                unlink($dataFilename) ;
        }

    }

    public function synchronisationAchatBonDeCommande()
    {
        $statutBonEnCours = $this->entityManager->getRepository(AchStatutBon::class)->findOneBy([
            "reference" => "ENCR",
        ]) ;

        $statutBonPayee = $this->entityManager->getRepository(AchStatutBon::class)->findOneBy([
            "reference" => "PAYE",
        ]) ;

        $statutBonLivree = $this->entityManager->getRepository(AchStatutBon::class)->findOneBy([
            "reference" => "LIVRE",
        ]) ;

        $typeCredit = $this->entityManager->getRepository(AchType::class)->findOneBy([
            "reference" => "CREDIT",
        ]) ;

        $bonCommandeEnCours = $this->entityManager->getRepository(AchBonCommande::class)->findBy([
            "agence" => $this->agence,
            "statutBon" => $statutBonEnCours,
            "type" => $typeCredit,
            "statutGen" => True,
        ]) ;

        foreach($bonCommandeEnCours as $bonCommandeEnCour)
        {
            $totalCreditPayee = $this->entityManager->getRepository(AchHistoPaiement::class)->getTotalPaiement($bonCommandeEnCour->getId()) ;
            
            if($totalCreditPayee["credit"] >= $bonCommandeEnCour->getMontant())
            {
                $bonCommandeEnCour->setStatutBon($statutBonPayee) ;
                $this->entityManager->flush() ;
            }
        }

        $statutNonLivree = $this->entityManager->getRepository(AchStatut::class)->findOneBy([
            "reference" => "NOTLVR"
        ]) ;

        $bonCommandePayees = $this->entityManager->getRepository(AchBonCommande::class)->findBy([
            "agence" => $this->agence,
            "statutBon" => $statutBonPayee,
            // "type" => $typeCredit,
            "statutGen" => True,
        ]) ;

        foreach($bonCommandePayees as $bonCommandePayee)
        {
            $detailAchats = $this->entityManager->getRepository(AchDetails::class)->findBy([
                "bonCommande" => $bonCommandePayee,
                "statut" => $statutNonLivree,
                "statutGen" => True
            ]) ;

            if(empty($detailAchats))
            {
                $bonCommandePayee->setStatutBon($statutBonLivree) ;
                $this->entityManager->flush() ;
            }
        }

        $statutLivree = $this->entityManager->getRepository(AchStatut::class)->findOneBy([
            "reference" => "LVR"
        ]) ;

        $bonCommandeLivrees = $this->entityManager->getRepository(AchBonCommande::class)->findBy([
            "agence" => $this->agence,
            "statutBon" => $statutBonLivree,
            // "type" => $typeCredit,
            "statutGen" => True,
        ]) ;

        foreach($bonCommandeLivrees as $bonCommandeLivree)
        {
            $detailAchatNonLivres = $this->entityManager->getRepository(AchDetails::class)->findBy([
                "bonCommande" => $bonCommandeLivree,
                "statut" => $statutNonLivree,
                "statutGen" => True
            ]) ;

            foreach($detailAchatNonLivres as $detailAchatNonLivre)
            {
                $detailAchatNonLivre->setStatut($statutLivree) ;
                $this->entityManager->flush() ;
            }
        }

        $dataFilenames = [
            "files/systeme/achat/listBonCommande(agence)/".$this->nameAgence,
        ] ;

        foreach ($dataFilenames as $dataFilename) {
            if(file_exists($dataFilename))
                unlink($dataFilename) ;
        }


    }

    public function updateAnneeData()
    {
        $factures = $this->entityManager->getRepository(Facture::class)->findBy([
            "agence" => $this->agence,
            "anneeData" => NULL,
        ]) ; 

        foreach ($factures as $facture) {
            $facture->setAnneeData($facture->getDate()->format('Y')) ;
            $this->entityManager->flush() ;
        }

        $annulations = $this->entityManager->getRepository(SavAnnulation::class)->findBy([
            "agence" => $this->agence,
            "anneeData" => NULL,
        ]) ; 

        foreach ($annulations as $annulation) {
            $annulation->setAnneeData($annulation->getDate()->format('Y')) ;
            $this->entityManager->flush() ;
        }

        $produits = $this->entityManager->getRepository(Produit::class)->findBy([
            "agence" => $this->agence,
            "anneeData" => NULL,
        ]) ; 

        foreach ($produits as $produit) {
            $produit->setAnneeData($produit->getCreatedAt()->format('Y')) ;
            $this->entityManager->flush() ;
        }

        $histoEntrepots = $this->entityManager->getRepository(PrdHistoEntrepot::class)->findBy([
            "agence" => $this->agence,
            "anneeData" => NULL,
        ]) ; 

        foreach ($histoEntrepots as $histoEntrepot) {
            $histoEntrepot->setAnneeData($histoEntrepot->getCreatedAt()->format('Y')) ;
            $this->entityManager->flush() ;
        }

        $caissePaniers = $this->entityManager->getRepository(CaissePanier::class)->findBy([
            "agence" => $this->agence,
            "anneeData" => NULL,
        ]) ; 

        foreach ($caissePaniers as $caissePanier) {
            $caissePanier->setAnneeData($caissePanier->getCommande()->getDate()->format('Y')) ;
            $this->entityManager->flush() ;
        }

        $finances = $this->entityManager->getRepository(CrdFinance::class)->findBy([
            "agence" => $this->agence,
            "anneeData" => NULL,
        ]) ; 

        foreach ($finances as $finance) {
            $finance->setAnneeData($finance->getCreatedAt()->format('Y')) ;
            $this->entityManager->flush() ;
        }
    }

    public function synchronisationFacture($agence)
    {
        $factures = $this->entityManager->getRepository(Facture::class)->findBy([
            "agence" => $agence,
            "statut" => True,
            "isUpdated" => True
        ],[
            "id" => "DESC"
        ]) ; 

        foreach ($factures as $facture) {
            $details = $this->entityManager->getRepository(FactDetails::class)->findBy([
                "facture" => $facture,
                "statut" => True ]
            ) ; 

            $totalGHt = 0 ;
            $totalTva = 0 ;

            foreach ($details as $factureDetail) {
                $tvaVal = (empty($factureDetail->getTvaVal()) || is_null($factureDetail->getTvaVal())) ? 0 : $factureDetail->getTvaVal() ;
                $tva = (($factureDetail->getPrix() * $tvaVal) / 100) * $factureDetail->getQuantite();
                $totalDHt = $factureDetail->getPrix() * $factureDetail->getQuantite()  ;
                $remise = $this->getFactureRemise($factureDetail,$totalDHt) ;
                $totalDHt = $totalDHt - $remise ;

                $totalGHt += $totalDHt ;
                $totalTva += $tva ;
            }

            $remiseGeneral = $this->getFactureRemise($facture,$totalGHt) ;
            $totalTTC = $totalGHt + $totalTva - $remiseGeneral ;

            $facture->setTvaVal(floatval($totalTva)) ;
            $facture->setTotal(floatval($totalTTC)) ;
            $facture->setIsUpdated(null) ;

            $this->entityManager->flush() ;
        }

    }

}
