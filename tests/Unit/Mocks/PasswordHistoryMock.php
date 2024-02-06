<?php


namespace HecFranco\PasswordPolicyBundle\Tests\Unit\Mocks;

use HecFranco\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use HecFranco\PasswordPolicyBundle\Traits\PasswordHistoryTrait;

/**
 * Class PasswordHistoryMock.
 * Mocked class
 */
class PasswordHistoryMock implements PasswordHistoryInterface
{
    use PasswordHistoryTrait;

    private $user;

    /**
     * @param $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}
