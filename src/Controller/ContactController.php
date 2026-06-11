<?php
namespace src\Controller;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController {

    public function index(){
        return $this->twig->render('contact/index.html.twig');
    }

    public function send(){
        //Conf Email
        $trspt = Transport::fromDsn("smtp://3dd84281bc8679:8a9180301c670a@sandbox.smtp.mailtrap.io:2525");
        $mailer = new Mailer($trspt);
        //Création Email
        $email = (new Email())
            ->from($_POST["mail"])
            ->to("admin@cesi.local")
            ->subject("Contact depuis le formulaire")
            ->html($this->twig->render('mail/contact.html.twig', [
                'nom' => $_POST["nom"],
                'message' => $_POST["message"],
            ]));
        $mailer->send($email);
        header("location:/");

    }

}