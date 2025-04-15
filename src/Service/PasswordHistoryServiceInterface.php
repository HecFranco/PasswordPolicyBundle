<?php

declare(strict_types=1);

namespace HecFranco\PasswordPolicyBundle\Service;

use HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;

interface PasswordHistoryServiceInterface
{
    public function getHistoryItemsForCleanup(HasPasswordPolicyInterface $hasPasswordPolicy, int $historyLimit): array;
}
