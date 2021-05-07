<?php

declare(strict_types=1);

namespace PHPMate\Dashboard\Security;

use Nette\Application\ForbiddenRequestException;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Utils\Strings;

final class BasicAuthChecker
{
    public function __construct(
        private string $username,
        private string $password,
        private Request $httpRequest,
        private Response $httpResponse
    ) {}

    /**
     * @throws ForbiddenRequestException
     */
    public function check(): void
    {
        $authorizationHeaderValue = $this->httpRequest->getHeader('Authorization');

        if ($authorizationHeaderValue === null) {
            $this->terminateWithAuthenticationRequest();
        }

        $encodedCredentials = Strings::after($authorizationHeaderValue, 'Basic ');
        [$username, $password] = explode(':', base64_decode($encodedCredentials));

        if ($this->username !== $username || $this->password !== $password) {
            $this->terminateWithAuthenticationRequest();
        }
    }


    /**
     * @throws ForbiddenRequestException
     */
    private function terminateWithAuthenticationRequest(): void
    {
        $this->httpResponse->setHeader('WWW-Authenticate', 'Basic realm="Secure Area"');

        throw new ForbiddenRequestException(httpCode: 401);
    }
}
