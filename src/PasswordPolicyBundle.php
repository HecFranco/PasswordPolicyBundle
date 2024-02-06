<?php

namespace HecFranco\PasswordPolicyBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
//
use HecFranco\PasswordPolicyBundle\DependencyInjection\PasswordPolicyExtension;

class PasswordPolicyBundle extends Bundle
{
  /**
   * Overridden to allow for the custom extension alias.
   */
  public function getContainerExtension(): ?ExtensionInterface
  {
    if (null === $this->extension) {
      $this->extension = new PasswordPolicyExtension();
    }
    return $this->extension;
  }
}
