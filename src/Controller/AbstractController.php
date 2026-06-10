<?php

namespace src\Controller;

abstract class AbstractController

{
    protected $twig;

    public function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader($_SERVER['DOCUMENT_ROOT'].'/../src/View');
        $this->twig = new \Twig\Environment($loader, [
            'cache' => $_SERVER['DOCUMENT_ROOT'].'/../var/cache'
            ,'debug' => true
        ]);
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        $fileExist = new \Twig\TwigFunction('file_exists', function($fullFileName){
            return file_exists($fullFileName);
        });
        $this->twig->addFunction($fileExist);

        $this->twig->addGlobal('session', $_SESSION);
    }


}
