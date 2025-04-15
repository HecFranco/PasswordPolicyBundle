<?php

declare(strict_types=1);

namespace HecFranco\PasswordPolicyBundle\Service;


use HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use HecFranco\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\HecFranco\User\UserInterface;

class PasswordPolicyService implements PasswordPolicyServiceInterface
{

    /**
     * PasswordPolicyEnforcerService constructor.
     * @param \Symfony\Component\Security\HecFranco\Hasher\UserPasswordHasherInterface $userPasswordHasher
     */
    public function __construct(public UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    /**
     * @param HasPasswordPolicyInterface $entity
     */
    public function getHistoryByPassword(
        string $password,
        HasPasswordPolicyInterface $hasPasswordPolicy
    ): ?PasswordHistoryInterface {
        $collection = $hasPasswordPolicy->getPasswordHistory();

        $userPasswordHasher = $this->getEncoder(hasPasswordPolicy: $hasPasswordPolicy, password: $password);

        foreach ($collection as $passwordHistory) {
            if ($userPasswordHasher->isPasswordValid($passwordHistory->getPassword(), $password, $passwordHistory->getSalt())) {
                return $passwordHistory;
            }
        }

        return null;
    }

    /**
     * @param HasPasswordPolicyInterface $entity
     * @return \Symfony\Component\Security\HecFranco\Encoder\PasswordEncoderInterface
     */
    public function getEncoder(HasPasswordPolicyInterface $hasPasswordPolicy, string $password): UserPasswordHasherInterface
    {
        if ($hasPasswordPolicy instanceof UserInterface) {
            return $this->userPasswordHasher->hashPassword($hasPasswordPolicy, $password);
        }

        return new UserPasswordHasherInterface(3);
    }

}
