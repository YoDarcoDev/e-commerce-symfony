<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class LoginFormAuthenticator extends AbstractGuardAuthenticator
{
    protected $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function supports(Request $request): bool
    {
        // Contrôle l'authentification uniquement si on se trouve sur la route security_login en méthode POST (soumission form login)
        return $request->attributes->get('_route') === 'security_login' && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        // Ce que l'on récupère ici va être envoyé à getUser, etc, login est un array
        return $request->request->get('login');
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        // UserProviderInterface va aller chercher un user dans la BDD en fonction de son nom d'utilisateur
        // On attrappe l'erreur et on envoie une exception personalisée
        try {
            return $userProvider->loadUserByUsername($credentials['email']);
        } catch(UsernameNotFoundException $e) {
            throw new AuthenticationException("Cette adresse email n'est pas connue");
        }
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        // Vérifie que le mdp fournit dans la requête correspond au mdp de la BDD
       $isValid = $this->encoder->isPasswordValid($user, $credentials['password']);

       if (!$isValid) {
           throw new AuthenticationException("Les informations de connexion ne correspondent pas");
       }
       return true;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // On ne fait rien afin de rester sur la page de login
       $request->attributes->set(Security::AUTHENTICATION_ERROR, $exception);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): RedirectResponse
    {
        // Redirige sur la page d'accueil en cas de succès
        return new RedirectResponse('/');
    }

    public function start(Request $request, AuthenticationException $authException = null): RedirectResponse
    {
        // Si quelqu'un de non connecté essaye d'accéder à une ressource pour laquelle il faut être connecté, la méthode start sera appelée et redirige le user
        return new RedirectResponse('/login');
    }

    public function supportsRememberMe()
    {
        // todo
    }
}
