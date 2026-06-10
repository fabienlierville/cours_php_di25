<?php
namespace src\Model;

class User {
    private ?int  $Id = null;
    private String $Email;
    private String $Password;
    private Array $Roles;
    private String $NomPrenom;

    public function getId(): ?int
    {
        return $this->Id;
    }

    public function setId(?int $Id): User
    {
        $this->Id = $Id;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): User
    {
        $this->Email = $Email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->Password;
    }

    public function setPassword(string $Password): User
    {
        $this->Password = $Password;
        return $this;
    }

    public function getRoles(): Array
    {
        return $this->Roles;
    }

    public function setRoles(Array $Roles): User
    {
        $this->Roles = $Roles;
        return $this;
    }

    public function getNomPrenom(): string
    {
        return $this->NomPrenom;
    }

    public function setNomPrenom(string $NomPrenom): User
    {
        $this->NomPrenom = $NomPrenom;
        return $this;
    }


    public static function SqlGetByMail(string $mail): ?User
    {
        $requete = BDD::getInstance()->prepare("SELECT * FROM users WHERE Email=:mail");
        $requete->execute([
            "mail" => $mail
        ]);
        $datas = $requete->fetch(\PDO::FETCH_ASSOC);
        if($datas != false){
            $user = new User();
            $user->setId($datas["Id"])
                ->setEmail($datas["Email"])
                ->setPassword($datas["Password"])
                ->setNomPrenom($datas["NomPrenom"])
                ->setRoles(json_decode($datas["Roles"]));
            return $user;
        }
        return null;
    }


    public static function SqlAdd(User $user) :int
    {
        $requete = BDD::getInstance()->prepare("INSERT INTO users (Email, Password, NomPrenom, Roles) VALUES(:Email, :Password, :NomPrenom, :Roles)");

        $requete->execute([
            "Email" => $user->getEmail(),
            "Password" => $user->getPassword(),
            "NomPrenom" => $user->getNomPrenom(),
            "Roles" => json_encode($user->getRoles())
        ]);

        return BDD::getInstance()->lastInsertId();
    }


}