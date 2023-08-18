<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ProfileService;
use App\Service\TranslationSessionService;
use App\Service\MessageHelper;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfileController extends AbstractController
{
    /** @var UserRepository */
    private $userRepository;

    /** @var EntityManagerInterface */
    private $em;

    /** @var UserPasswordEncoderInterface */
    private $encoder;

    /** @var ProfileService */
    private $profileService;

    /** @var MessageHelper */
    private $messageHelper;

    /** @var TranslationSessionService */
    private $translationSessionService;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder,
        ProfileService $profileService,
        MessageHelper $messageHelper,
        TranslationSessionService $translationSessionService
    )
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->encoder = $encoder;
        $this->profileService = $profileService;
        $this->translationSessionService = $translationSessionService;
        $this->messageHelper = $messageHelper;
    }
    function login(Request $request, SessionInterface $session) {

        $content = $request->getContent();
        $parsedContent = json_decode($content);
        //
//        $name = $request->request->get('name');
        $name = $parsedContent->name;
//        $code = $request->request->get('code');
        $code = $parsedContent->code;
        /** @var $user User */
        $user = $this->userRepository->findOneBy(['name' => $name]);
        if ($user !== null) {

            $valid = $this->profileService->isPasswordValid($user, $code);

            if (! $valid) {
                return $this->json(['errors' => [['title' => 'password invalid', 'detail' => 'password for \'' . $name . '\' is not valid']]], Response::HTTP_FORBIDDEN);
            }

            /*

            // https://ourcodeworld.com/articles/read/459/how-to-authenticate-login-manually-an-user-in-a-controller-with-or-without-fosuserbundle-on-symfony-3
            // https://gist.github.com/azhararmar/0a952cf03b1cfbd2a5b059089b764491
            // Manually authenticate user in controller

            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            */

            $profileData = $this->profileService->getProfileData($user);

            $translationSession = $this->translationSessionService->getOrCreateTranslationSession();
            $translationSession->setUserId($user->getId());
            // Sorry, a bit brittle, this could be done in a better way
            $translationSession->setUserLevel($profileData['profile']['level']);

            //
            // perform administration of sentences, points etc. that have already been collected in the session
            //
            $this->translationSessionService->updateTranslationSessionAfterLogin($user, $translationSession);
            $this->translationSessionService->saveSession();

            return $this->json(['data' => $profileData]);
        } else {
            return $this->json(['errors' => [['title' => 'user not found', 'detail' => 'user \'' . $name . '\' not found']]], Response::HTTP_FORBIDDEN);
        }
    }

    function logout(SessionInterface $session) {
//        $this->translationSessionService->destroyTranslationSession();
        $session->invalidate();
        return $this->json(true);
    }

    function checkname(Request $request) {
        $content = $request->getContent();
        $parsedContent = json_decode($content);
        $name = $parsedContent->name;
        try {
            $available = $this->profileService->checkNameAvailable($name);
            return $this->json(['data' => ['available' => $available, 'name' => $name]]);
        } catch (\Error $e) {
            return $this->json(['errors' => [['title' => 'unexpected error']]], Response::HTTP_BAD_REQUEST);
        }
    }

    function register(Request $request, SessionInterface $session) {
        $content = $request->getContent();
        $parsedContent = json_decode($content);
        $name = $parsedContent->name;
        $personalName = $parsedContent->personalName;
        $code = $parsedContent->code;
        $email = $parsedContent->email;
        $country = $parsedContent->country;
        // check username, generate code and return
        try {
            $user = $this->profileService->createUserWithCode($name, $code, $email, $personalName, $country);
            // now log the user in
            $translationSession = $this->translationSessionService->getOrCreateTranslationSession();
            $translationSession->setUserId($user->getId());

            $profileData = $this->profileService->getProfileData($user);
            // side effect: update level
            $translationSession->setUserLevel($profileData['profile']['level']);

            //
            // perform administration of sentences, points etc. that have already been collected in the session
            //
            $this->translationSessionService->updateTranslationSessionAfterLogin($user, $translationSession);
            $this->translationSessionService->saveSession();

            return $this->json(['data' => $profileData]);
        } catch (UniqueConstraintViolationException $ucve) {
            return $this->json(['errors' => [['title' => 'name or email already taken']]], Response::HTTP_CONFLICT);
        } catch (\Error $e) {
            return $this->json(['errors' => [['title' => 'unexpected error']]], Response::HTTP_BAD_REQUEST);
        }

    }

    function addEmail(Request $request, SessionInterface $session) {
        $content = $request->getContent();
        $parsedContent = json_decode($content);
        $email = $parsedContent->email;
        $translationSession = $this->translationSessionService->getOrCreateTranslationSession();
        //var_dump($translationSession);
        $activeUserId = $translationSession->getUserId();
        if ($activeUserId) {
            /** @var $user User */
            $user = $this->userRepository->findOneBy(['id' => $activeUserId]);
            if ($user !== null) {

                $this->profileService->setUserEmailById($activeUserId, $email);
                return $this->json(['data' => true]);
            //return $this->json(['data' => array_merge(['name' => $user->getName() ], $this->getProfileData($user))]);
            } else {
                return $this->json(['errors' => [['title' => 'user not found']]]);
            }
        } else {
            return $this->json(['errors' => [['title' => 'userId not found in session']]], Response::HTTP_FORBIDDEN);
        }
    }

    function addCountry(Request $request, SessionInterface $session) {
        $content = $request->getContent();
        $parsedContent = json_decode($content);
        $country = $parsedContent->country;
        $translationSession = $this->translationSessionService->getOrCreateTranslationSession();
        //var_dump($translationSession);
        $activeUserId = $translationSession->getUserId();
        if ($activeUserId) {
            /** @var $user User */
            $user = $this->userRepository->findOneBy(['id' => $activeUserId]);
            if ($user !== null) {

                $this->profileService->setUserCountryById($activeUserId, $country);
                return $this->json(['data' => true]);
//                return $this->json(['data' => array_merge(['name' => $user->getName() ], $this->getProfileData($user))]);
            } else {
                return $this->json(['errors' => [['title' => 'user not found']]]);
            }
        } else {
            return $this->json(['errors' => [['title' => 'userId not found in session']]], Response::HTTP_FORBIDDEN);
        }
    }

    function profile(SessionInterface $session) {

        $translationSession = $this->translationSessionService->getOrCreateTranslationSession();
        $activeUserId = $translationSession->getUserId();
        //
        // does the session have a user?
        //
        if ($activeUserId) {
            /** @var $user User */
            $user = $this->userRepository->findOneBy(['id' => $activeUserId]);
            if ($user !== null) {
                $profileData = $this->profileService->getProfileData($user);
                // side effect: update level
                $translationSession->setUserLevel($profileData['profile']['level']);
                $this->translationSessionService->saveSession();
                return $this->json(['data' => $profileData]);
            } else {
                return $this->json(['errors' => [['title' => 'user not found']]]);
            }
        } else {
            return $this->json(['errors' => [['title' => 'userId not found in session']]], Response::HTTP_FORBIDDEN);
        }
    }

    function nameChange() {

    }

    function requestBonus(SessionInterface $session) {
        $translationSession = $this->translationSessionService->getOrCreateTranslationSession();
        $this->profileService->requestBonus($translationSession);
        return $this->json(true);
    }

    function emailVerification($token) {
        if($token){
            $user = $this->em->getRepository('App:User')->findOneBy(['token' => $token]);
            if($user){
                $user->setToken(null);
                $user->setTokenDate(null);
                $user->setVerifiedEmail(true);
                $this->em->persist($user);
                $this->em->flush();
                return $this->redirect('/');
            }else{
                return new Response(
                    '<div style="padding: 20px; background: repeating-linear-gradient(140deg, #F1F5FA, #F1F5FA 600px, #E4E7FB 600px, #E4E7FB 1200px, #C7CCF0 1200px, #C7CCF0 1800px); height: 100%; width: auto ">
                        <h1>Link Expired or User not found</h1>
                        <p>Return to the homepage <a href='.$_ENV['SITE_URL'].'>click here</a>.</p>
                    </div>'
                );
            }
        }else{
            return $this->json(['errors' => [['title' => 'Token not found.']]], Response::HTTP_FORBIDDEN);
        }
    }

    function forgotPassword(Request $request, SessionInterface $session){
        $content = $request->getContent();
        $parsedContent = json_decode($content);
        $email = $parsedContent->email;
        if($email){
            $user = $this->em->getRepository('App:User')->findOneBy(['email' => $email]);
            if($user){
                $token = sha1(md5(time()) . uniqid() . $user->getEmail());
                $user->setToken($token);
                $user->setTokenDate(date_create());
                $this->em->persist($user);
                $this->em->flush();

                $confirmationLink = $_ENV['SITE_URL'].$this->container->get('router')->generate('user_reset_password', array('token' => $token), true);
                $validateEmailCopy = '<p>To reset your password on the TWB Data Validation platform, please <a href='.$confirmationLink.'>click here</a>.</p>';
                $content = '<p>Dear <b>'.$user->getName().'</b>,</p>
                        '.$validateEmailCopy.'
                        <p>If you did not request a password reset, let us know or disregard this email.
                        You can reply to this email if you have any questions or suggestions for the TWB team.
                        </p>
                        <p>Thank you for choosing to collaborate with us in this project!</p>';
                $subject = 'TWB Data Validation: Reset your password';
                $this->messageHelper->sendMail($user->getEmail(),$_ENV['MAILER_ADDRESS'], $subject, $content);
                return $this->json(['data' => true]);
            }else{
                return $this->json(['errors' => [['title' => 'User nor found.']]]);
            }
        }else{
            return $this->json(['errors' => [['title' => 'Email not found.']]], Response::HTTP_FORBIDDEN);
        }
    }

    function resetPassword(Request $request, $token){
        $user = $this->em->getRepository('App:User')->findOneBy(['token' => $token]);
        if($user){
            $error = null;
            if($request->isMethod('POST')){
                $password = $request->request->get('npass');
                $confirmPassword = $request->request->get('cpass');
                if($password === $confirmPassword && strlen($password) >= 6){
                    $user->setToken(null);
                    $user->setTokenDate(null);
                    $user->setPassword($this->encoder->encodePassword($user, $password));
                    $this->em->persist($user);
                    $this->em->flush();
                    return $this->redirect('/');
                }else{
                    $error = 'Password entered not matching or less than 6 letter.';
                }
            }

            $url = $this->container->get('router')->generate('user_reset_password', array('token' => $token), true);
            return new Response(
                '<div style="position: relative; background: repeating-linear-gradient(140deg, #F1F5FA, #F1F5FA 600px, #E4E7FB 600px, #E4E7FB 1200px, #C7CCF0 1200px, #C7CCF0 1800px); height: 100%; width: auto ">
                    <div style="width: 500px; height: 330px;margin: 0 auto;position: relative; top:50px; overflow: hidden;box-shadow: 0 0 6px 6px rgba(0, 0, 0, 0.1);" >
                    <div style="display: block; text-align: center; top:20px; font-weight: 700;font-size: 24px; padding: 15px"> Reset Password</div>
                    <div style="display: flex;height: 85%;width: 100%;background: white;position: absolute; justify-content: center; align-items: center">
                        <form action='.$url.' method="post">
                          <label for="npass" style="font-weight: 600;font-size: 20px;">New Password:</label><br>
                          <input type="password" id="npass" name="npass" style="font-size: 2rem;width: 100%; margin: 5px 0;"><br>
                          <label for="cpass" style="font-weight: 600;font-size: 20px;">Confirm Password:</label><br>
                          <input type="password" id="cpass" name="cpass" style="font-size: 2rem;width: 100%; margin: 5px 0;">
                          <div style="color: #B82424;margin: 2px 0; font-weight: 600">'.$error.'</div>
                          <br>
                          <input type="submit" value="Submit" style="background-color: #F17030;color: white;border: none;font-size: 16px;font-weight: 400;padding: 10px 40px;border-radius: 25px;cursor: pointer;">
                        </form>
                    </div>
                </div>
            </div>'
            );
        }else{
            return new Response(
                '<div style="padding: 20px; background: repeating-linear-gradient(140deg, #F1F5FA, #F1F5FA 600px, #E4E7FB 600px, #E4E7FB 1200px, #C7CCF0 1200px, #C7CCF0 1800px); height: 100%; width: auto ">
                        <h1>Link Expired or User not found</h1>
                        <p>Return to the homepage <a href='.$_ENV['SITE_URL'].'>click here</a>.</p>
                    </div>'
            );
        }

    }

    function emailTest(Request $request): Response
    {
        if($request->isMethod('POST')){
            $body = "<p>This is a <strong>TEST</strong> Email</p>";
            $this->messageHelper->sendMail($request->get('email'),'test@test.com', 'Email Test', $body);
            echo "<p>Email Sent</p>";
        }
        $form = "<form method='post'><input type='email' name='email' placeholder='Send To' required><button type='submit'>SEND</button></form>";
        return new Response($form);
    }
}