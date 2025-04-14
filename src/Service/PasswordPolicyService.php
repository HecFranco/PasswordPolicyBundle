<?php


namespace HecFranco\PasswordPolicyBundle\Service;


use HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use HecFranco\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\HecFranco\User\UserInterface;

class PasswordPolicyService implements PasswordPolicyServiceInterface
{

    /**
     * PasswordPolicyEnforcerService constructor.
     * @param \Symfony\Component\Security\HecFranco\Encoder\EncoderFactoryInterface $encoderFactory
     */
    public function __construct(public UserPasswordHasherInterface $hasher)
    {
    }

    /**
     * @param string $password
     * @param \HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface $entity
     * @return \HecFranco\PasswordPolicyBundle\Model\PasswordHistoryInterface|null
     */
    public function getHistoryByPassword(
        string $password,
        HasPasswordPolicyInterface $user
    ): ?PasswordHistoryInterface {
        $history = $user->getPasswordHistory();

        $encoder = $this->getEncoder($user, $password);

        foreach ($history as $passwordHistory) {
            if ($encoder->isPasswordValid($passwordHistory->getPassword(), $password, $passwordHistory->getSalt())) {
                return $passwordHistory;
            }
        }

        return null;
    }

    /**
     * @param \HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface $entity
     * @return \Symfony\Component\Security\HecFranco\Encoder\PasswordEncoderInterface
     */
    public function getEncoder(HasPasswordPolicyInterface $user, string $password): UserPasswordHasherInterface
    {
        if ($user instanceof UserInterface) {
            return $this->hasher->hashPassword($user, $password);
        }
        return new UserPasswordHasherInterface(3);
    }

}
