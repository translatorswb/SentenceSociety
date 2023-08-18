<?php

namespace App\Service;

class MessageHelper
{
    private $mailer;
    private $templating;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }
    public function sendMail($to, $from, $subject, $content, $cc = null, $bcc = null)
    {
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($content,'text/html');

        if($cc) $message->setCc($cc);
        if($bcc) $message->setBcc($bcc);

        return $this->mailer->send($message);
    }
}