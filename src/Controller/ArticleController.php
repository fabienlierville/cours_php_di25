<?php

namespace src\Controller;

use src\Model\Article;


class ArticleController extends AbstractController
{
    public function index(){
        $articles = Article::SqlGetLast(20);
        return $this->twig->render('Article/index.html.twig', [
            'articles' => $articles
        ]);
    }

}
