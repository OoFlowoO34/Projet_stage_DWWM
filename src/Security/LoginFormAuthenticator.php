<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;


class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    /**
    * Trait using session to get (and set) the URL the user last visited before being forced to authenticate.
    */
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $userRepository;

    public function __construct(private UrlGeneratorInterface $urlGenerator, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    
    // https://symfony.com/doc/current/security/custom_authenticator.html#security-passports
    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $passport = new Passport(
            new UserBadge($email, function (string $userIdentifier) {
            return $this->userRepository->findOneBy(['email' => $userIdentifier]);
            }),
            new PasswordCredentials($request->request->get('password', '')),
            [ 
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ],
        );
       
        /**
         * @var $user User
         */     
        $user = $passport->getUser();

        $isVerfied = $user->isVerified();
       
        if(!$isVerfied){
            //stop the execution
            throw new CustomUserMessageAuthenticationException("Votre compte n'a pas été vérifié");
        }
        
        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return $passport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // For example:
        return new RedirectResponse($this->urlGenerator->generate('app_demand_index'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }
    

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
