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
    
            $dataProduits[] = [
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

        $dataDateLvrs = $this->getAllData("SELECT * FROM `lvr_date_livraison` WHERE `statut` = 1 ")  ;

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
        $typeLvr = $request->request->get("typeLvr") ;
        $nom = $request->request->get("nom") ;
        $adresse = $request->request->get("adresse") ;
        $telephone = $request->request->get("telephone") ;
        $lieuLvr = $request->request->get("lieuLvr") ;
        $dateLvr = $request->request->get("dateLvr") ;
        $message = $typeLvr == "DRV" ? "Point de récupération" : "Zone de livraison" ;

        $result = $this->verificationElement([
            $typeLvr,
            $nom,
            $adresse,
            $telephone,
            $lieuLvr,
            $dateLvr
        ],[
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

        $lastRecord = $this->getData("SELECT * FROM `cmd_commande` WHERE 1 ORDER BY `id` DESC LIMIT 1 ") ;

        $numCommande = !is_null($lastRecord) ? (intval($lastRecord["id"]) + 1) : 1 ;
        $numCommande = str_pad($numCommande, 4, "0", STR_PAD_LEFT)."/".date('y');

        // Requête SQL d'insertion avec des marqueurs de position (?)
        $this->setData("INSERT INTO `cmd_commande`(`id`, `client_id`, `cmd_statut_id`, `date`, `lieu`, `montant`, `statut`, `created_at`, `updated_at`, `num_commande`) VALUES (?,?,?,?,?,?,?,?,?,?)",[
            NULL, 
            NULL,
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
}
