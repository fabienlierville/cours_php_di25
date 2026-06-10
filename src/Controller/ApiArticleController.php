<?php
namespace src\Controller;

use src\Model\Article;
//use src\Service\JwtService;

class ApiArticleController{

    public function __construct(){
        header("Content-type: application/json; charset=utf-8");
    }

    public function getAll(){
        if($_SERVER['REQUEST_METHOD'] != 'GET'){
            header("HTTP/1.1 405 Method Not Allowed");
            return json_encode([
                'success' => false,
                'message' => 'Method Not Allowed'
            ]);
        }

        /*
        $jwtresult = JwtService::checkToken();
        if($jwtresult["success"] != true){
            return json_encode($jwtresult);
        }
        */

        $articles = Article::SqlGetAll();
        return json_encode($articles);

    }

    public function add(){
        if($_SERVER['REQUEST_METHOD'] != 'POST'){
            header("HTTP/1.1 405 Method Not Allowed");
            return json_encode([
                'success' => false,
                'message' => 'Method Not Allowed'
            ]);
        }

        //Récupération des données du Body
        $data = json_decode(file_get_contents("php://input"));

        if(empty($data->Titre) || empty($data->Auteur) || empty($data->Description)){
            header("HTTP/1.1 400 Bad Request");
            return json_encode([
                'success' => false,
                'message' => 'Il manque des données obligatoires'
            ]);
        }

        // Traitement image
        $sqlRepository = null;
        $nomImage = null;
        if(isset($data->Image)){
            $nomImage = uniqid().'.jpg';
            $dateNow = new \DateTime();
            $sqlRepository = $dateNow->format('Y/m');
            $repository = "{$_SERVER['DOCUMENT_ROOT']}/uploads/images/{$sqlRepository}";
            if(!is_dir($repository)){
                mkdir($repository,0777, true);
            }
            $file = fopen("{$repository}/{$nomImage}", "wb");
            fwrite($file, base64_decode($data->Image));
            fclose($file);
        }
        // Création de l'article + insert en BDD
        $article = new Article();
        $article->setTitre($data->Titre)
            ->setAuteur($data->Auteur)
            ->setDescription($data->Description)
            ->setImageRepository($sqlRepository)
            ->setImageFileName($nomImage)
            ->setDatePublication(new \DateTime($data->DatePublication));
        $id = Article::SqlAdd($article);
        return json_encode([
            'success' => true,
            'id' => $id,
            'message' => 'Article ajouté avec succès'
        ]);
    }

    public function delete($id){
        if($_SERVER['REQUEST_METHOD'] != 'DELETE'){
            header("HTTP/1.1 405 Method Not Allowed");
            return json_encode([
                'success' => false,
                'message' => 'Method Not Allowed'
            ]);
        }

        if(empty($id)){
            header("HTTP/1.1 400 Bad Request");
            return json_encode([
                'success' => false,
                'message' => 'ID invalide ou manquant'
            ]);
        }

        //Récupération Article pour supprimer l'image si image il y'a
        $article = Article::SqlGetById($id);
        if(!$article){
            header("HTTP/1.1 404 Not Found");
            return json_encode([
                'success' => false,
                'message' => "Article {$id} introuvable"
            ]);
        }
        $imageRepo = $article->getImageRepository();
        $imageName = $article->getImageFileName();

        if($imageRepo && $imageName){
            $fullPath = "{$_SERVER['DOCUMENT_ROOT']}/uploads/images/{$imageRepo}/{$imageName}";
            if(file_exists($fullPath)){
                unlink($fullPath); // Suppression du fichier
            }
        }

        // Suppression
        Article::SqlDelete($id);
        return json_encode([
            'success' => true,
            'message' => "Suppression article {$id}"
        ]);
    }
    public function update($id){
        if($_SERVER['REQUEST_METHOD'] != 'PUT'){
            header("HTTP/1.1 405 Method Not Allowed");
            return json_encode([
                'success' => false,
                'message' => 'Method Not Allowed'
            ]);
        }

        $data = json_decode(file_get_contents("php://input"));

        // Vérification des données obligatoires
        if(empty($id) || empty($data->Titre) || empty($data->Auteur) || empty($data->Description)){
            header("HTTP/1.1 400 Bad Request");
            return json_encode([
                'success' => false,
                'message' => 'Données manquantes ou ID invalide'
            ]);
        }

        // Récupération de l'article existant
        $article = Article::SqlGetById($id);

        if(!$article){
            header("HTTP/1.1 404 Not Found");
            return json_encode([
                'success' => false,
                'message' => "Article {$id} introuvable"
            ]);
        }

        // Gestion de l'Image
        // Si une NOUVELLE image est envoyée, on supprime l'ancienne et on crée la nouvelle.
        // Sinon, on conserve l'image actuelle.
        $sqlRepository = null;
        $nomImage = null;
        if(isset($data->Image) && !empty($data->Image)){

            // Suppression de l'ancienne image physique
            $oldRepo = $article->getImageRepository();
            $oldName = $article->getImageFileName();

            if($oldRepo && $oldName){
                $oldPath = "{$_SERVER['DOCUMENT_ROOT']}/uploads/images/{$oldRepo}/{$oldName}";
                if(file_exists($oldPath)){
                    unlink($oldPath);
                }
            }

            // Création de la nouvelle image
            $nomImage = uniqid().'.jpg';
            $dateNow = new \DateTime();
            $sqlRepository = $dateNow->format('Y/m');
            $repository = "{$_SERVER['DOCUMENT_ROOT']}/uploads/images/{$sqlRepository}";

            if(!is_dir($repository)){
                mkdir($repository, 0777, true);
            }

            $file = fopen("{$repository}/{$nomImage}", "wb");
            fwrite($file, base64_decode($data->Image));
            fclose($file);

        }

        // Mise à jour des autres propriétés de l'objet
        $article->setTitre($data->Titre)
            ->setAuteur($data->Auteur)
            ->setDatePublication(new \DateTime($data->DatePublication))
            ->setImageRepository($sqlRepository)
            ->setImageFileName($nomImage)
            ->setDescription($data->Description);

        // Mise à jour Article
        Article::SqlUpdate($article);

        return json_encode([
            'success' => true,
            'message' => "Article {$id} mis à jour avec succès"
        ]);
    }

    public function search (){

        if($_SERVER["REQUEST_METHOD"] != "POST"){
            header("HTTP/1.1 405 Method Not Allowed");
            return json_encode([
                "code" => 1,
                "Message" => "Post Attendu"
            ]);
        }

        //Récupération du body en String
        $data = file_get_contents("php://input");
        //Conversion du string en JSON
        $json = json_decode($data);

        if(!isset($json->keyword)){
            header("HTTP/1.1 403 Forbiden");
            return json_encode([
                "code" => 1,
                "Message" => "GET keyword manquant"
            ]);
        }
        $articles = Article::SqlSearch($json->keyword);
        return json_encode($articles);

    }

}