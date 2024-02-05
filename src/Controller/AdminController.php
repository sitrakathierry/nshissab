<?php

namespace App\Controller;

use App\Entity\AgdCategorie;
use App\Entity\AgdEcheance;
use App\Entity\Agence;
use App\Entity\Agenda;
use App\Entity\Client;
use App\Entity\CltHistoClient;
use App\Entity\CltSociete;
use App\Entity\CltTypes;
use App\Entity\CrdDetails;
use App\Entity\CrdFinance;
use App\Entity\CrdStatut;
use App\Entity\FactDetails;
use App\Entity\FactHistoPaiement;
use App\Entity\FactModele;
use App\Entity\FactPaiement;
use App\Entity\FactRemiseType;
use App\Entity\FactType;
use App\Entity\Facture;
use App\Entity\HistoHistorique;
use App\Entity\ImportModule;
use App\Entity\Menu;
use App\Entity\MenuAgence;
use App\Entity\MenuUser;
use App\Entity\PrdApprovisionnement;
use App\Entity\PrdCategories;
use App\Entity\PrdEntrepot;
use App\Entity\PrdFournisseur;
use App\Entity\PrdHistoEntrepot;
use App\Entity\PrdHistoFournisseur;
use App\Entity\PrdMargeType;
use App\Entity\PrdPreferences;
use App\Entity\PrdType;
use App\Entity\PrdVariationPrix;
use App\Entity\Produit;
use App\Entity\User;
use App\Entity\UsrAbonnement;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

use function PHPSTORM_META\type;

class AdminController extends AbstractController
{
    private $entityManager;
    private $session ;
    private $appService ;
    private $nameUser ;
    private $agence ;
    private $nameAgence ;
    private $userObj ;
    private $user ;

    public function __construct(EntityManagerInterface $entityManager,SessionInterface $session, AppService $appService)
    {

        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->appService = $appService ;
        $this->appService->checkUrl() ;
        $this->nameUser = strtolower($this->session->get("user")["username"]) ;
        $this->agence = $this->entityManager->getRepository(Agence::class)->find($this->session->get("user")["agence"]) ; 
        $this->nameAgence = strtolower($this->agence->getNom())."-".$this->agence->getId().".json" ;
        $this->user = $this->session->get("user") ;
        $this->userObj = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $this->user["username"],
            "agence" => $this->agence
        ]) ;
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
        $filename = "files/systeme/admin/agence/allAgence.json" ;

        if(!file_exists($filename))
            $this->appService->generateAllAgence($filename) ;

        $agences = json_decode(file_get_contents($filename)) ;

        $modules = $this->entityManager->getRepository(ImportModule::class)->findBy([
            "statut" => True
        ],[
            "rang" => "ASC"
        ]) ;

        $dispAgence = True ;

        if($this->userObj->getRoles()[0] != "ADMIN")
        {
            $dispAgence = False ;
        }

        $nomAgence = $this->agence->getNom() ;

        return $this->render('admin/importData.html.twig', [
            "filename" => "admin",
            "titlePage" => "Importation de Données",
            "with_foot" => true,
            "agences" => $agences,
            "modules" => $modules,
            "dispAgence" => $dispAgence,
            "nomAgence" => $nomAgence,
        ]);
    }

    #[Route('admin/data/import/display', name:'admin_import_data_display')]
    public function adminDisplayDataToImport(Request $request)
    {
        try {
            $import_agence = $request->request->get("import_agence") ;
            $import_module = $request->request->get("import_module") ;
            $base64Data = $request->request->get("base64Data") ;

            // Décoder la chaîne base64 en binaire
            $binaryData = base64_decode($base64Data);
            
            // Chemin vers le fichier Excel
            $filename = "files/dataToImport.xslx" ;
    
            if(file_exists($filename))
                unlink($filename) ;
    
            file_put_contents($filename,$binaryData) ;
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
        finally{
            try {
                // Charger le fichier Excel
                $spreadsheet = IOFactory::load($filename);
        
                // Récupérer tous les noms de feuilles
                $nomsFeuilles = $spreadsheet->getSheetNames();
        
                // dd($nomsFeuilles) ;
                // Récupérer les données de chaque feuille
        
                $allData = [] ;
        
                foreach ($nomsFeuilles as $nomFeuille) {
                    $feuille = $spreadsheet->getSheetByName($nomFeuille);
        
                    // Vérifier si la feuille existe
                    if ($feuille) {
                        $donnees = [];
                        foreach ($feuille->getRowIterator() as $ligne) {
                            $ligneData = [];
                            foreach ($ligne->getCellIterator() as $cellule) {
                                $ligneData[] = $cellule->getValue();
                            }
                            $donnees[$nomFeuille][] = $ligneData;
                        }
        
                        // Faire quelque chose avec les données de la feuille (par exemple, les afficher)
                        
                        $allData[] = $donnees ;
                    } 
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
            finally {
                $module = $this->entityManager->getRepository(ImportModule::class)->find($import_module) ;
                $allDatas = [] ;
                if($module->getReference() == "STOCK")
                {
                    $variations = $allData[1]["Variation_Produit"] ;
                    $produits = $allData[0]["Produit"] ;
                    
                    for ($i=0; $i < count($variations); $i++) { 
                        if($i == 0)
                            continue ;
            
                        $variation = $variations[$i] ;
                        for ($j=0; $j < count($produits); $j++) { 
                            if($j == 0)
                                continue ;
            
                            $produit = $produits[$j] ;
                            if($variation[0] == $produit[1])
                            {
                                if(!isset($allDatas[$variation[0]]))
                                {
                                    $allDatas[$variation[0]] = [
                                        "categorie" => $produit[0],
                                        "code_produit" => $produit[1],
                                        "unite" => $produit[2],
                                        "nom_produit" => $produit[3],
                                        "designation" => $produit[4],
                                        "description" => $produit[5],
                                    ] ;
                                }
            
                                $allDatas[$variation[0]]["variations"][] = [
                                    "code_produit" => $variation[0],
                                    "indice" => $variation[1],
                                    "entrepot" => $variation[2],
                                    "fournisseur" => $variation[3],
                                    "expiree_le" => $variation[4],
                                    "prix_achat" => $variation[5],
                                    "charge" => $variation[6],
                                    "prix_revient" => $variation[7],
                                    "marge" => $variation[8],
                                    "stock" => $variation[9],
                                    "stock_alert" => $variation[10],
                                    "prix_vente" => $variation[11],
                                ] ;
    
                                break ;
                            }
                        }
                    }

                }
                else if($module->getReference() == "FACT")
                {
                    // suite enregistrement information si ce n'est pas un enregistrement de produit
                    $factures = $allData[0]["factures"] ;
                    $details = $allData[1]["details"] ;

                    foreach ($factures as $key => $value) {
                        if($key == 0)
                            continue ;

                        $elementFacture = $factures[$key] ;

                        if($elementFacture[10] == "%")
                        {
                            $remiseGen = ($elementFacture[13] * $elementFacture[11]) / 100 ; 
                        }
                        else
                        {
                            $remiseGen = $elementFacture[11] ;
                        }

                        $keyTab = $elementFacture[0].''.strtoupper($elementFacture[6]) ;

                        $allDatas[$keyTab] = [
                            "num_fact" => $elementFacture[0] ,
                            "type_fact" => $elementFacture[1] ,
                            "modele_fact" => $elementFacture[2] ,
                            "mode_paiement" => $elementFacture[3] ,
                            "info1_paiement" => $elementFacture[4] ,
                            "info2_paiement" => $elementFacture[5] ,
                            "nom_client" => $elementFacture[6] ,
                            "type_client" => $elementFacture[7] ,
                            "date_fact" => $elementFacture[9] ,
                            "label_date_fact" => $this->appService->timeToDate($elementFacture[9])->format("d/m/Y") ,
                            "lieu_fact" => $elementFacture[8] ,
                            "type_remise" => $elementFacture[10] ,
                            "val_remise" => $elementFacture[11] ,
                            "montant_tva" => $elementFacture[12] ,
                            "total_ht" => $elementFacture[13] ,
                            "description" => $elementFacture[14] ,
                            "date_creation" => $elementFacture[15] ,
                            "totalTva" => 0,
                            "totalHt" => 0,
                            "remiseGen" => $remiseGen ,
                        ] ;
                    }

                    foreach ($details as $key => $value) {
                        if($key == 0)
                            continue ;

                        $detail = $details[$key] ;

                        $keyDetailTab = $detail[0].''.strtoupper($detail[1]) ;

                        $totalLigne = $detail[3] * $detail[4] ;

                        if($detail[6] == "%")
                        {
                            $remiseVal = ($totalLigne * $detail[7]) / 100 ; 
                        }
                        else
                        {
                            $remiseVal = $detail[7] ;
                        }

                        $totalLigne = $totalLigne - $remiseVal ;

                        $allDatas[$keyDetailTab]["details"][] = [
                            "client" => $detail[1],
                            "designation" => $detail[2],
                            "quantite" => $detail[3],
                            "prix_unitaire" => $detail[4],
                            "montant_tva" => $detail[5],
                            "type_remise" => $detail[6],
                            "val_remise" => $detail[7],
                            "montant_total" => $detail[8],
                            "total_ligne" => $totalLigne,
                        ] ;

                        $allDatas[$keyDetailTab]["totalTva"] += (($detail[4] * $detail[5]) / 100) * $detail[3] ;
                        $allDatas[$keyDetailTab]["totalHt"] += $totalLigne ;
                    }
                }

                // dd($allDatas) ;
            }
        }

        $agence = $this->entityManager->getRepository(Agence::class)->find($import_agence) ;

        $nameFile = strtolower($agence->getNom())."-".$agence->getId().".json" ;

        $filename = "files/systeme/admin/import/".$nameFile ;

        if(file_exists($filename))
            unlink($filename) ;

        file_put_contents($filename, json_encode($allDatas)) ;
        
        // dd($allDatas) ;

        // dd($allDatas) ;

        if($module->getReference() == "STOCK")
        {
            $response = $this->renderView("admin/templateDisplayProduit.html.twig",[
                "dataToImports" => $allDatas
            ]) ;
        }
        else if($module->getReference() == "FACT")
        {
            $response = $this->renderView("admin/templateDisplayFacture.html.twig",[
                "dataToImports" => $allDatas
            ]) ;
        }

        return new Response($response);
    }

    #[Route('admin/data/import/save', name:'admin_import_data_save')]
    public function adminSaveDataToImport(Request $request)
    {
        $import_module = $request->request->get("import_module") ;
        $import_agence = $request->request->get("import_agence") ;
        $import_produit_annulee = $request->request->get("import_produit_annulee") ;

        $module = $this->entityManager->getRepository(ImportModule::class)->find($import_module) ;
        $agence = $this->entityManager->getRepository(Agence::class)->find($import_agence) ;

        $nameFile = strtolower($agence->getNom())."-".$agence->getId().".json" ;

        $filename = "files/systeme/admin/import/".$nameFile ;
        
        $user = null;
        if($this->userObj->getRoles()[0] != "ADMIN")
        {
            $user = $this->userObj ;
        }
        else
        {
            $users = $this->entityManager->getRepository(User::class)->findBy([
                "agence" => $agence,
                "statut" => True
            ]) ;
            
    
            foreach ($users as $membre) {
                if($membre->getRoles()[0] == "MANAGER")
                {
                    $user = $membre ;
                    break ;
                }
            }

        }
    
        if($module->getReference() == "STOCK")
        {
            $produits = json_decode(file_get_contents($filename)) ;

            $produits = $this->appService->objectToArray($produits) ;

            // DEBUT ENLEVER DANS LE TABLEAU LES PRODUITS ANNULEE 

            // ...

            // FIN ENLEVER DANS LE TABLEAU LES PRODUITS ANNULEE 


            // DEBUT ENREGISTREMENT
            foreach ($produits as $produit) {
                $codeProduit = $produit["code_produit"] ;

                $produitcChk = $this->entityManager->getRepository(Produit::class)->findOneBy([
                    "codeProduit" => $codeProduit,
                    "agence" => $agence,
                    "statut" => True
                ]) ;
        
                if(!empty($codeProduit) && !is_null($produitcChk))
                {
                    return new JsonResponse([
                        "title" => "Code existant",
                        "message" => "Veuillez supprimer le code ".$codeProduit." car elle existe déjà",
                        "type" => "orange"
                    ]) ;
                }

                $prod_nom_categorie = $produit["categorie"] ;
                $prod_categorie = $produit["categorie"] ;
                $code_produit = $produit["code_produit"] ;
                $prod_nom = $produit["designation"] ;
                $prod_type = $produit["nom_produit"] ;
                $unite_produit = $produit["unite"] ;
                $produit_editor = $produit["description"] ;
                $prod_image = null ;

                $data = [
                    $prod_categorie,
                    $code_produit,
                    $prod_type,
                    $prod_nom,
                    $unite_produit,
                ];

                $dataMessage = [
                    "Catégorie",
                    "Code Produit",
                    "Nom du Produit",
                    "Désignation du Produit",
                    "Unité"
                ] ;

                $result = $this->appService->verificationElement($data,$dataMessage) ;

                if(!$result["allow"])
                    return new JsonResponse($result) ;

                $writer = new PngWriter();
                $qrCode = QrCode::create($codeProduit)
                    ->setEncoding(new Encoding('UTF-8'))
                    ->setSize(2400)
                    ->setMargin(0)
                    ->setForegroundColor(new Color(0, 0, 0))
                    ->setBackgroundColor(new Color(255, 255, 255));

                $base64Data = $writer->write($qrCode, null)->getDataUri();

                $variations = $this->appService->objectToArray($produit["variations"]) ;

                $tableau = [] ;
                foreach ($variations as $variation) {
                    $data = [
                        $variation["entrepot"],
                        $variation["prix_vente"],
                        $variation["stock_alert"],
                        $variation["stock"],
                    ];
            
                    $dataMessage = [
                        "Entrepot",
                        "Prix Vente",
                        "Stock Alert",
                        "Stock",
                    ] ;

                    $result = $this->appService->verificationElement($data,$dataMessage) ;

                    if(!$result["allow"])
                        return new JsonResponse($result) ;
                    
                    $uniteTableau = [] ;
                    
                    $uniteTableau["entrepot"] = $variation["entrepot"] ;
                    $uniteTableau["indice"] = $variation["indice"] ;
                    $uniteTableau["prix_vente"] = $variation["prix_vente"] ;

                    array_push($tableau,$uniteTableau) ;
                }

                $doublons = $this->appService->detecter_doublons($tableau) ;

                if(!empty($doublons))
                {
                    return new JsonResponse([
                        "message" => "Veuiller vérifier vos variations de produit car il y a des doublons (Entrepot, Indice et Prix de Vente); Code Produit -> ".$codeProduit ,
                        "type" => "orange",
                        "error" => "DOUBLON",
                        "doublons" => $doublons,
                    ]) ; 
                }

                // C'est le nom Produit pour le filtre mais ici c'est Prd Type : Enregitrement

                $type = $this->entityManager->getRepository(PrdType::class)->findOneBy([
                    "agence" => $agence,
                    "nom" => strtoupper($produit["nom_produit"]),
                    "statut" => True
                ]) ; 

                if(is_null($type))
                {
                    $type = new PrdType() ;

                    $type->setAgence($agence) ;
                    $type->setNom(strtoupper($produit["nom_produit"])) ;
                    $type->setStatut(True) ;

                    $this->entityManager->persist($type) ;
                    $this->entityManager->flush() ;
                }

                $categorie = $this->entityManager->getRepository(PrdCategories::class)->findOneBy([
                    "agence" => $agence,
                    "nom" => strtoupper($prod_categorie),
                    "statut" => True
                ]) ; 


                if(is_null($categorie))
                {
                    $categorie = new PrdCategories() ;
                    $categorie->setAgence($agence) ;
                    $categorie->setImages("") ;
                    $categorie->setNom(strtoupper($prod_categorie)) ;
                    $categorie->setStatut(True) ;
                    $categorie->setCreatedAt(new \DateTimeImmutable) ;
                    $categorie->setUpdatedAt(new \DateTimeImmutable) ;

                    $this->entityManager->persist($categorie);
                    $this->entityManager->flush();

                    $preference = new PrdPreferences() ;

                    $preference->setCategorie($categorie) ;
                    $preference->setUser($user) ;
                    $preference->setStatut(True) ;
                    $preference->setCreatedAt(new \DateTimeImmutable) ;
                    $preference->setUpdatedAt(new \DateTimeImmutable) ;
        
                    $this->entityManager->persist($preference) ;
                    $this->entityManager->flush() ;
                }
                else
                {
                    $preference = $this->entityManager->getRepository(PrdPreferences::class)->findOneBy([
                        "categorie" => $categorie,
                        "user" => $user,
                        "statut" => True
                    ]) ; 

                    if(is_null($preference))
                    {
                        $preference = new PrdPreferences() ;

                        $preference->setCategorie($categorie) ;
                        $preference->setUser($user) ;
                        $preference->setStatut(True) ;
                        $preference->setCreatedAt(new \DateTimeImmutable) ;
                        $preference->setUpdatedAt(new \DateTimeImmutable) ;
            
                        $this->entityManager->persist($preference) ;
                        $this->entityManager->flush() ;
                    }
                }

                $produit = new Produit() ;

                $produit->setAgence($agence) ;
                $produit->setPreference($preference) ;
                $produit->setUser($user) ;
                $produit->setType($type) ;
                $produit->setCodeProduit($code_produit) ;
                $produit->setQrCode($base64Data) ;
                $produit->setImages(null) ;
                $produit->setNom($prod_nom) ;
                $produit->setDescription($produit_editor) ;
                $produit->setUnite($unite_produit) ;
                $produit->setStock(null) ;
                $produit->setStatut(True) ;
                $produit->setAnneeData(date('Y')) ;
                $produit->setToUpdate(True) ;
                $produit->setCreatedAt(new \DateTimeImmutable) ;
                $produit->setUpdatedAt(new \DateTimeImmutable) ;

                $this->entityManager->persist($produit) ;
                $this->entityManager->flush() ;

                $stockProduit = 0 ;
                $indice = 0 ;
                foreach ($variations  as $variation) {
                    $variationPrix = $this->entityManager->getRepository(PrdVariationPrix::class)->findOneBy([
                        "produit" => $produit,
                        "prixVente" => $variation["prix_vente"],
                        "indice" => empty($variation["indice"]) ? null : $variation["indice"],
                        "statut" => True
                    ]) ; 

                    if(!is_null($variationPrix))
                    {
                        $variationPrix->setStock($variationPrix->getStock() + floatval($variation["stock"])) ;
                        $variationPrix->setUpdatedAt(new \DateTimeImmutable) ;
                        $this->entityManager->flush() ;
                    }
                    else
                    {
                        $variationPrix = new PrdVariationPrix() ;
            
                        $variationPrix->setProduit($produit) ;
                        $variationPrix->setPrixVente($variation["prix_vente"]) ;
                        $variationPrix->setIndice(empty($variation["indice"]) ? null : $variation["indice"]) ;
                        $variationPrix->setStock(floatval($variation["stock"])) ;
                        $variationPrix->setStockAlert(floatval($variation["stock_alert"])) ;
                        $variationPrix->setStatut(True) ;
                        $variationPrix->setCreatedAt(new \DateTimeImmutable) ;
                        $variationPrix->setUpdatedAt(new \DateTimeImmutable) ;
            
                        $this->entityManager->persist($variationPrix) ;
                        $this->entityManager->flush() ;
                    }

                    $histoEntrepot = new PrdHistoEntrepot() ;

                    $entrepot = $this->entityManager->getRepository(PrdEntrepot::class)->findOneBy([
                        "agence" => $agence,
                        "nom" => strtoupper($variation["entrepot"]),
                        "statut" => True
                    ]) ; 

                    if(is_null($entrepot))
                    {
                        $entrepot = new PrdEntrepot() ;

                        $entrepot->setNom(strtoupper($variation["entrepot"])) ;
                        $entrepot->setAdresse(null) ;
                        $entrepot->setTelephone(null) ;
                        $entrepot->setAgence($agence) ;
                        $entrepot->setStatut(True) ;
                        $entrepot->setCreatedAt(new \DateTimeImmutable) ;
                        $entrepot->setUpdatedAt(new \DateTimeImmutable) ;

                        $this->entityManager->persist($entrepot) ;
                        $this->entityManager->flush() ;
                    }


                    if(empty($variation["expiree_le"]))
                    {
                        $expirer = null;
                    }
                    else
                    {
                        $expirer = date('Y-m-d', strtotime('1899-12-30 +' . $variation["expiree_le"] . ' days'));
                        $expirer = \DateTime::createFromFormat('Y-m-d', $expirer) ;
                    }

                    $histoEntrepot->setEntrepot($entrepot) ;
                    $histoEntrepot->setVariationPrix($variationPrix) ;
                    // $histoEntrepot->setIndice(empty($crt_indice[$key]) ? null : $crt_indice[$key]) ;
                    $histoEntrepot->setStock($variation["stock"]) ;
                    $histoEntrepot->setStatut(True) ;
                    $histoEntrepot->setAgence($agence) ;
                    $histoEntrepot->setAnneeData(date('Y')) ;
                    $histoEntrepot->setCreatedAt(new \DateTimeImmutable) ;
                    $histoEntrepot->setUpdatedAt(new \DateTimeImmutable) ;

                    $this->entityManager->persist($histoEntrepot) ;
                    $this->entityManager->flush() ;

                    $approvisionnement = new PrdApprovisionnement() ;

                    $margeType = $this->entityManager->getRepository(PrdMargeType::class)->find(1) ;

                    $approvisionnement->setAgence($agence) ;
                    $approvisionnement->setUser($user) ;
                    $approvisionnement->setHistoEntrepot($histoEntrepot) ;
                    $approvisionnement->setVariationPrix($variationPrix) ;
                    $approvisionnement->setMargeType($margeType) ;
                    $approvisionnement->setQuantite($variation["stock"]) ;
                    $approvisionnement->setPrixAchat(empty($variation["prix_achat"]) ? null : floatval($variation["prix_achat"])) ;
                    $approvisionnement->setCharge(empty($variation["charge"]) ? null : floatval($variation["charge"])) ;
                    $approvisionnement->setMargeValeur(empty($variation["marge"]) ? null : floatval($variation["marge"])) ;
                    $approvisionnement->setPrixRevient(empty($variation["prix_revient"]) ? null : floatval($variation["prix_revient"])) ;
                    $approvisionnement->setPrixVente(floatval($variation["prix_vente"])) ;
                    $approvisionnement->setExpireeLe($expirer) ;
                    $approvisionnement->setDateAppro(null) ;
                    $approvisionnement->setDescription("Création de Produit Code : ".$variation["code_produit"]) ;
                    $approvisionnement->setCreatedAt(new \DateTimeImmutable) ;
                    $approvisionnement->setUpdatedAt(new \DateTimeImmutable) ;

                    $this->entityManager->persist($approvisionnement) ;
                    $this->entityManager->flush() ;

                    $fournisseur = $this->entityManager->getRepository(PrdFournisseur::class)->findOneBy([
                        "agence" => $agence,
                        "nom" => strtoupper($variation["fournisseur"]),
                        "statut" => True,
                    ]) ;
                    
                    if(is_null($fournisseur))
                    {
                        $fournisseur = new PrdFournisseur() ;

                        $fournisseur->setAgence($agence) ;
                        $fournisseur->setNom(strtoupper($variation["fournisseur"])) ;
                        $fournisseur->setTelBureau(null) ;
                        $fournisseur->setAdresse(null) ;
                        $fournisseur->setNomContact(null) ;
                        $fournisseur->setTelMobile(null) ;
                        $fournisseur->setEmail(null) ;
                        $fournisseur->setStatut(True) ;
                        $fournisseur->setCreatedAt(new \DateTimeImmutable) ;
                        $fournisseur->setUpdatedAt(new \DateTimeImmutable) ;

                        $this->entityManager->persist($fournisseur) ;
                        $this->entityManager->flush() ;
                    }

                    $histoFournisseur = new PrdHistoFournisseur() ;
                    
                    $histoFournisseur->setFournisseur($fournisseur) ;
                    $histoFournisseur->setApprovisionnement($approvisionnement) ;
                    $histoFournisseur->setCreatedAt(new \DateTimeImmutable) ;
                    $histoFournisseur->setUpdatedAt(new \DateTimeImmutable) ;

                    $this->entityManager->persist($histoFournisseur) ;
                    $this->entityManager->flush() ;

                    $stockProduit += $variation["stock"] ;
                }

                $produit->setStock($stockProduit) ;
                $this->entityManager->flush() ;

                // DEBUT SAUVEGARDE HISTORIQUE

                $this->entityManager->getRepository(HistoHistorique::class)
                ->insererHistorique([
                    "refModule" => "STOCK",
                    "nomModule" => "GESTION DE STOCK",
                    "refAction" => "CRT",
                    "user" => $user,
                    "agence" => $agence,
                    "nameAgence" => $nameFile,
                    "description" => "Nouveau Produit ; Code Produit : ". $code_produit." ; Nom Produit : ".strtoupper($prod_nom) ,
                ]) ;

                // FIN SAUVEGARDE HISTORIQUE

            }

            // FIN ENREGISTREMENT
            
            if(!is_null($user))
            {
                $username = strtolower($user->getUsername()) ;
                $userId = $user->getId() ;
            }
            else
            {
                $username = "_" ;
                $userId = "_" ;
            }

            $dataFilenames = [
                "files/systeme/stock/categorie(agence)/".$nameFile,
                "files/systeme/stock/preference(user)/".$username."_".$userId.".json",
                "files/systeme/stock/entrepot(agence)/".$nameFile,
                "files/systeme/stock/fournisseur(agence)/".$nameFile ,
                "files/systeme/stock/stock_general(agence)/".$nameFile,
                "files/systeme/stock/stock_entrepot(agence)/".$nameFile,
                "files/systeme/stock/type(agence)/".$nameFile,
                "files/systeme/stock/stockType(agence)/".$nameFile ,
                "files/systeme/stock/stockGEntrepot(agence)/".$nameFile ,
            ] ;

            foreach ($dataFilenames as $dataFilename) {
                if(file_exists($dataFilename))
                    unlink($dataFilename) ;
            }
        }
        else if($module->getReference() == "FACT") // INSERTION FACTURE
        { 
            /*
                num_fact
                type_fact
                modele_fact
                mode_paiement
                info1_paiement
                info2_paiement
                nom_client
                type_client
                date_fact
                lieu_fact
                type_remise
                val_remise
                montant_tva
                total_ht
                description
                date_creation
            */

            $tabKeyRef = [
                "DEFINITIVE" => "DF",
                "ESPECE" => "ES",
                "CREDIT" => "CR",
                "PRODUIT" => "PROD",
                "PRESTATION" => "PSTD",
                "%" => 100,
                "MONTANT" => 1,
            ] ;

            $factures = json_decode(file_get_contents($filename)) ;

            $factures = $this->appService->objectToArray($factures) ;

            foreach ($factures as $facture) {
                
                $facture["details"] = $this->appService->objectToArray($facture["details"]) ;

                $type = $this->entityManager->getRepository(FactType::class)->findOneBy([
                    "reference" =>  $tabKeyRef[$facture["type_fact"]]
                ]) ; 
    
                $paiement = $this->entityManager->getRepository(FactPaiement::class)->findOneBy([
                    "reference" => $tabKeyRef[$facture["mode_paiement"]]
                ]) ; 
        
                $modele = $this->entityManager->getRepository(FactModele::class)->findOneBy([
                    "reference" => $tabKeyRef[$facture["modele_fact"]]
                ]) ; 
        
                // DEBUT D'INSERTION DE DONNEE
        
                if($facture["type_client"] == "PHYSIQUE")
                {
                    $testClient = $this->entityManager->getRepository(Client::class)->findOneBy([
                        "agence" => $agence,
                        "nom" => strtoupper($facture["nom_client"]),
                    ]) ;
                }
                else
                {
                    $testClient = $this->entityManager->getRepository(CltSociete::class)->findOneBy([
                        "agence" => $agence,
                        "nom" => strtoupper($facture["nom_client"]),
                    ]) ;
                }
    
                if(is_null($testClient))
                {
                    $typeClient = $this->entityManager->getRepository(CltTypes::class)->findOneBy([
                        "reference" => $facture["type_client"]
                    ]) ;
        
                    if($typeClient->getReference() == "MORAL")
                    {
                        $societe = new CltSociete() ;
        
                        $societe->setAgence($agence) ;
                        $societe->setNom(strtoupper($facture["nom_client"])) ;
        
                        $this->entityManager->persist($societe) ;
                        $clientP = null ;
                    }
                    else
                    {
                        $clientP = new Client() ;
        
                        $clientP->setAgence($agence) ;
                        $clientP->setNom(strtoupper($facture["nom_client"])) ;
                        
                        $this->entityManager->persist($clientP) ;
                        
                        $societe = null ;
                    }

                    $this->entityManager->flush() ;

                    $client = new CltHistoClient() ;
        
                    $client->setAgence($agence) ;
                    $client->setClient($clientP) ;
                    $client->setSociete($societe) ;
                    $client->setType($typeClient) ;
                    $client->setUrgence(null) ;
                    $client->setStatut(True) ;
                    $client->setCreatedAt(new \DateTimeImmutable) ;
                    $client->setUpdatedAt(new \DateTimeImmutable) ;
        
                    $this->entityManager->persist($client) ;
                    $this->entityManager->flush() ;
        
                    $filename = "files/systeme/client/client(agence)/".$nameFile ;
        
                    if(file_exists($filename))
                        unlink($filename) ;
                }
                else
                {
                    if($facture["type_client"] == "PHYSIQUE")
                    {
                        $client = $this->entityManager->getRepository(CltHistoClient::class)->findOneBy([
                            "client" => $testClient
                        ]) ; 
                    }
                    else
                    {
                        $client = $this->entityManager->getRepository(CltHistoClient::class)->findOneBy([
                            "societe" => $testClient
                        ]) ; 
                    }
                }
                
                $typeRemise = $this->entityManager->getRepository(FactRemiseType::class)->findOneBy([
                    "calcul" => $tabKeyRef[$facture["type_remise"]]
                ]) ;

                if($tabKeyRef[$facture["type_remise"]] == 100)
                {
                    $remiseG = ($facture["total_ht"] * $facture["val_remise"]) / 100 ; 
                }
                else
                {
                    $remiseG = $facture["val_remise"] ;
                }

                $totalTtc = $facture["total_ht"] - $remiseG ;

                $dateFacture = $this->appService->timeToDate($facture["date_fact"]) ;
                $dateCreation = $this->appService->timeToDate($facture["date_creation"]) ;
                $dateCreation = \DateTimeImmutable::createFromMutable($dateCreation);

                $existFacture = $this->entityManager->getRepository(Facture::class)->findOneBy([
                    "agence" => $agence,
                    "client" => $client,
                    "numFact" => $facture["num_fact"],
                    "statut" => True,
                ]) ;

                if(!is_null($existFacture))
                    continue ;

                // DEBUT INSERTION

                $newFacture = new Facture() ;

                $newFacture->setAgence($agence) ;
                $newFacture->setUser($user) ;
                $newFacture->setClient($client) ;
                $newFacture->setType($type);
                $newFacture->setModele($modele) ;
                $newFacture->setRemiseType($typeRemise) ;
                $newFacture->setRemiseVal($facture["val_remise"]) ;
                $newFacture->setNumFact($facture["num_fact"]) ;
                $newFacture->setDescription($facture["description"]) ;
                $newFacture->setTvaVal(floatval($facture["totalTva"])) ;
                $newFacture->setLieu($facture["lieu_fact"]) ;
                $newFacture->setDate($dateFacture) ;
                $newFacture->setTotal(floatval($totalTtc)) ;
                $newFacture->setDevise(NULL) ;
                $newFacture->setStatut(True) ;
                $newFacture->setIsUpdated(True) ;
                $newFacture->setIsImported(True) ;
                $newFacture->setAnneeData($dateFacture->format('Y')) ;
                $newFacture->setCreatedAt($dateCreation) ;
                $newFacture->setUpdatedAt(new \DateTimeImmutable) ;
        
                $this->entityManager->persist($newFacture) ;
                $this->entityManager->flush() ;
        
                // DEBUT SAUVEGARDE HISTORIQUE
        
                $this->entityManager->getRepository(HistoHistorique::class)
                ->insererHistorique([
                    "refModule" => "FACT",
                    "nomModule" => "FACTURE",
                    "refAction" => "CRT",
                    "user" => $user,
                    "agence" => $agence,
                    "nameAgence" => $nameFile,
                    "description" => "Création Facture N° : ".$facture["num_fact"],
                ]) ;
        
                // FIN SAUVEGARDE HISTORIQUE
        
                $histoPaiement = new FactHistoPaiement() ;
                /*
                    Statut : 
                        - Payee
                        - En_cours
                */
                $statutPaiement = "" ; 
                switch ($type->getReference()) {
                    case 'DF':
                        $statutPaiement = "Payee" ;
                        break;
                    case 'PR':
                        $statutPaiement = "En_cours" ;
                        break;
                    default:
                        $statutPaiement = "En_attente" ;
                        break;
                }
        
                // $fact_libelle = empty($fact_libelle) ? null : $fact_libelle ;
                $fact_libelle = empty($facture["info1_paiement"]) ? null : $facture["info1_paiement"] ;
                $fact_num = empty($facture["info2_paiement"]) ? null : $facture["info2_paiement"] ;
                
                $histoPaiement->setLibelle($fact_libelle) ;
                $histoPaiement->setNumero($fact_num) ;
                $histoPaiement->setPaiement($paiement) ;
                $histoPaiement->setFacture($newFacture) ;
                $histoPaiement->setStatutPaiement($statutPaiement) ;
                
                $this->entityManager->persist($histoPaiement) ;
                $this->entityManager->flush() ;
        
                /*
                    client
                    designation
                    quantite
                    prix_unitaire
                    montant_tva
                    type_remise
                    val_remise
                    montant_total
                */

                // INSERTION DES DETAILS DE LA FACTURE 
                // PAR RAPPORT AU MODELE 
                // PORTANT DES REFERENCES SPECIFIQUES
                $modeleRef = $modele->getReference() ;
                if($modeleRef == "PROD" || $modeleRef == "PSTD") // Produit ou Prestation Standard
                {
                    foreach ($facture["details"] as $detail) {
                        $factDetail = new FactDetails() ;

                        $remiseVal = 0 ;
                        
                        $totalHt = $detail["quantite"] * $detail["prix_unitaire"] ;

                        if(empty($detail["type_remise"]))
                        {
                            if($tabKeyRef[$detail["type_remise"]] == 100)
                            {
                                $remiseVal = ($totalHt * $detail["val_remise"]) / 100 ; 
                            }
                            else
                            {
                                $remiseVal = $detail["val_remise"] ;
                            }
                        }
                        
                        $factDetail->setFacture($newFacture) ; 
                        $factDetail->setRemiseType($typeRemise) ;
                        $factDetail->setRemiseVal($remiseVal) ;
                        $factDetail->setDesignation($detail["designation"]) ;
                        $factDetail->setQuantite($detail["quantite"]) ;
                        $factDetail->setPrix($detail["prix_unitaire"]) ;
                        $factDetail->setTvaVal($detail["montant_tva"]) ;
                        $factDetail->setStatut(True) ;
        
                        $this->entityManager->persist($factDetail) ;
                        $this->entityManager->flush() ; 
                    }
        
                }

                // INSERTION DE FINANCE : CREDIT et ACOMPTE (reference CR et AC)
                if(!is_null($paiement))
                {
                    if($paiement->getReference() == "CR" || $paiement->getReference() == "AC")
                    {
                        $lastRecordFinance = $this->entityManager->getRepository(CrdFinance::class)->findOneBy([], ['id' => 'DESC']);
                        $numFinance = !is_null($lastRecordFinance) ? ($lastRecordFinance->getId()+1) : 1 ;
                        $numFinance = str_pad($numFinance, 5, "0", STR_PAD_LEFT);
                        $refFncStatut = "ECR" ; 
                        
                        $crdStatut = $this->entityManager->getRepository(CrdStatut::class)->findOneBy([
                                "reference" => $refFncStatut
                            ]) ;
        
                        $finance = new CrdFinance() ;
        
                        $finance->setAgence($agence) ;
                        $finance->setFacture($newFacture) ;
                        $finance->setPaiement($paiement) ;
                        $finance->setNumFnc($numFinance) ;
                        $finance->setStatut($crdStatut) ; 
                        $finance->setCreatedAt(new \DateTimeImmutable) ; 
                        $finance->setUpdatedAt(new \DateTimeImmutable) ; 
        
                        $this->entityManager->persist($finance) ;
                        $this->entityManager->flush() ; 
                    }
                }
                
                if(!is_null($paiement))
                {
                    if($paiement->getReference() == "AC")
                    {
                        $filename = "files/systeme/credit/acompte(agence)/".$nameFile ;
                        if(file_exists($filename))
                        unlink($filename);
                    }
                    else if($paiement->getReference() == "CR")
                    {
                        $filename = "files/systeme/credit/credit(agence)/".$nameFile ;
                        if(file_exists($filename))
                        unlink($filename);
                    }
                }
        
                // gestion des fichiers 
        
                $filename = "files/systeme/facture/facture(agence)/".$nameFile ;
                if(file_exists($filename))
                    unlink($filename);
            }
                
        }

        return new JsonResponse([
            "message" => "Importation effectuée",
            "type" => "green"
        ]) ;
    }
}
