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

        $dataDateLvrs = [] ;

        $sqlDateLvr = "SELECT * FROM `lvr_date_livraison` WHERE `statut` = 1 "  ;

        // $stmt = $this->connection->query();

        echo json_encode($dataLieuLvrs) ;

        return new Response("") ;
        
    }

    #[Route('/api/commande/valider', name: 'app_api_commande_valider')]
    public function apiValiderCommande(Request $request)
    {
        $itemPanier = (array)$request->request->get("itemPanier") ;

        dd($itemPanier) ;

        $typeLvr = $request->request->get("typeLvr") ;
        $nom = $request->request->get("nom") ;
        $adresse = $request->request->get("adresse") ;
        $telehphone = $request->request->get("telehphone") ;
        $lieuLvr = $request->request->get("lieuLvr") ;
        $message = $typeLvr == "DRV" ? "Point de récupération" : "Zone de livraison" ;

        $result = $this->verificationElement([
            $typeLvr,
            $nom,
            $adresse,
            $telehphone,
            $lieuLvr,
        ],[
            "Type de livraison",
            "Nom",
            "Adresse",
            "Téléphone",
            $message
        ]) ;

        if(!$result["allow"])
        {
            echo json_encode($result) ;
            return new Response("") ;
        }

        $sqlLastCommande = "SELECT * FROM `cmd_commande` WHERE 1 ORDER BY `id` LIMIT 1 " ;

        $stmt = $this->connection->prepare($sqlLastCommande);

        $stmt->execute();

        // Récupération du dernier enregistrement
        $lastRecord = $stmt->fetch(\PDO::FETCH_ASSOC);

        $numCommande = !is_null($lastRecord) ? ($lastRecord["id"]+1) : 1 ;
        $numCommande = str_pad($numCommande, 4, "0", STR_PAD_LEFT)."/".date('y');

        // Requête SQL d'insertion avec des marqueurs de position (?)
        $sqlCmdCommande = "INSERT INTO `cmd_commande`(`id`, `client_id`, `cmd_statut_id`, `date`, `lieu`, `montant`, `statut`, `created_at`, `updated_at`, `num_commande`) VALUES (?,?,?,?,?,?,?,?,?,?)";

        $stmt = $this->connection->prepare($sqlCmdCommande);
        $stmt->execute([
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
        ]);

        $commandeId = $this->connection->lastInsertId();

        $sqlCmdDetail = "INSERT INTO `cmd_details` (`id`, `commande_id`, `fournisseur_id`, `prix_id`, `produit_id`, `cmd_statut_id`, `designation`, `montant`, `quantite`, `statut`) VALUES (?,?,?,?,?,?,?,?,?,?) " ;
        
        for ($i=0; $i < count($itemPanier); $i++) { 
            $stmt = $this->connection->prepare($sqlCmdDetail);
            $stmt->execute([
                NULL, 
                $commandeId, 
                $itemPanier->fournisseur,
                NULL,
                $itemPanier->id,
                4,
                $itemPanier->nom,
                $itemPanier->montant,
                $itemPanier->quantite,
                true,
            ]);

            $detailId = $this->connection->lastInsertId();

            $sqlLivraison = "INSERT INTO `lvr_livraison`(`id`, `cmd_detail_id`, `lvr_date_id`, `lvr_zone_id`, `statut`, `cmd_statut_id`, `commande_id`, `client_id`, `point_recup_id`, `livreur_id`, `nom`, `adresse`, `telephone`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?) " ;
            
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
            

            $stmt = $this->connection->prepare($sqlLivraison);
            $stmt->execute([
                NULL, 
                $detailId,
                1, 
                NULL,
                $lvrZone,
                1,
                4,
                $commandeId,

            ]);
        }

        echo json_encode([
            "type" => "red",
            "message" => "Test d'enregistrement"
        ]) ;

        return new Response("") ;

    }
}
