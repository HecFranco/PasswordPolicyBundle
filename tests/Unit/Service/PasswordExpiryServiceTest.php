<?php


namespace HecFranco\PasswordPolicyBundle\Tests\Unit\Service;


use Mockery\Mock;
use HecFranco\PasswordPolicyBundle\Exceptions\RuntimeException;
use Mockery;
use DateTime;
use HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use HecFranco\PasswordPolicyBundle\Model\PasswordExpiryConfiguration;
use HecFranco\PasswordPolicyBundle\Service\PasswordExpiryService;
use HecFranco\PasswordPolicyBundle\Service\PasswordExpiryServiceInterface;
use HecFranco\PasswordPolicyBundle\Tests\UnitTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PasswordExpiryServiceTest extends UnitTestCase
{
    /**
     * @var HasPasswordPolicyInterface|Mock
     */
    protected $userMock;

    /**
     * @var UrlGeneratorInterface|Mock
     */
    protected $routerMock;

    /**
     * @var PasswordExpiryServiceInterface|Mock
     */
    private $passwordExpiryServiceMock;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage|Mock
     */
    private $tokenStorageMock;

    protected function setUp(): void
    {
        $this->tokenStorageMock = Mockery::mock(TokenStorageInterface::class);
        $this->routerMock = Mockery::mock(UrlGeneratorInterface::class);
        $this->userMock = Mockery::mock(HasPasswordPolicyInterface::class);
        $this->passwordExpiryServiceMock = Mockery::mock(PasswordExpiryService::class, [
            $this->tokenStorageMock,
            $this->routerMock,
        ])->makePartial();
    }

    /**
     * @throws RuntimeException
     */
    public function testIsPasswordExpired(): void
    {
        $expiredPassword = (new DateTime())->modify('-100 days');
        $notExpiredPassword = (new DateTime())->modify('-89 days');
        $this->userMock->shouldReceive('getPasswordChangedAt')
                       ->twice()
                       ->andReturn($expiredPassword, $notExpiredPassword);

        $tokenMock = Mockery::mock(TokenInterface::class)
                             ->shouldReceive('getUser')
                             ->andReturn($this->userMock)
                             ->getMock();

        $this->tokenStorageMock->shouldReceive('getToken')
                               ->andReturn($tokenMock);

        $this->passwordExpiryServiceMock->addEntity(
            new PasswordExpiryConfiguration($this->userMock::class, 90, 'lock')
        );

        $this->assertTrue($this->passwordExpiryServiceMock->isPasswordExpired());
        $this->assertFalse($this->passwordExpiryServiceMock->isPasswordExpired());
    }

    public function testGenerateLockedRoute(): void
    {
        $tokenMock = Mockery::mock(TokenInterface::class)
                             ->shouldReceive('getUser')
                             ->andReturn($this->userMock)
                             ->getMock();

        $this->tokenStorageMock->shouldReceive('getToken')
                               ->andReturn($tokenMock);

        $this->passwordExpiryServiceMock->addEntity(
            new PasswordExpiryConfiguration($this->userMock::class, 90, 'lock', ['id' => 1])
        );

        $this->routerMock->shouldReceive('generate')
                         ->withArgs(['lock', ['id' => 1, 'foo' => 'bar']])
                         ->andReturn('lock/1');

        $route = $this->passwordExpiryServiceMock->generateLockedRoute(null, ['foo' => 'bar']);

        $this->assertEquals('lock/1', $route);
    }

}
