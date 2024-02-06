<?php


namespace HecFranco\PasswordPolicyBundle\Model;


use HecFranco\PasswordPolicyBundle\Exceptions\RuntimeException;

class PasswordExpiryConfiguration
{

  /**
   * @var string
   */
  private $entityClass;
  /**
   * @var int
   */
  private $expiryDays;
  /**
   * @var array
   */
  private $lockRoutes;
  /**
   * @var array
   */
  private $excludedRoutes;

  /**
   * PasswordExpiryConfiguration constructor.
   * @param string $class
   * @param int $expiryDays
   * @param string $lockRoutes
   * @param array $excludedRoutes
   */
  public function __construct(
    string $class,
    int $expiryDays,
    array $lockRoutes = [],
    array $excludedRoutes = []
  ) {
    if (!is_a($class, HasPasswordPolicyInterface::class, true)) {
      throw new RuntimeException(sprintf(
        'Entity %s must implement %s interface',
        $class,
        HasPasswordPolicyInterface::class
      ));
    }
    $this->entityClass = $class;
    $this->expiryDays = $expiryDays;
    $this->lockRoutes = $lockRoutes;
    $this->excludedRoutes = $excludedRoutes;
  }

  /**
   * @return string
   */
  public function getEntityClass(): string
  {
    return $this->entityClass;
  }

  /**
   * @return int
   */
  public function getExpiryDays(): int
  {
    return $this->expiryDays;
  }

  /**
   * @return string
   */
  public function getLockRoutes(): array
  {
    return $this->lockRoutes;
  }

  /**
   * @return array
   */
  public function getExcludedRoutes(): array
  {
    return $this->excludedRoutes;
  }

}
