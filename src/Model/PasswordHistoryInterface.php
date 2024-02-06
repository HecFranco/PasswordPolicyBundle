<?php

namespace HecFranco\PasswordPolicyBundle\Model;


interface PasswordHistoryInterface
{

  /**
   * @return string
   */
  public function getPassword(): string;

  /**
   * @param string $password
   */
  public function setPassword(string $password): self;

  /**
   * @return \DateTime
   */
  public function getCreatedAt(): ?\DateTimeInterface;

  /**
   * @param \DateTime $dateTime
   * @return \DateTime|null
   */
  public function setCreatedAt(\DateTimeInterface $createdAt): self;
}
