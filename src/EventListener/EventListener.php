<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Service\MessageHelper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class EventListener
{
    /** @var ContainerInterface */
    private $container;

    /** @var MessageHelper */
    private $messageHelper;

    function __construct(ContainerInterface $container, MessageHelper $messageHelper)
    {
        $this->container = $container;
        $this->messageHelper = $messageHelper;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof User) {
            $em = $args->getObjectManager();
            $token = sha1(md5(time()) . uniqid() . $entity->getEmail());
            $entity->setToken($token);
            $entity->setTokenDate(date_create());
            $em->persist($entity);
            $em->flush();

            $confirmationLink = $_ENV['SITE_URL'].$this->container->get('router')->generate('user_email_verification', array('token' => $token), true);
            $validateEmailCopy = '<p>FBefore you start translating, please <a href='.$confirmationLink.'>click here</a> to validate your e-mail address.</p>';
            $content = '<p>Hello,</p>
                        <p>Thank you for taking part in this data validation project!</p>
                        <p>Your account is all set up. Your username is:<b> '.$entity->getName().'</b></p>
                        '.$validateEmailCopy.'
                        <p>You can reply to this email if you have any questions or suggestions for the TWB team.</p>
                        <p>Thank you for choosing to collaborate with us in this project!</p>';
            $subject = 'TWB Data Validation: Welcome!';
            $this->messageHelper->sendMail($entity->getEmail(),$_ENV['MAILER_ADDRESS'], $subject, $content);
        }
    }
}
