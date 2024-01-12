<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\Menu;
use App\Entity\MenuAgence;
use App\Entity\MenuUser;
use App\Entity\User;
use App\Entity\UsrAbonnement;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class AdminController extends AbstractController
{
    private $entityManager;
    private $session ;
    private $appService ;
    private $nameUser ;
    public function __construct(EntityManagerInterface $entityManager,SessionInterface $session, AppService $appService)
    {

        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->appService = $appService ;
        $this->appService->checkUrl() ;
        $this->nameUser = strtolower($this->session->get("user")["username"]) ;
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        $this->regenerateUserMenu() ;
        return $this->render('admin/index.html.twig');
    }

    #[Route('pdf/display', name: 'display_pdf')]
    public function displayPdf(): Response
    {
        // Décodez le chemin du fichier PDF
        $pdfFilePath = 'files/tempPdf/'.$this->nameUser.'_pdf.pdf';

        // // Vérifiez si le fichier PDF existe
        // if (!file_exists($pdfFilePath)) {
        //     throw $this->createNotFoundException('Le fichier PDF n\'existe pas.');
        // }

        // // Créez une réponse HTTP pour afficher le PDF
        // $response = new Response();
        // $response->headers->set('Content-Type', 'application/pdf');
        // $response->headers->set('Content-Disposition', 'inline; filename="document.pdf"; target="_blank"'); // Pour afficher le PDF dans le navigateur

        // // Lisez le contenu du fichier PDF et définissez-le comme contenu de la réponse
        // $response->setContent(file_get_contents($pdfFilePath));

        // return $response;
        // $filePdfPath = file_get_contents($pdfFilePath) ;

        // unlink($pdfFilePath) ;

        return $this->render('pdf/displayPdf.html.twig',["pdfFilePath" => $pdfFilePath]);
    }

    

    public function regenerateUserMenu($is_user = null)
    {
        if(!is_null($is_user))
            $user = $is_user ;
        else
            $user = $this->session->get("user")  ; 

        $filename = "files/json/menu/".strtolower($user['username'])."_".$user['agence'].".json" ;

        if(!file_exists($filename))
        {
            $userClass = $this->entityManager
                            ->getRepository(User::class)
                            ->findOneBy(array("email" => $user['email'])) ;
            
            $menus = [] ;
            
            $menuUsers = $this->appService->requestMenu($userClass->getRoles()[0],$userClass,null) ;

            if(!empty($menuUsers))
            {
                $id = 0;
                $this->appService->getMenu($menuUsers,$id,$menus,$user) ;
            } 
            
            $json = json_encode($menus) ;
            file_put_contents($filename, $json); 
        }
    }

    #[Route('/admin/societe/add', name: 'admin_add_societe')]
    public function addSociete()
    {
        $password = $this->appService->generatePassword() ;
        return $this->render('admin/societe/add.html.twig',[
            'password' => $password
        ]);
    }

    #[Route('/admin/societe/save', name:'admin_saveSociete')]
    public function saveSociete(Request $request)
    {
        $nom = $request->request->get('nom') ;
        $region = $request->request->get('region') ;
        $capacite = $request->request->get('capacite') ;
        $adresse = $request->request->get('adresse') ;
        $telephone = $request->request->get('telephone') ;

        $username = $request->request->get('username') ;
        $password = $request->request->get('password') ;
        $email = $request->request->get('email') ;
        $poste = $request->request->get('poste') ;
        $abonnement = $request->request->get('abonnement') ;

        $data = [$nom, $region, $capacite, $adresse, $telephone,$username ,$password ,$email ,$poste,$abonnement ] ;
        $dataMessage = ["nom", "region", "nombre de compte", "adresse", "telephone","nom d'utilisateur" ,"mot de passe" ,"email" ,"responsabilite", "Date d'abonnement" ] ;
        
        $result = $this->appService->verificationElement($data,$dataMessage) ;

        $allow = $result["allow"] ;
        $type = $result["type"] ;
        $message = $result["message"] ;

        $chk_uname = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => strtoupper($username),
        ]) ;

        if(!is_null($chk_uname))
        {
            $allow = False ;
            $type="orange" ;
            $message = "Votre nom d'utilisateur existe déjà, veuillez entrer un autre" ;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $chk_email = $this->entityManager->getRepository(User::class)->findOneBy(["email" => $email]) ;
            if(!is_null($chk_email))
            {
                $allow = False ;
                $type="orange" ;
                $message = "Votre adresse email existe déjà, veuillez entrer un autre" ;
            }
        } else {
            $allow = False ;
            $type="red" ;
            $message = "Votre adresse email est invalide" ;
        }
        
        if(strlen($password) < 8)
        {
            $allow = False ;
            $type="orange" ;
            $message = "Votre mot de passe doit contenir au moins 8 caractère" ;
        }

        if(!$allow)
            return new JsonResponse(["message"=>$message, "type"=>$type]) ;

        $agence = new Agence() ;
        $agence->setNom(strtoupper($nom));
        $agence->setRegion($region);
        $agence->setCapacite($capacite);
        $agence->setAdresse($adresse) ;
        $agence->setTelephone($telephone) ;
        $agence->setStatut(True) ;
        $agence->setCreatedAt(new \DateTimeImmutable) ;
        $agence->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($agence);
        $this->entityManager->flush();

        $user = new User() ;
        
        $encodedPass = $this->appService->hashPassword($user,$password) ;

        $user->setUsername(strtoupper($username)) ;
        $user->setEmail($email);
        $user->setPassword($encodedPass) ;
        $user->setPoste($poste) ;
        $user->setAgence($agence) ;
        $user->setStatut(True) ; 
        $user->setRoles(["MANAGER"]) ;
        $user->setCreatedAt(new \DateTimeImmutable) ;
        $user->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $userAbonnement = new UsrAbonnement() ;

        $userAbonnement->setUser($user) ;
        $userAbonnement->setDateEtHeure(\DateTime::createFromFormat("d/m/Y H:i:s", $abonnement.date(" H:i:s"))) ;
        $userAbonnement->setDateDebut(\DateTime::createFromFormat("d/m/Y",date("d/m/Y"))) ;
        $userAbonnement->setStatut(True) ;
        $userAbonnement->setCreatedAt(new \DateTimeImmutable) ;
        $userAbonnement->setUpdatedAt(new \DateTimeImmutable) ;
        
        $this->entityManager->persist($userAbonnement);
        $this->entityManager->flush();

        return new JsonResponse(["message"=>$message, "type"=>$type]) ;
    }

    #[Route('/admin/password/get', name:"getRandomPass")]
    public function getRandomPass()
    {
        $randomPass = $this->appService->generatePassword() ;

        return new JsonResponse(["randomPass" => $randomPass]) ;
    }

    #[Route('/admin/societe/list',name:'admin_listSociete')]
    public function listSociete()
    {
        $agences = $this->entityManager->getRepository(Agence::class)->findAll() ;

        return $this->render('admin/societe/list.html.twig',[
            "agences" => $agences
        ]);
    }

    #[Route('/admin/menu/attribution',name:'menu_attribution')]
    public function menuAttribution()
    {
        $menus = [] ;
        $filename = "files/json/menuUser.json" ;
        if(!file_exists($filename))
            $this->appService->generateUserMenu($menus,$filename) ;
        
        $menu_array = json_decode(file_get_contents($filename)) ;
        $agences = $this->entityManager->getRepository(Agence::class)->findAll() ;

        $countArray = [] ;
        foreach ($agences as $agence) {
            $countUser = $this->entityManager->getRepository(User::class)->countUser($agence->getId()) ;
            array_push($countArray,$countUser["countUser"]) ;
        }
        
        $connection = $this->entityManager->getConnection();
        $databaseName = $connection->getDatabase();

        return $this->render('admin/menu/attribution.html.twig',[
            "agences" => $agences,
            "menus" => $menu_array,
            "countArray" => $countArray,
            "databaseName" => $databaseName 
        ]);
    }

    #[Route('/admin/menu/attribution/save',name:'admin_save_attribution')]
    public function saveAttributionMenu(Request $request){
        $agence = $request->request->get('agence') ;
        try
        {
            $menus = (array)$request->request->get('menus') ;
            $compareMenu = [] ;
            foreach ($menus as $unMenu) {
                array_push($compareMenu,$unMenu) ;
            }

            $agence = $this->entityManager->getRepository(Agence::class)->find($agence) ;

            if(!is_null($menus))
            {
                $toremove = [];
                for ($i=0; $i < count($menus); $i++) { 
                    $menu = $this->entityManager->getRepository(Menu::class)->find($menus[$i]) ;
                    $chkMenuAg = $this->entityManager->getRepository(MenuAgence::class)->findOneBy([
                        "menu" => $menu,
                        "agence" => $agence
                    ]) ;

                    if(!is_null($chkMenuAg))
                    {
                        array_push($toremove,$menus[$i]) ;
                    }
                }
                
                $menus = array_diff($menus, $toremove);
                $addMenus = [] ;

                foreach ($menus as $menu) {
                    array_push($addMenus,$menu) ; 
                }

                for ($i=0; $i < count($addMenus); $i++) { 
                    $menuAgence = new MenuAgence() ;
                    $menuAgence->setAgence($agence) ;
                    $menu = $this->entityManager->getRepository(Menu::class)->find($addMenus[$i]) ;
                    $menuAgence->setMenu($menu) ;
                    $menuAgence->setStatut(True) ;
                    $menuAgence->setCreatedAt(new \DateTimeImmutable) ;
                    $menuAgence->setUpdatedAt(new \DateTimeImmutable) ;
    
                    $this->entityManager->persist($menuAgence);
                    $this->entityManager->flush();
                }

                $menuAgAll = $this->entityManager->getRepository(MenuAgence::class)->findBy([
                    "agence" => $agence
                ]) ;

                // dd($menuAgAll) ;
                foreach ($menuAgAll as $mAgence) {
                    if(!in_array($mAgence->getMenu()->getId(),$compareMenu))
                    {
                        $this->entityManager->remove($mAgence);
                        $this->entityManager->flush();
                    }
                }

                $user = $this->entityManager->getRepository(User::class)->findManager($agence->getId()) ;

                $filename = "files/json/menu/".strtolower($user['username'])."_".$user['agence'].".json" ;
                if(file_exists($filename))
                    unlink($filename) ;
                $this->regenerateUserMenu($user) ;
                $type = 'green' ;
                $message = "Information enregistré avec succès" ;
            }
            else
            {
                $type = 'orange' ;
                $message = "Veuiller sélectionner un menu" ;
            }
            
        }
        catch(\Exception $e)
        {
            if(empty($agence))
            {
                $type = 'orange' ;
                $message = "Veuillez sélectionner une agence" ;
            }
            else
            {
                $type = 'red' ;
                $message = $e->getMessage() ;
            }
            
        }
        
        return new JsonResponse(["type" => $type, "message" => $message]) ;
    }

    #[Route('/admin/menu/creation',name:'admin_menu_creation')]
    public function menuCreation()
    {
        
        $menus = [] ;
        $pathMenuUser = "files/json/menuUser.json" ;
        if(!file_exists($pathMenuUser))
            $this->appService->generateUserMenu($menus,$pathMenuUser) ;

        $pathListeMenu = "files/json/listeMenu.json" ;
        if(!file_exists($pathListeMenu))
            $this->appService->generateListeMenu() ;
        $listes = json_decode(file_get_contents($pathListeMenu)) ;
        $menu_array = json_decode(file_get_contents($pathMenuUser)) ;
        return $this->render('admin/menu/creation.html.twig',[
            "menus" => $menu_array,
            "listes" => $listes
        ]);
            
    }

    #[Route('admin/menu/disp/one', name:'disp_edit_menu')]
    public function menuDisplayOne(Request $request)
    {
        $liste = [] ;
        try {
            $id = $request->request->get('value') ;
            $menu = $this->entityManager->getRepository(Menu::class)->find($id) ;

            $parent = 0 ;
            if(!is_null($menu->getMenuParent()))
                $parent = $menu->getMenuParent()->getId() ;
            $liste["parent"] = $parent;
            $liste["nom"] = $menu->getNom() ;
            $liste["icone"] = $menu->getIcone();
            $liste["route"] = $menu->getRoute();
            $liste["rang"] = $menu->getRang();

            $title = "Succès" ;
            $type = "green" ;
            $message = "Vous pouvez editer votre menu maintenant" ;
        } catch (\Exception $e) {
            $title = "Erreur" ;
            $type = "red" ;
            $message = $e->getMessage() ;      
        }
        return new JsonResponse([
            "title" => $title,
            "type" => $type,
            "message" => $message,
            "liste" => $liste
        ]);
    }

    #[Route('admin/menu/valid/creation', name:'admin_validCreation')]
    public function validCreation(Request $request)
    {
        $type = $request->request->get("type") ;
        $idMenu = $request->request->get("idMenu") ;
        $username = $this->session->get("user")["username"] ;
        if($type == "supprimer")
        {
            $menu = $this->entityManager->getRepository(Menu::class)->find($idMenu) ;
            $menu->setStatut(False) ;
            $this->entityManager->flush() ;

            $menus = [] ;
            $pathMenuUser = "files/json/menuUser.json" ;
            unlink($pathMenuUser) ;
            if(!file_exists($pathMenuUser))
                $this->appService->generateUserMenu($menus,$pathMenuUser) ;

            $pathListeMenu = "files/json/listeMenu.json" ;
            unlink($pathListeMenu) ;
            if(!file_exists($pathListeMenu))
                $this->appService->generateListeMenu() ;
            
            if($username == "SHISSAB")
            {
                $path = "files/json/menu/".strtolower($username)."_".$this->session->get("user")['agence'].".json" ;
                unlink($path) ;
                $this->regenerateUserMenu() ;
            }
                

            return new JsonResponse(["type" => "green", "message" => "Suppression effectué"]) ;
        }
        
        $menu_parent_id = $request->request->get("menu_parent_id") ;
        $nom = $request->request->get("nom") ;
        $icone = $request->request->get("icone") ;
        $route = $request->request->get("route") ;
        $rang = $request->request->get("rang") ;
        $data = [$nom,$icone,$rang] ;
        $dataMessage = ["Nom","Icone","Rang"] ;
        $result = $this->appService->verificationElement($data,$dataMessage) ;
        if($result["allow"])
        {
            if(empty($route))
                $route = null ;
            if($type == "enregistrer")
            {
                $menu = new Menu() ; 
            }
            else
            {
                $menu = $this->entityManager->getRepository(Menu::class)->find($idMenu) ;  
            } 
            if(intval($menu_parent_id) == 0)
                $menuParent = null;
            else
                $menuParent = $this->entityManager->getRepository(Menu::class)->find($menu_parent_id) ; 

            $menu->setMenuParent($menuParent);
            $menu->setNom($nom);
            $menu->setIcone("fa-".$icone);
            $menu->setRoute($route);
            $menu->setRang($rang);
            $menu->setCreatedAt(new \DateTimeImmutable);
            $menu->setUpdatedAt(new \DateTimeImmutable);
            $menu->setStatut(True);

            $this->entityManager->persist($menu) ;
            $this->entityManager->flush() ;

            $menus = [] ;
            $pathMenuUser = "files/json/menuUser.json" ;
            unlink($pathMenuUser) ;
            if(!file_exists($pathMenuUser))
                $this->appService->generateUserMenu($menus,$pathMenuUser) ;

            $pathListeMenu = "files/json/listeMenu.json" ;
            unlink($pathListeMenu) ;
            if(!file_exists($pathListeMenu))
                $this->appService->generateListeMenu() ;
            
            if($username == "SHISSAB")
            {
                $path = "files/json/menu/".strtolower($username)."_".$this->session->get("user")["agence"].".json" ;
                unlink($path) ;
                $this->regenerateUserMenu() ;
            }
        }

        return new JsonResponse($result) ;
    }

    #[Route('admin/menu/affiche/corbeille',name:'menu_corbeille')]
    public function menuCorbeille()
    {
        
        $menus = $this->entityManager->getRepository(Menu::class)->findBy(["statut" => False]) ;

        return $this->render('admin/menu/corbeille.html.twig',[
            "menus" => $menus
        ]);
    }

    #[Route('admin/menu/restore/corbeille',name:'restore_menu_corbeille')]
    public function restoreMenuCorbeille(Request $request)
    {
        $idMenu = $request->request->get("idMenu") ;
        $menu = $this->entityManager->getRepository(Menu::class)->find($idMenu);
        $menu->setStatut(True) ;
        $this->entityManager->flush() ;
        $this->refreshAll() ;
        return new JsonResponse([
            "type" => "green",
            "message" => "Elément restauré avec succès"
            ]) ;
    }

    #[Route('refresh/all/', name:'refresh_all')]
    public function refreshAll()
    {
        $user = $this->session->get("user")  ; 
        $filename = "files/json/menu/".strtolower($user['username'])."_".$user['agence'].".json" ;
        if(file_exists($filename))
            unlink($filename) ;
        $this->regenerateUserMenu() ;
        if($user['role'] == "ADMIN")
        {
            $menus = [] ;
            $pathMenuUser = "files/json/menuUser.json" ;
            unlink($pathMenuUser) ;
            if(!file_exists($pathMenuUser))
                $this->appService->generateUserMenu($menus,$pathMenuUser) ;

            $pathListeMenu = "files/json/listeMenu.json" ;
            if(file_exists($pathListeMenu))
                unlink($pathListeMenu) ;
            if(!file_exists($pathListeMenu))
                $this->appService->generateListeMenu() ;
        }
        if($user["role"] == "ADMIN")
        {
            $url = $this->generateUrl('app_admin');
        }
        else
        {
            $url = $this->generateUrl('app_home');
        }
        
        return new RedirectResponse($url);
    }

    #[Route('admin/agence/manager', name:'manager_agence')]
    public function managerAgence(Request $request)
    {
        $idAgence = $request->request->get("idAgence") ;

        $user = $this->entityManager->getRepository(User::class)->findManager($idAgence) ;

        $filename = "files/json/menu/".strtolower($user['username'])."_".$user['agence'].".json" ;
        if(!file_exists($filename))
            $this->regenerateUserMenu($user) ;
        
        $menuManager = json_decode(file_get_contents($filename)) ;

        $response = [
            "manager" => strtolower($user["username"]),
            "menuManager" => $menuManager
        ] ;
        
        return new JsonResponse($response) ;
    }


    #[Route('admin/data/import', name:'admin_import_data')]
    public function adminImportData()
    {



        return $this->render('admin/importData.html.twig', [
            "filename" => "admin",
            "titlePage" => "Importation de Donnée",
            "with_foot" => true,
        ]);
    }
}
