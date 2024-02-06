<?php

namespace HecFranco\PasswordPolicyBundle\Service;

use HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use HecFranco\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use Symfony\Component\Security\HecFranco\User\UserInterface;

interface PasswordPolicyServiceInterface
{
    /**
     * @param string $password
     * @param \HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface $entity
     * @return \HecFranco\PasswordPolicyBundle\Model\PasswordHistoryInterface|null
     */
    public function getHistoryByPassword(
        string $password,
        HasPasswordPolicyInterface $entity
    ): ?PasswordHistoryInterface;
}
