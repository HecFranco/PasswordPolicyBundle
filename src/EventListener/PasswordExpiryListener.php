<?php

namespace HecFranco\PasswordPolicyBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
//
use Symfony\Component\HttpKernel\Event\RequestEvent;
//
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
// services
use HecFranco\PasswordPolicyBundle\Service\PasswordExpiryServiceInterface;
// attributes
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'kernel.request', method: 'onKernelRequest')]
class PasswordExpiryListener
{

  /**
   * @var string
   */
  private string $errorMessageType;

  /**
   * @var string
   */
  private string $errorMessage;


  /**
   * The function is a constructor that initializes properties and dependencies for a class.
   *
   * @param string errorMessageType A string representing the type of error message. This could be used
   * to differentiate between different types of error messages, such as "error", "warning", or "info".
   * @param string errorMessage The `errorMessage` parameter is a string that represents the error
   * message to be displayed.
   */
  public function __construct(
    public PasswordExpiryServiceInterface $passwordExpiryService,
    public SessionInterface $session,
    string $errorMessageType,
    string $errorMessage
  ) {
    $this->errorMessageType = $errorMessageType;
    $this->errorMessage = $errorMessage;
  }

  /**
   * The function checks if a route is locked and if the password has expired, and if so, it adds an
   * error message to the session flash bag and redirects the user to the current page.
   *
   * @param RequestEvent event The `` parameter is an instance of the `RequestEvent` class. It
   * represents an event that occurs when a request is made to the application.
   *
   * @return The code returns either nothing (null) or a RedirectResponse object.
   */
  public function onKernelRequest(RequestEvent $event)
  {
    //
    if (!$event->isMainRequest()) {
      return;
    }

    $request = $event->getRequest();
    $route = $request->get('_route');
    //
    $isLockedRoute = $this->passwordExpiryService->isLockedRoute($route);

    if ($isLockedRoute) {
      return;
    }

    if (
      !in_array($route, $this->passwordExpiryService->getExcludedRoutes())
      && $this->passwordExpiryService->isPasswordExpired()
    ) {
      if ($this->session instanceof Session) {
        $this->session->getFlashBag()->add($this->errorMessageType, $this->errorMessage);
      }
      //
      $requestUri = $request->get('pathInfo');
      $event->setResponse(new RedirectResponse($requestUri));
    }
  }
}
