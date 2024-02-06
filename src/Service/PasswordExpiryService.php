<?php


namespace HecFranco\PasswordPolicyBundle\Service;


use HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use HecFranco\PasswordPolicyBundle\Model\PasswordExpiryConfiguration;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PasswordExpiryService implements PasswordExpiryServiceInterface
{
  /**
   * @var \HecFranco\PasswordPolicyBundle\Model\PasswordExpiryConfiguration[]
   */
  private $entities;


  /**
   * PasswordExpiryService constructor.
   * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
   * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
   */
  public function __construct(public TokenStorageInterface $tokenStorage, public UrlGeneratorInterface $router)
  {
  }

  /**
   * @return bool
   */
  public function isPasswordExpired(): bool
  {
    /** @var HasPasswordPolicyInterface $user */
    if ($user = $this->getCurrentUser()) {
      foreach ($this->entities as $class => $config) {
        $passwordLastChange = $user->getPasswordChangedAt();
        if ($passwordLastChange && $user instanceof $class) {
          $expiresAt = (clone $passwordLastChange)->modify('+' . $config->getExpiryDays() . ' days');

          return $expiresAt <= new \DateTime();
        }
      }
    }


    return false;
  }

  /**
   * @param string $entityClass
   * @return string
   */
  public function getLockedRoutes(string $entityClass = null): array
  {
    $entityClass = $this->prepareEntityClass($entityClass);

    return isset($this->entities[$entityClass]) ? $this->entities[$entityClass]->getLockRoutes() : [];
  }

  /**
   * The function checks if a given route is locked based on the provided route name and entity class.
   *
   * @param string routeName The name of the route that you want to check if it is locked or not.
   * @param string entityClass The  parameter is an optional parameter that specifies the
   * class of the entity for which the route is being checked. If provided, it will be used to retrieve
   * the locked routes specific to that entity class. If not provided, the method will retrieve the
   * locked routes for all entity classes.
   *
   * @return bool a boolean value. It returns true if the given route name is found in the array of
   * locked routes, and false otherwise.
   */
  public function isLockedRoute(string $routeName, string $entityClass = null): bool
  {
    $lockedRoutes = $this->getLockedRoutes($entityClass);
    //
    if (in_array($routeName, (array) $lockedRoutes)) {
      return true;
    }
    //
    return false;
  }
  public function getResetPasswordRouteName(): string
  {

  }
  /**
   * @param string $entityClass
   * @return array
   */
  public function getExcludedRoutes(string $entityClass = null): array
  {
    $entityClass = $this->prepareEntityClass($entityClass);

    return isset($this->entities[$entityClass]) ? $this->entities[$entityClass]->getExcludedRoutes() : [];
  }

  /**
   * @param \HecFranco\PasswordPolicyBundle\Model\PasswordExpiryConfiguration $configuration
   */
  public function addEntity(PasswordExpiryConfiguration $configuration): void
  {
    $this->entities[$configuration->getEntityClass()] = $configuration;
  }

  /**
   * @return \HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface|null
   */
  private function getCurrentUser(): ?HasPasswordPolicyInterface
  {
    $token = $this->tokenStorage->getToken();
    if ($token && $user = $token->getUser()) {
      if ($user === 'anon.') {
        return null;
      }

      return $user instanceof HasPasswordPolicyInterface ? $user : null;
    }

    return null;
  }

  /**
   * @param string $entityClass
   * @return string
   */
  private function prepareEntityClass(?string $entityClass): ?string
  {
    if (is_null($entityClass) && $user = $this->getCurrentUser()) {
      $entityClass = get_class($user);
    }

    return $entityClass;
  }
}
