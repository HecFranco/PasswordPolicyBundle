<?php


namespace HecFranco\PasswordPolicyBundle\Model;


use DateTime;
use Doctrine\Common\Collections\Collection;

/**
 * Interface HasPasswordPolicyInterface
 * @package HecFranco\PasswordPolicyBundle\Model
 */
interface HasPasswordPolicyInterface
{
  /**
   * @return mixed
   */
  public function getId();

  /**
   * @return DateTime
   */
  public function getPasswordChangedAt(): ?DateTime;

  /**
   * @param DateTime $dateTime
   */
  public function setPasswordChangedAt(DateTime $dateTime): self;

  /**
   * @return \Doctrine\Common\Collections\Collection
   */
  public function getPasswordHistory(): Collection;

  /**
   * @param PasswordHistoryInterface $passwordHistory
   */
  public function addPasswordHistory(PasswordHistoryInterface $passwordHistory): static;

  /**
   * @return mixed
   */
  public function getPassword(): string;

}
