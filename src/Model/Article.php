<?php
namespace src\Model;
class Article{
    private ?int $Id = null;
    private ?string $Titre = null;
    private ?string $Auteur = null;
    private ?string $Description = null;
    private ?\DateTime $DatePublication = null;
    private ?string $ImageRepository = null;
    private ?string $ImageFileName = null;

    public function getId(): ?int
    {
        return $this->Id;
    }

    public function setId(?int $Id): Article
    {
        $this->Id = $Id;
        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->Titre;
    }

    public function setTitre(?string $Titre): Article
    {
        $this->Titre = $Titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): Article
    {
        $this->Description = $Description;
        return $this;
    }

    public function getAuteur(): ?string
    {
        return $this->Auteur;
    }

    public function setAuteur(?string $Auteur): Article
    {
        $this->Auteur = $Auteur;
        return $this;
    }

    public function getDatePublication(): ?\DateTime
    {
        return $this->DatePublication;
    }

    public function setDatePublication(?\DateTime $DatePublication): Article
    {
        $this->DatePublication = $DatePublication;
        return $this;
    }

    public function getImageRepository(): ?string
    {
        return $this->ImageRepository;
    }

    public function setImageRepository(?string $ImageRepository): Article
    {
        $this->ImageRepository = $ImageRepository;
        return $this;
    }

    public function getImageFileName(): ?string
    {
        return $this->ImageFileName;
    }

    public function setImageFileName(?string $ImageFileName): Article
    {
        $this->ImageFileName = $ImageFileName;
        return $this;
    }


    public static function SqlGetAll(){
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare('SELECT * FROM articles ORDER BY Id DESC');
        $requete->execute();
        $articlesSQL = $requete->fetchAll(\PDO::FETCH_ASSOC);
        $articlesObjet=[];
        foreach ($articlesSQL as $articleSQL){
            $article = new Article();
            $date = new \DateTime($articleSQL["DatePublication"]);
            $article->setTitre($articleSQL["Titre"])
                ->setId($articleSQL["Id"])
                ->setDescription($articleSQL["Description"])
                ->setDatePublication($date)
                ->setAuteur($articleSQL["Auteur"]);
            $articlesObjet[] = $article;
        }
        return $articlesObjet;
    }

        public static function SqlAdd(Article $article) : int
        {
        try {
            $requete = BDD::getInstance()->prepare("INSERT INTO articles (Titre,Description,DatePublication,Auteur, ImageRepository, ImageFileName) VALUES (:Titre,:Description,:DatePublication,:Auteur, :ImageRepository, :ImageFileName)");
            $requete->bindValue(':Titre',$article->getTitre());
            $requete->bindValue(':Description',$article->getDescription());
            $requete->bindValue(':DatePublication',$article->getDatePublication()?->format('Y-m-d'));
            $requete->bindValue(':Auteur',$article->getAuteur());
            $requete->bindValue(':ImageRepository',$article->getImageRepository());
            $requete->bindValue(':ImageFileName',$article->getImageFileName());
            $requete->execute();
            return BDD::getInstance()->lastInsertId();
        }catch (\PDOException $e) {
            return $e->getMessage();
        }
    }


    public static function SqlGetById($id) : ?Article{
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare('SELECT * FROM articles WHERE Id=:Id');
        $requete->execute([
            "Id"=> $id
        ]);
        $articleSQL = $requete->fetch(\PDO::FETCH_ASSOC);
        if($articleSQL != false){
            $article = new Article();
            $date = new \DateTime($articleSQL["DatePublication"]);
            $article->setTitre($articleSQL["Titre"])
                ->setId($articleSQL["Id"])
                ->setDescription($articleSQL["Description"])
                ->setDatePublication($date)
                ->setAuteur($articleSQL["Auteur"])
                ->setImageRepository($articleSQL["ImageRepository"])
                ->setId($articleSQL["Id"])
                ->setImageFileName($articleSQL["ImageFileName"]);

            return $article;
        }
        return null;

    }

    public static function SqlUpdate(Article $article) : ?Article
    {
        $bdd = BDD::getInstance();
        $req = $bdd->prepare('UPDATE articles set Titre=:Titre, Description=:Description, DatePublication=:DatePublication, Auteur=:Auteur, ImageRepository=:ImageRepository, ImageFileName=:ImageFileName  where Id=:Id');

        $req->bindValue(':Id', $article->getId());
        $req->bindValue(':Titre', $article->getTitre());
        $req->bindValue(':Description', $article->getDescription());
        $req->bindValue(':DatePublication', $article->getDatePublication()->format('Y-m-d'));
        $req->bindValue(':Auteur', $article->getAuteur());
        $req->bindValue(':ImageRepository', $article->getImageRepository());
        $req->bindValue(':ImageFileName', $article->getImageFileName());
        $req->execute();

        return $article;
    }

    public static function SqlDelete(int $id){
        $requete = BDD::getInstance()->prepare("DELETE FROM articles WHERE Id=:Id");
        $execute = $requete->execute([
            'Id' => $id
        ]);
    }


    public static function SqlGetLast(int $nb)
    {
        $requete = BDD::getInstance()->prepare('SELECT * FROM articles ORDER BY Id DESC LIMIT :limit');
        $requete->bindValue("limit", $nb, \PDO::PARAM_INT);
        $requete->execute();

        $articlesSql = $requete->fetchAll(\PDO::FETCH_ASSOC);
        $articlesObjet = [];
        foreach ($articlesSql as $articleSql){
            $article = new Article();
            $article->setTitre($articleSql["Titre"])
                ->setDescription($articleSql["Description"])
                ->setDatePublication(new \DateTime($articleSql["DatePublication"]))
                ->setAuteur($articleSql["Auteur"])
                ->setImageRepository($articleSql["ImageRepository"])
                ->setImageFileName($articleSql["ImageFileName"]);
            $articlesObjet[] = $article;
        }
        return $articlesObjet;


    }


}
