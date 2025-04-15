<?php

declare(strict_types=1);

namespace HecFranco\PasswordPolicyBundle\Tests\Unit\Service;


use HecFranco\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use Mockery\Mock;
use Mockery;
use HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use HecFranco\PasswordPolicyBundle\Service\PasswordPolicyService;
use HecFranco\PasswordPolicyBundle\Service\PasswordPolicyServiceInterface;
use HecFranco\PasswordPolicyBundle\Tests\Unit\Mocks\PasswordHistoryMock;
use HecFranco\PasswordPolicyBundle\Tests\UnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\HecFranco\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\HecFranco\Encoder\PasswordEncoderInterface;

final class PasswordPolicyServiceTest extends UnitTestCase
{
    /**
     * @var HasPasswordPolicyInterface|Mock
     */
    private $entityMock;

    /**
     * @var PasswordPolicyServiceInterface|Mock
     */
    private $passwordPolicyServiceMock;

    /**
     *
     */
    protected function setUp(): void
    {
        $passwordEncoderFactoryMock = Mockery::mock(EncoderFactoryInterface::class);
        $this->passwordPolicyServiceMock = Mockery::mock(PasswordPolicyService::class, [
            $passwordEncoderFactoryMock,
        ])->makePartial();

        $this->entityMock = Mockery::mock(HasPasswordPolicyInterface::class);

    }

    public function testGetHistoryByPasswordMatch(): void
    {
        $encoderMock = Mockery::mock(PasswordEncoderInterface::class);
        $encoderMock->shouldReceive('isPasswordValid')
                    ->twice()
                    ->andReturn(false, true);

        $this->passwordPolicyServiceMock->shouldReceive('getEncoder')
                                        ->once()
                                        ->withArgs([$this->entityMock])
                                        ->andReturn($encoderMock);

        $history[] = $this->makePasswordHistoryMock();
        $history[] = $this->makePasswordHistoryMock();

        $this->entityMock->shouldReceive('getPasswordHistory')
                         ->once()
                         ->andReturn(new ArrayCollection($history));

        $actual = $this->passwordPolicyServiceMock->getHistoryByPassword('pwd', $this->entityMock);
        $this->assertEquals($history[1], $actual);
    }

    public function testGetHistoryByPasswordNoMatch(): void
    {
        $encoderMock = Mockery::mock(PasswordEncoderInterface::class);
        $encoderMock->shouldReceive('isPasswordValid')
                    ->twice()
                    ->andReturn(false, false);

        $this->passwordPolicyServiceMock->shouldReceive('getEncoder')
                                        ->once()
                                        ->withArgs([$this->entityMock])
                                        ->andReturn($encoderMock);


        $history[] = $this->makePasswordHistoryMock();
        $history[] = $this->makePasswordHistoryMock();

        $this->entityMock->shouldReceive('getPasswordHistory')
                         ->once()
                         ->andReturn(new ArrayCollection($history));

        $actual = $this->passwordPolicyServiceMock->getHistoryByPassword('pwd', $this->entityMock);
        $this->assertNotInstanceOf(PasswordHistoryInterface::class, $actual);
    }

    public function testGetHistoryByPasswordEmptyHistory(): void
    {
        $encoderMock = Mockery::mock(PasswordEncoderInterface::class);
        $encoderMock->shouldNotReceive('isPasswordValid');

        $this->passwordPolicyServiceMock->shouldReceive('getEncoder')
                                        ->once()
                                        ->withArgs([$this->entityMock])
                                        ->andReturn($encoderMock);

        $this->entityMock->shouldReceive('getPasswordHistory')
                         ->once()
                         ->andReturn(new ArrayCollection());


        $actual = $this->passwordPolicyServiceMock->getHistoryByPassword('pwd', $this->entityMock);
        $this->assertNotInstanceOf(PasswordHistoryInterface::class, $actual);
    }

    /**
     * @return Mock|PasswordHistoryMock
     */
    private function makePasswordHistoryMock()
    {
        return Mockery::mock(PasswordHistoryMock::class)
                       ->shouldReceive('getPassword')
                       ->once()
                       ->andReturn('pwd')
                       ->shouldReceive('getSalt')
                       ->andReturn(null)
                       ->getMock();
    }
}
