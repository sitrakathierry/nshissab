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
    private $appService ;

    public function __construct(AppService $appService)
    {
        header("Access-Control-Allow-Origin: *");

        $servername = "51.195.220.197"; // ou votre adresse IP du serveur de base de données
        $username = "debian"; // votre nom d'utilisateur de base de données
        $password = "yQYRQqe9zFuB"; // votre mot de passe de base de données
        $dbname = "bazarbdd"; // le nom de votre base de données
        $this->appService = $appService ;

        try {
            $this->connection = new \PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        } catch(\PDOException $e) {
            echo "La connexion a échoué : " . $e->getMessage();
        }
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
            $sql = "SELECT p.id, p.nom, p.profil, p.description, p.prix, c.nom as categorie, c.id as id_cat  FROM `prd_produit` p JOIN prd_categorie c ON p.categorie_id = c.id WHERE p.statut = 1 AND c.statut = 1" ;

            $stmt = $this->connection->query($sql);
    
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $dataProduits[$row['id_cat']."#".$row['categorie']][] = [
                    "id" => $row['id'],
                    "nom" => $row['nom'],
                    "profil" => $row['profil'],
                    "description" => $row['description'],
                    "prix" => $row['prix'],
                ] ;
            }
        }
        else
        {
            $sql = "SELECT p.id, p.nom, p.profil, p.description, p.prix, c.nom as categorie  FROM `prd_produit` p JOIN prd_categorie c ON p.categorie_id = c.id WHERE p.id = :val1 " ;

            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(':val1', $idProduit);

            $stmt->execute();
    
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $dataProduits[] = [
                    "id" => $row['id'],
                    "nom" => $row['nom'],
                    "profil" => $row['profil'],
                    "description" => $row['description'],
                    "prix" => $row['prix'],
                    "categorie" => $row['categorie'],
                ] ;
            }
        }
        
        echo json_encode($dataProduits) ;

        return new Response("") ;
    }

    #[Route('/api/get/min/produit', name: 'app_api_produit_min_get')]
    public function apiGetMinProduit(Request $request)
    {
        $idPrd = $request->request->get('idPrd') ;
        $quantite = $request->request->get('quantite') ;

        $sql = "SELECT p.id, p.nom, p.profil, p.description, p.prix, c.nom as categorie  FROM `prd_produit` p JOIN prd_categorie c ON p.categorie_id = c.id WHERE p.id = :val1 " ;

        $stmt = $this->connection->prepare($sql);

        $stmt->bindParam(':val1', $idPrd);

        $stmt->execute();
        $dataProduits = [] ;
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $dataProduits = [
                "id" => $row['id'],
                "nom" => $row['nom'],
                "prix" => $row['prix'],
                "quantite" => $quantite,
                "categorie" => $row['categorie'],
                "fournisseur" => $row['user_id'],
            ] ;
        }

        echo json_encode($dataProduits) ;

        return new Response("") ;
    }

    // 
    #[Route('/api/lieu/livraison/get', name: 'app_api_lieu_livraison_get')]
    public function apiGetLieuLivraison(Request $request)
    {
        $typeLvr = $request->request->get("typeLvr") ;
        if($typeLvr == "DRV")
            $sql = "SELECT * FROM `lvr_point_recup` WHERE `statut` = 1 " ;
        else
            $sql = "SELECT * FROM `lvr_zone` WHERE `statut` = 1 " ;

        $stmt = $this->connection->query($sql);

        $dataLieuLvrs = [] ;

        if($typeLvr == "DRV")
        {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $dataLieuLvrs[] = [
                    "id" => $row['id'],
                    "lieu" => $row['lieu'],
                ] ;
            }
        }
        else
        {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $dataLieuLvrs[] = [
                    "id" => $row['id'],
                    "lieu" => $row['nom_zone']." | Prix : ".$row['prix']." €",
                ] ;
            }
        }

        $dataDateLvrs = [] ;

        $sqlDateLvr = "SELECT * FROM `lvr_date_livraison` WHERE `statut` = 1"  ;

        $stmt = $this->connection->query($sql);



        echo json_encode($dataLieuLvrs) ;

        return new Response("") ;
        
    }

    #[Route('/api/commande/valider', name: 'app_api_commande_valider')]
    public function apiValiderCommande(Request $request)
    {
        $itemPanier = json_decode($request->request->get("itemPanier")) ;
        $typeLvr = $request->request->get("typeLvr") ;
        $nom = $request->request->get("nom") ;
        $adresse = $request->request->get("adresse") ;
        $telehphone = $request->request->get("telehphone") ;
        $lieuLvr = $request->request->get("lieuLvr") ;
        $message = $typeLvr == "DRV" ? "Point de récupération" : "Zone de livraison" ;

        $result = $this->appService->verificationElement([
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
