<?php

namespace App\Controller;


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

    

}
