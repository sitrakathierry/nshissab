<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api/insert', name: 'app_api_insert')]
    public function apiInsertRecord()
    {
        header("Access-Control-Allow-Origin: *");

        $servername = "51.195.220.197"; // ou votre adresse IP du serveur de base de données
        $username = "debian"; // votre nom d'utilisateur de base de données
        $password = "yQYRQqe9zFuB"; // votre mot de passe de base de données
        $dbname = "bazarbdd"; // le nom de votre base de données

        try {
            $conn = new \PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            // Définir le mode d'erreur de PDO sur Exception
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Préparer la requête d'insertion
            $stmt = $conn->prepare("INSERT INTO compte (nom, prenom) VALUES (:valeur1, :valeur2)");
            
            // Remplacez les valeurs ci-dessous par vos propres données
            $valeur1 = "mamisoa";
            $valeur2 = "olivia";
            
            // Liaison des valeurs et exécution de la requête
            $stmt->bindParam(':valeur1', $valeur1);
            $stmt->bindParam(':valeur2', $valeur2);

            $stmt->execute();

            echo "Données insérées avec succès";

        } catch(\PDOException $e) {
            echo "La connexion a échoué : " . $e->getMessage();
        }
    }
}
