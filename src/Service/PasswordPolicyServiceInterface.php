<?php

declare(strict_types=1);

namespace HecFranco\PasswordPolicyBundle\Service;

use HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use HecFranco\PasswordPolicyBundle\Model\PasswordHistoryInterface;

interface PasswordPolicyServiceInterface
{
    public function getHistoryByPassword(
        string $password,
        HasPasswordPolicyInterface $hasPasswordPolicy
    ): ?PasswordHistoryInterface;
}
