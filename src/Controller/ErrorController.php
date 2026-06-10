<?php
namespace src\Controller;


class ErrorController extends AbstractController
{
    public function show(\Exception $e){
        return $this->twig->render('error.html.twig',[

            'message' => $e->getMessage()
        ]);
    }

}
