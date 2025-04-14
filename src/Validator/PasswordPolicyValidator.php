<?php


namespace HecFranco\PasswordPolicyBundle\Validator;


use HecFranco\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use Carbon\Carbon;
use HecFranco\PasswordPolicyBundle\Exceptions\ValidationException;
use HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use HecFranco\PasswordPolicyBundle\Service\PasswordPolicyServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordPolicyValidator extends ConstraintValidator
{

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    public function __construct(private readonly PasswordPolicyServiceInterface $passwordPolicyService, TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     * @return bool
     * @throws ValidationException
     */
    public function validate($value, Constraint $constraint)
    {
        if (is_null($value)) {
            return true;
        }

        $entity = $this->context->getObject();

        if (!$entity instanceof HasPasswordPolicyInterface) {
            throw new ValidationException(sprintf('Expected validation entity to implements %s',
                HasPasswordPolicyInterface::class));
        }

        Carbon::setLocale($this->translator->getLocale());

        if (($history = $this->passwordPolicyService->getHistoryByPassword($value, $entity)) instanceof PasswordHistoryInterface) {
            $this->context->buildViolation($constraint->message)
                          ->setParameter('{{ days }}', Carbon::instance($history->getCreatedAt())->diffForHumans())
                          ->setCode(PasswordPolicy::PASSWORD_IN_HISTORY)
                          ->addViolation();

            return false;
        }

        return true;
    }
}
