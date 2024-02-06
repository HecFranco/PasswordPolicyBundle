<?php

namespace HecFranco\PasswordPolicyBundle\Service;

use HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;

interface PasswordHistoryServiceInterface
{
    /**
     * @param \HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface $entity
     * @param int $historyLimit
     * @return array
     */
    public function getHistoryItemsForCleanup(HasPasswordPolicyInterface $entity, int $historyLimit): array;
}
