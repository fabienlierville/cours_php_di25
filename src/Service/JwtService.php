<?php
namespace src\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService{
    public static String $secretKey = "cesi";
    public static function createToken(array $datas){
        $issuedAt = new \DateTimeImmutable();
        $expireAt = $issuedAt->modify("+3 minutes")->getTimestamp();
        $serverName = "cesi.local";
        $data = [
            'iat' => $issuedAt->getTimestamp(), //Date de génération du token
            'iss' => $serverName,
            'nbf' => $issuedAt->getTimestamp(),//Inutilisable avant le ....
            'exp' => $expireAt, //Date expiration
            'datas' => $datas
        ];
        $jwt = JWT::encode($data, self::$secretKey,'HS512');
        return $jwt;
    }


    public static function checkToken()
    {
        if (! preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            $result = [
                "success" => false,
                "body" => "Token non trouvé dans la requête"
            ];
            return $result;
        }

        $jwt = $matches[1];
        if (! $jwt) {
            $result = [
                "success" => false,
                "body" => "Aucun jeton n'a pu être extrait de l'en-tête d'autorisation."
            ];
            return $result;
        }

        try{
            //ça remonte une exception dès qu'il trouve une erreur on on veut catch l'erreur pour la donner en JSON
            $token = JWT::decode($jwt, new Key(self::$secretKey, 'HS512'));
        }catch (\Exception$e){
            $result = [
                "success" => false,
                "body" => "Les données du jeton ne sont pas compatibles : {$e->getMessage()}"
            ];
            return $result;
        }

        $now = new \DateTimeImmutable();
        $serverName = "cesi.local";

        if ($token->iss !== $serverName ||
            $token->nbf > $now->getTimestamp() ||
            $token->exp < $now->getTimestamp())
        {
            $result = [
                "success" => false,
                "body" => "Les données du jeton ne sont pas compatibles"
            ];
            return $result;
        }

        $result = [
            "success" => true,
            "body" => "Token OK",
            "data" => $token->datas //On récupère le champs datas du payload du JWT pour pouvoir par exemple comparer les roles avec ceux attendus
        ];
        return $result;


    }
}