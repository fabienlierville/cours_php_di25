<?php
session_start();
require_once "../vendor/autoload.php";


function chargerClasse($classe)
{
    $ds = DIRECTORY_SEPARATOR;
    $dir = $_SERVER["DOCUMENT_ROOT"]."$ds.."; //remonte d’un cran par rapport à index.php
    // Remplacement des séparateur Namespace
    $className = str_replace('\\', $ds, $classe);

    $file = "{$dir}{$ds}{$className}.php";
    if (is_readable($file)) require_once $file;
}
// enregistrement de la fonction "chargerClasse" sur une instanciation de classe
spl_autoload_register('chargerClasse');



$URLS = explode("/",$_GET["url"]);
$controller = (isset($URLS[0])) ? $URLS[0] : '';
$action = (isset($URLS[1])) ? $URLS[1] : '';
$param = (isset($URLS[2])) ? $URLS[2] : '';


if($controller != ''){
    try {
        $class = "src\Controller\\".$controller."Controller";
        if (class_exists($class)) {
            $controller = new $class();
            if (method_exists($class, $action)) {
                echo $controller->$action($param);
            }else { throw new Exception("Action {$action} does not exist in {$class}"); }
        }else { throw new Exception("Controller {$controller} does not exist"); }
    }
    catch(Exception $e) {
        $controller = new \src\Controller\ErrorController();
        echo $controller->show($e);
    }
}else {
    //Route par défaut (/) ce controller sera fait plus tard dans le cadre de l’exercice Front Office
    $controller = new \src\Controller\ArticleController();
    echo $controller->index();
}

