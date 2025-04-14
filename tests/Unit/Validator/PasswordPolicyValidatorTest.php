<?php


namespace HecFranco\PasswordPolicyBundle\Tests\Unit\Validator;


use Mockery\Mock;
use Mockery;
use DateTime;
use HecFranco\PasswordPolicyBundle\Exceptions\ValidationException;
use HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use HecFranco\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use HecFranco\PasswordPolicyBundle\Service\PasswordPolicyServiceInterface;
use HecFranco\PasswordPolicyBundle\Tests\UnitTestCase;
use HecFranco\PasswordPolicyBundle\Validator\PasswordPolicy;
use HecFranco\PasswordPolicyBundle\Validator\PasswordPolicyValidator;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class PasswordPolicyValidatorTest extends UnitTestCase
{
  /**
   * @var HasPasswordPolicyInterface|Mock
   */
  private $entityMock;
  /**
   * @var ExecutionContextInterface|Mock
   */
  private $contextMock;
  /**
   * @var PasswordPolicyValidator|Mock
   */
  private $validator;
  /**
   * @var PasswordPolicyServiceInterface|Mock
   */
  private $passwordPolicyServiceMock;
  /**
   * @var \Symfony\Component\Translation\TranslatorInterface|Mock
   */
  private $translatorMock;

  /**
   * Setup.
   */
  protected function setUp(): void
  {
    $this->translatorMock = Mockery::mock(TranslatorInterface::class);
    $this->translatorMock->shouldReceive('getLocale')
      ->andReturn('en');

    $this->passwordPolicyServiceMock = Mockery::mock(PasswordPolicyServiceInterface::class);
    $this->validator = Mockery::mock(PasswordPolicyValidator::class, [
      $this->passwordPolicyServiceMock,
      $this->translatorMock,
    ])->makePartial();
    $this->contextMock = Mockery::mock(ExecutionContextInterface::class);
    $this->entityMock = Mockery::mock(HasPasswordPolicyInterface::class);
  }

  public function testValidatePass(): void
  {
    $this->contextMock->shouldReceive('getObject')
      ->once()
      ->andReturn($this->entityMock);

    $constraint = new PasswordPolicy();

    $this->passwordPolicyServiceMock->shouldReceive('getHistoryByPassword')
      ->withArgs(['pwd', $this->entityMock])
      ->andReturn(null);

    $this->validator->initialize($this->contextMock);
    $this->assertTrue($this->validator->validate('pwd', $constraint));
  }

  public function testValidateFail(): void
  {
    $this->contextMock->shouldReceive('getObject')
      ->once()
      ->andReturn($this->entityMock);

    $constraintBuilderMock = Mockery::mock(ConstraintViolationBuilderInterface::class);

    $constraintBuilderMock->shouldReceive('setParameter')
      ->once()
      ->andReturnSelf();

    $constraintBuilderMock->shouldReceive('setCode')
      ->once()
      ->andReturnSelf();

    $constraintBuilderMock->shouldReceive('addViolation')
      ->once();

    $this->contextMock->shouldReceive('buildViolation')
      ->once()
      ->andReturn($constraintBuilderMock);

    $constraint = new PasswordPolicy();

    $historyMock = Mockery::mock(PasswordHistoryInterface::class);
    $historyMock->shouldReceive('getCreatedAt')
      ->andReturn(new DateTime('-2 days'));

    $this->passwordPolicyServiceMock->shouldReceive('getHistoryByPassword')
      ->withArgs(['pwd', $this->entityMock])
      ->andReturn($historyMock);

    $this->validator->initialize($this->contextMock);
    $this->assertFalse($this->validator->validate('pwd', $constraint));
  }

  public function testValidateNullValue(): void
  {
    $this->assertTrue($this->validator->validate(null, new PasswordPolicy()));
  }

  public function testValidateBadEntity(): void
  {
    $this->contextMock->shouldReceive('getObject')
      ->once()
      ->andReturn(new PasswordPolicyValidatorTest());

    $constraint = new PasswordPolicy();

    $this->validator->initialize($this->contextMock);

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Expected validation entity to implements ' . HasPasswordPolicyInterface::class);
    $this->assertTrue($this->validator->validate('pwd', $constraint));
  }
}
