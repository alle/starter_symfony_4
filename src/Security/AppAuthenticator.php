<?php

declare(strict_types=1);

namespace App\Security;

use App\Model\User\Credentials;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    /** @var RouterInterface */
    private $router;

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(
        RouterInterface $router,
        CsrfTokenManagerInterface $csrfTokenManager,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request): bool
    {
        return 'app_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request): Credentials
    {
        $credentials = Credentials::build(
            $request->request->get('email'),
            $request->request->get('password'),
            $request->request->get('_csrf_token')
        );

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials->email()
        );

        return $credentials;
    }

    /**
     * @param Credentials $credentials
     */
    public function getUser(
        $credentials,
        UserProviderInterface $userProvider
    ): UserInterface {
        if (!$this->csrfTokenManager->isTokenValid($credentials->csrfToken())) {
            throw new InvalidCsrfTokenException();
        }

        // Load / create our user however you need.
        // You can do this by calling the user provider, or with custom logic here.
        $user = $userProvider->loadUserByUsername($credentials->email());

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }

        return $user;
    }

    /**
     * @param Credentials $credentials
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->passwordEncoder->isPasswordValid(
            $user,
            $credentials->password()
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        $providerKey
    ): ?Response {
        if ($targetPath = $this->getTargetPath(
            $request->getSession(),
            $providerKey
        )) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('admin_dashboard'));
    }

    protected function getLoginUrl(): string
    {
        return $this->router->generate('app_login');
    }
}
