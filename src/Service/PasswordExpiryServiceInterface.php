<?php

namespace HecFranco\PasswordPolicyBundle\Service;

use HecFranco\PasswordPolicyBundle\Model\PasswordExpiryConfiguration;

interface PasswordExpiryServiceInterface
{
  /**
   * @return bool
   */
  public function isPasswordExpired(): bool;

  /**
   * @param string|null $entityClass
   * @param array $params
   * @return string
   */
  public function generateLockedRoute(string $entityClass = null, array $params = []): string;

  /**
   * @param string $entityClass
   * @return null|string
   */
  public function getLockedRoutes(string $entityClass = null): array;

  /**
   * The `isLockedRoute` function is used to check if a specific route is locked for a given entity
   * class. It takes two parameters:
   * @param string $routeName, which is the name of the route to check
   * @param string $entityClass, which is an optional parameter specifying the entity class
   **/
  public function isLockedRoute(string $routeName, string $entityClass = null): bool;

  /**
   * @param string $entityClass
   * @return array
   */
  public function getExcludedRoutes(string $entityClass = null): array;

  /**
   * @param \HecFranco\PasswordPolicyBundle\Model\PasswordExpiryConfiguration $configuration
   * @return void
   */
  public function addEntity(PasswordExpiryConfiguration $configuration): void;
}
