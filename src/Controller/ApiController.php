<?php

namespace App\Controller;

use App\Service\AppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private $connection ;

    public function __construct()
    {
        header("Access-Control-Allow-Origin: *");

        $servername = "51.195.220.197"; // ou votre adresse IP du serveur de base de données
        $username = "debian"; // votre nom d'utilisateur de base de données
        $password = "yQYRQqe9zFuB"; // votre mot de passe de base de données
        $dbname = "bazarbdd"; // le nom de votre base de données

        try {
            $this->connection = new \PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        } catch(\PDOException $e) {
            echo "La connexion a échoué : " . $e->getMessage();
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

    public function getAllData($sql,$params = [])
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function getData($sql,$params = [])
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch() ;
        return $result;
    }

    public function setData($sql,$params = [])
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return true;
    }

    #[Route('/api/insert', name: 'app_api_insert')]
    public function apiInsertRecord()
    {
        try {
            // Définir le mode d'erreur de PDO sur Exception
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Préparer la requête d'insertion
            $stmt = $this->connection->prepare("INSERT INTO compte (nom, prenom) VALUES (:valeur1, :valeur2)");
            
            // Remplacez les valeurs ci-dessous par vos propres données
            $valeur1 = "ravaka";
            $valeur2 = "niaina";
            
            // Liaison des valeurs et exécution de la requête
            $stmt->bindParam(':valeur1', $valeur1);
            $stmt->bindParam(':valeur2', $valeur2);

            $stmt->execute();

        } catch(\PDOException $e) {
            echo "La connexion a échoué : " . $e->getMessage();
        }

        return new Response("") ;
    }

    #[Route('/api/get/produit', name: 'app_api_prodtui_get')]
    public function apiGetProduitRecord(Request $request)
    {
        $idProduit = $request->request->get("idPrd") ;
        $dataProduits = [] ;

        if(!isset($idProduit))
        {
            $produits = $this->getAllData("SELECT p.id, p.nom, p.profil, p.description, p.prix, c.nom as categorie, c.id as id_cat  FROM `prd_produit` p JOIN prd_categorie c ON p.categorie_id = c.id WHERE p.statut = 1 AND c.statut = 1") ;

            foreach ($produits as $produit) {
                $dataProduits[$produit['id_cat']."#".$produit['categorie']][] = [
                    "id" => $produit['id'],
                    "nom" => $produit['nom'],
                    "profil" => $produit['profil'],
                    "description" => $produit['description'],
                    "prix" => $produit['prix'],
                ] ;
            }
        }
        else
        {
            $produit = $this->getData("SELECT p.id, p.nom, p.profil, p.description, p.prix, c.nom as categorie  FROM `prd_produit` p JOIN prd_categorie c ON p.categorie_id = c.id WHERE p.id = ? ",[
                $idProduit
            ]) ; 
    
            $dataProduits = [
                "id" => $produit['id'],
                "nom" => $produit['nom'],
                "profil" => $produit['profil'],
                "description" => $produit['description'],
                "prix" => $produit['prix'],
                "categorie" => $produit['categorie'],
            ] ;
        }
        
        echo json_encode($dataProduits) ;

        return new Response("") ;
    }

    #[Route('/api/get/min/produit', name: 'app_api_produit_min_get')]
    public function apiGetMinProduit(Request $request)
    {
        $idPrd = $request->request->get('idPrd') ;
        $quantite = $request->request->get('quantite') ;

        $produit = $this->getData("SELECT p.id, p.nom, p.prix, c.nom as categorie, p.user_id as fournisseur  FROM `prd_produit` p JOIN prd_categorie c ON p.categorie_id = c.id WHERE p.id = ? ", [
            $idPrd
        ]) ;

        $dataProduit = [
            "id" => $produit['id'],
            "nom" => $produit['nom'],
            "prix" => $produit['prix'],
            "quantite" => $quantite,
            "categorie" => $produit['categorie'],
            "fournisseur" => $produit['fournisseur'],
        ] ;

        echo json_encode($dataProduit) ;

        return new Response("") ;
    }

    // 
    #[Route('/api/lieu/livraison/get', name: 'app_api_lieu_livraison_get')]
    public function apiGetLieuLivraison(Request $request)
    {
        $typeLvr = $request->request->get("typeLvr") ;
        if($typeLvr == "DRV")
            $dataLieux = $this->getAllData("SELECT * FROM `lvr_point_recup` WHERE `statut` = 1 ") ;
        else
            $dataLieux = $this->getAllData("SELECT * FROM `lvr_zone` WHERE `statut` = 1 ") ;

        $dataLieuLvrs = [] ;

        if($typeLvr == "DRV")
        {
            foreach ($dataLieux as $dataLieu) {
                $dataLieuLvrs[] = [
                    "id" => $dataLieu['id'],
                    "lieu" => $dataLieu['lieu'],
                ] ;
            }
        }
        else
        {
            foreach ($dataLieux as $dataLieu)  {
                $dataLieuLvrs[] = [
                    "id" => $dataLieu['id'],
                    "lieu" => $dataLieu['nom_zone']." | Prix : ".$dataLieu['prix']." €",
                ] ;
            }
        }

        $dataDateLvrs = $this->getAllData("SELECT id, DATE_FORMAT(date,'%d/%m/%Y') as date FROM `lvr_date_livraison` WHERE `statut` = 1 ORDER BY `date` ASC ")  ;

        echo json_encode([
            "lieuLvrs" => $dataLieuLvrs,
            "dateLvrs" => $dataDateLvrs
        ]) ;

        return new Response("") ;
        
    }

    #[Route('/api/commande/valider', name: 'app_api_commande_valider')]
    public function apiValiderCommande(Request $request)
    {
        $itemPanier = (array)$request->request->get("itemPanier") ;
        $itemPanier = isset($itemPanier) ? $itemPanier : [] ;
        $typeLvr = $request->request->get("typeLvr") ;
        $nom = $request->request->get("nom") ;
        $adresse = $request->request->get("adresse") ;
        $telephone = $request->request->get("telephone") ;
        $lieuLvr = $request->request->get("lieuLvr") ;
        $dateLvr = $request->request->get("dateLvr") ;
        $message = $typeLvr == "DRV" ? "Point de récupération" : "Zone de livraison" ;

        $result = $this->verificationElement([
            $itemPanier,
            $typeLvr,
            $nom,
            $adresse,
            $telephone,
            $lieuLvr,
            $dateLvr
        ],[
            "Commande",
            "Type de livraison",
            "Nom",
            "Adresse",
            "Téléphone",
            $message,
            "Date de livraison"
        ]) ;

        if(!$result["allow"])
        {
            echo json_encode($result) ;
            return new Response("") ;
        }

        $clientId = empty($request->request->get("userId")) ? NULL : $request->request->get("userId") ;

        $lastRecord = $this->getData("SELECT * FROM `cmd_commande` WHERE 1 ORDER BY `id` DESC LIMIT 1 ") ;

        $numCommande = !is_null($lastRecord) ? (intval($lastRecord["id"]) + 1) : 1 ;
        $numCommande = str_pad($numCommande, 4, "0", STR_PAD_LEFT)."/".date('y');

        // Requête SQL d'insertion avec des marqueurs de position (?)
        $this->setData("INSERT INTO `cmd_commande`(`id`, `client_id`, `cmd_statut_id`, `date`, `lieu`, `montant`, `statut`, `created_at`, `updated_at`, `num_commande`) VALUES (?,?,?,?,?,?,?,?,?,?)",[
            NULL, 
            $clientId,
            4,
            date("Y-m-d"),
            "",
            0,
            true,
            date("Y-m-d H:i:s"),
            date("Y-m-d H:i:s"),
            $numCommande,
        ]) ;

        $commandeId = $this->connection->lastInsertId();
        
        for ($i=0; $i < count($itemPanier); $i++) { 
            $element = $itemPanier[$i] ;
            $this->setData("INSERT INTO `cmd_details` (`id`, `commande_id`, `fournisseur_id`, `prix_id`, `produit_id`, `cmd_statut_id`, `designation`, `montant`, `quantite`, `statut`) VALUES (?,?,?,?,?,?,?,?,?,?) ",[
                NULL, 
                $commandeId, 
                $element["fournisseur"],
                NULL,
                $element["id"],
                4,
                $element["nom"],
                $element["prix"],
                $element["quantite"],
                true,
            ]) ;

            $detailId = $this->connection->lastInsertId();
            
            if($typeLvr == "DRV")
            {
                $lvrZone = NULL ;
                $lvrPoint = $lieuLvr ;
            }
            else
            {
                $lvrZone = $lieuLvr ;
                $lvrPoint = NULL ;
            }
            
            $this->setData("INSERT INTO `lvr_livraison`(
                `id`, 
                `cmd_detail_id`, 
                `lvr_date_id`, 
                `lvr_zone_id`, 
                `statut`, 
                `cmd_statut_id`, 
                `commande_id`, 
                `client_id`, 
                `point_recup_id`, 
                `livreur_id`, 
                `nom`, 
                `adresse`, 
                `telephone`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)", [
                NULL, 
                $detailId,
                $dateLvr,
                $lvrZone,
                1,
                4,
                $commandeId,
                NULL,
                $lvrPoint,
                NULL,
                $nom,
                $adresse,
                $telephone,
            ]);
        }

        echo json_encode([
            "type" => "green",
            "message" => "Succès"
        ]) ;

        return new Response("") ;

    }

    #[Route('/api/user/auth', name: 'app_api_authentificate_user')]
    public function apiAuthUser(Request $request)
    {
        $username = $request->request->get('username') ;
        $password = $request->request->get('password') ;
        $csrf_token = $request->request->get('csrf_token') ;

        $result = $this->verificationElement([
            $username,
            $password,
            $csrf_token,
        ],[
            "Nom d'utilisateur",
            "Mot de passe ",
            "Token"
        ]) ;

        if(!$result["allow"])
        {
            echo json_encode($result) ;
            return new Response("") ;
        }

        // verification du nom
        $user = $this->getData("SELECT * FROM `user` WHERE `username` = ? AND `statut` = 1",[
            $username
        ]) ;

        if($user != false)
        {
            $user = $this->getData("SELECT * FROM `user` WHERE `username` = ? AND `statut` = 1 ",[
                $username
            ]) ;

            if(password_verify($password,$user["password"]))
            {
                $response = [
                    "type" => "green",
                    "csrf_token" => $csrf_token,
                    "dataUser" => [
                        "id" => $user["id"],
                        "email" => $user["email"],
                        "fonction" => $user["fonction"],
                        "username" => $user["username"],
                        "nom" => $user["nom"],
                        "adresse" => $user["adresse"],
                        "telephone" => $user["telephone"],
                    ],
                ] ;

                echo json_encode($response) ;
            }
            else
            {
                echo json_encode([
                    "type" => "red",
                    "message" => "Mot de passe incorrect"
                ]) ;
            }

            
        }
        else
        {
            echo json_encode([
                "type" => "orange",
                "message" => "Le Nom d'utilisateur n'existe pas"
            ]) ;
        }

        return new Response("") ;
    }

    
    #[Route('/api/user/achat/get', name: 'app_api_user_achat_get')]
    public function apiGetUserAchat(Request $request)
    {
        $userId = $request->request->get('userId') ;

        $dataAchats = $this->getAllData("SELECT cs.nom as statut, DATE_FORMAT(cc.date,'%d/%m/%Y') as date, cc.montant, cc.num_commande as numero, cc.id FROM `cmd_commande` cc JOIN cmd_statut cs ON cs.id = cc.cmd_statut_id WHERE `client_id` = ? AND `statut` = 1 ", [
            $userId
        ]) ;

        echo json_encode($dataAchats) ;

        return new Response("") ;
    }


    #[Route('/api/user/inscription/valider', name: 'app_api_user_inscription_valider')]
    public function apiUserValiderInscription(Request $request)
    {
        $ins_civilite = $request->request->get("ins_prenom") ;
        $ins_prenom = $request->request->get("ins_prenom") ;
        $ins_nom = $request->request->get("ins_nom") ;
        $ins_email = $request->request->get("ins_email") ;
        $ins_adresse = $request->request->get("ins_adresse") ;
        $ins_telephone = $request->request->get("ins_telephone") ;
        $ins_username = $request->request->get("ins_username") ;
        $ins_pass = $request->request->get("ins_pass") ;
        $ins_confirm = $request->request->get("ins_confirm") ;

        $result = $this->verificationElement([
            $ins_prenom,
            $ins_nom,
            $ins_email,
            $ins_adresse,
            $ins_telephone,
            $ins_username,
            $ins_pass,
            $ins_confirm,
        ],[
            "Prénom",
            "Nom",
            "E-mail",
            "Adresse",
            "Téléphone",
            "Nom d'utilisateur",
            "Mot de passe",
            "Confirmation mot de passe",
        ]) ;

        if(!$result["allow"])
        {
            echo json_encode($result) ;
            return new Response("") ;
        }

        if($ins_pass != $ins_confirm)
        {
            echo json_encode("Mot de passe non identiques. Rééssayez") ;
            return new Response("") ;
        }

        $this->setData("INSERT INTO `user`
            (
                `id`, 
                `email`, 
                `roles`, 
                `password`, 
                `fonction`, 
                `statut`, 
                `disabled`, 
                `profil`, 
                `created_at`, 
                `updated_at`, 
                `username`, 
                `nom`, 
                `civilite`, 
                `prenom`, 
                `adresse`, 
                `telephone`
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",[
                NULL,
                $ins_email,
                json_encode(["CLT"]),
                password_hash($ins_pass, PASSWORD_DEFAULT),
                "CLT",
                true,
                null,
                null,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s'),
                $ins_username,
                $ins_nom,
                $ins_civilite,
                $ins_prenom,
                $ins_adresse,
                $ins_telephone,
            ]) ;

        echo json_encode([
            "type" => "green",
            "message" => "Succès"
        ]) ;

        return new Response("");
    }

    #[Route('/api/categorie/detail/get', name: 'app_api_detail_categorie_get')]
    public function apiGetCategorieDetail(Request $request)
    {
        $catId = $request->request->get("catId") ;

        $dataCatDetails = $this->getAllData("SELECT p.id, p.nom, p.profil, p.description, p.prix, c.nom as categorie, c.id as id_cat FROM `prd_produit` p JOIN prd_categorie c ON p.categorie_id = c.id WHERE p.categorie_id = ? AND c.statut = 1 and p.statut = 1 ",[
            $catId
        ]) ;

        echo json_encode($dataCatDetails) ; 

        return new Response("") ;
    }

    #[Route('/api/categorie/liste', name: 'app_api_categorie_liste')]
    public function apiListeCategorie()
    {
        $dataCategories = $this->getAllData("SELECT * FROM `prd_categorie` WHERE `statut` = 1") ;

        echo json_encode($dataCategories) ;

        return new Response("") ;
    }   

    #[Route('/api/livraison/get', name: 'app_api_livraison_get')]
    public function apiGetLivraison(Request $request)
    {
        $idLivreur = $request->request->get("idLivreur") ;

        $dataLivraisons = $this->getAllData("
        SELECT 
            cc.id as commandeId,
            cc.num_commande as numCommande, 
            cd.designation as prdNom, 
            DATE_FORMAT(ld.date, '%d/%m/%Y') as dateLvr,
            cs.nom as statut, 
            lz.id as idZone,
            lz.num_zone as numZone, 
            lz.nom_zone as nomZone

            FROM `lvr_livraison` lv

            JOIN cmd_commande cc ON cc.id = lv.commande_id
            JOIN cmd_details cd ON cd.id = lv.cmd_detail_id
            JOIN cmd_statut cs ON cs.id = lv.cmd_statut_id
            JOIN lvr_zone lz ON lz.id = lv.lvr_zone_id
            JOIN lvr_date_livraison ld ON ld.id = lv.lvr_date_id
        WHERE `livreur_id` = ? and lv.statut = 1
        ",[
            $idLivreur
        ]) ;

        $tabLivrs = [] ;

        foreach ($dataLivraisons as $itemLvr) {
            $tabLivrs[$itemLvr["dateLvr"]][$itemLvr["idZone"]."#".$itemLvr["numZone"]."#".$itemLvr["nomZone"]."#".$itemLvr["statut"]][$itemLvr["commandeId"]."#".$itemLvr["numCommande"]][] = [
                "designation" => $itemLvr["prdNom"]
            ] ;
        }

        echo json_encode($tabLivrs) ;

        return new Response("") ;

    }

    #[Route('/api/livraison/detail/get', name: 'app_api_livraison_detail_get')]
    public function apiGetDetailLivraison(Request $request)
    {
        $idCommande = $request->request->get("idCommande") ;

        $dataLivraisons = $this->getAllData("
            SELECT 
                cc.num_commande as numCommande, 
                cd.designation as prdNom, 
                cd.montant as prdMontant, 
                cd.quantite as prdQte, 
                DATE_FORMAT(ld.date, '%d/%m/%Y') as dateLvr,
                cs.nom as statut, 
                lz.id as idZone,
                lz.num_zone as numZone, 
                lz.nom_zone as nomZone
                FROM `lvr_livraison` lv
                JOIN cmd_commande cc ON cc.id = lv.commande_id
                JOIN cmd_details cd ON cd.id = lv.cmd_detail_id
                JOIN cmd_statut cs ON cs.id = lv.cmd_statut_id
                JOIN lvr_zone lz ON lz.id = lv.lvr_zone_id
                JOIN lvr_date_livraison ld ON ld.id = lv.lvr_date_id
            WHERE cc.id = ? and lv.statut = 1 ",
        [
            $idCommande
        ]) ;

        $tabLivrs = [] ;

        foreach ($dataLivraisons as $itemLvr) {
            $tabLivrs[$itemLvr["dateLvr"]."#".$itemLvr["numZone"]."#".$itemLvr["nomZone"]."#".$itemLvr["numCommande"]][] = [
                "designation" => $itemLvr["prdNom"],
                "prix" => $itemLvr["prdMontant"],
                "quantite" => $itemLvr["prdQte"],
            ] ;
        }

        echo json_encode($tabLivrs) ;

        return new Response("") ;
    }   
}
