<?php


namespace HecFranco\PasswordPolicyBundle\Service;


use HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use HecFranco\PasswordPolicyBundle\Model\PasswordHistoryInterface;

class PasswordHistoryService implements PasswordHistoryServiceInterface
{
    /**
     * @param \HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface $entity
     * @param int $historyLimit
     *
     * @return array Removed items
     */
    public function getHistoryItemsForCleanup(HasPasswordPolicyInterface $entity, int $historyLimit): array
    {
        $historyCollection = $entity->getPasswordHistory();

        $len = $historyCollection->count();
        $removedItems = [];

        if ($len > $historyLimit) {
            $historyArray = $historyCollection->toArray();

            usort($historyArray, function (PasswordHistoryInterface $a, PasswordHistoryInterface $b) {
                $aTs = $a->getCreatedAt()->format('U');
                $bTs = $b->getCreatedAt()->format('U');

                return $bTs - $aTs;
            });

            $historyForCleanup = array_slice($historyArray, $historyLimit);

            foreach ($historyForCleanup as $item) {
                $removedItems[] = $item;
            }
        }

        return $removedItems;
    }

}
