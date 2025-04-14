<?php


namespace HecFranco\PasswordPolicyBundle\Tests\Unit\EventListener;


use Mockery;
use HecFranco\PasswordPolicyBundle\EventListener\PasswordExpiryListener;
use HecFranco\PasswordPolicyBundle\Service\PasswordExpiryServiceInterface;
use HecFranco\PasswordPolicyBundle\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class PasswordExpiryListenerTest extends UnitTestCase
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session|\Mockery\Mock
     */
    private $sessionMock;

    /**
     * @var \HecFranco\PasswordPolicyBundle\EventListener\PasswordExpiryListener|\Mockery\Mock
     */
    private $passwordExpiryListenerMock;
    /**
     * @var PasswordExpiryServiceInterface|\Mockery\Mock
     */
    private $passwordExpiryServiceMock;

    /**
     * Setup..
     */
    protected function setUp(): void
    {
        $this->passwordExpiryServiceMock = Mockery::mock(PasswordExpiryServiceInterface::class);

        $this->passwordExpiryServiceMock->shouldReceive('getLockedRoute')
                                        ->withNoArgs()
                                        ->andReturn('locked');
        $this->passwordExpiryServiceMock->shouldReceive('getExcludedRoutes')
                                        ->withNoArgs()
                                        ->andReturn(['/excluded-1', '/excluded-2']);
        $this->passwordExpiryServiceMock->shouldReceive('generateLockedRoute')
                                        ->andReturn('/locked');

        $this->sessionMock = Mockery::mock(Session::class);

        $this->passwordExpiryListenerMock = Mockery::mock(PasswordExpiryListener::class, [
            $this->passwordExpiryServiceMock,
            $this->sessionMock,
            'error',
            'Your password expired. You need to change it'
        ])->makePartial();
    }

    public function testOnKernelRequest(): void
    {
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('getPathInfo')
                    ->once()
                    ->andReturn('/route');
        $requestMock->shouldReceive('get')
                    ->with('_route')
                    ->once()
                    ->andReturn('route');

        $responseEventMock = Mockery::mock(GetResponseEvent::class);
        $responseEventMock->shouldReceive('isMasterRequest')
                          ->andReturn(true);
        $responseEventMock->shouldReceive('getRequest')
                          ->andReturn($requestMock);

        $responseEventMock->shouldReceive('setResponse')
                          ->once()
                          ->andReturnUsing(function (RedirectResponse $response): void {
                              $this->assertEquals($response->getTargetUrl(), '/locked');
                          });

        $this->passwordExpiryServiceMock->shouldReceive('isPasswordExpired')
                                        ->once()
                                        ->andReturnTrue();

        $flashBagMock = Mockery::mock(FlashBagInterface::class);
        $flashBagMock->shouldReceive('add')
                     ->once()
                     ->withArgs(['error', 'Your password expired. You need to change it']);
        $this->sessionMock->shouldReceive('getFlashBag')
                          ->once()
                          ->andReturn($flashBagMock);

        $this->passwordExpiryListenerMock->onKernelRequest($responseEventMock);
    }

    public function testOnKernelRequestAsLockedRoute(): void
    {
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('getPathInfo')
                    ->once()
                    ->andReturn('/locked');
        $requestMock->shouldReceive('get')
                    ->with('_route')
                    ->once()
                    ->andReturn('/route');

        $responseEventMock = Mockery::mock(GetResponseEvent::class);
        $responseEventMock->shouldReceive('isMasterRequest')
                          ->andReturn(true);
        $responseEventMock->shouldReceive('getRequest')
                          ->andReturn($requestMock);

        $this->passwordExpiryServiceMock->shouldNotReceive('isPasswordExpired');

        $this->passwordExpiryListenerMock->onKernelRequest($responseEventMock);

        $this->assertTrue(true);
    }

    public function testOnKernelRequestExcludedRoute(): void
    {
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('getPathInfo')
                    ->once()
                    ->andReturn('/route');
        $requestMock->shouldReceive('get')
                    ->with('_route')
                    ->once()
                    ->andReturn('/excluded-2');

        $responseEventMock = Mockery::mock(GetResponseEvent::class);
        $responseEventMock->shouldReceive('isMasterRequest')
                          ->andReturn(true);
        $responseEventMock->shouldReceive('getRequest')
                          ->andReturn($requestMock);

        $this->passwordExpiryServiceMock->shouldNotReceive('isPasswordExpired');

        $this->passwordExpiryListenerMock->onKernelRequest($responseEventMock);

        $this->assertTrue(true);
    }

    public function testOnKernelRequestPasswordNotExpired(): void
    {
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('getPathInfo')
                    ->once()
                    ->andReturn('/route');
        $requestMock->shouldReceive('get')
                    ->with('_route')
                    ->once()
                    ->andReturn('/route');

        $responseEventMock = Mockery::mock(GetResponseEvent::class);
        $responseEventMock->shouldReceive('isMasterRequest')
                          ->andReturn(true);
        $responseEventMock->shouldReceive('getRequest')
                          ->andReturn($requestMock);

        $this->passwordExpiryServiceMock->shouldReceive('isPasswordExpired')
                                        ->once()
                                        ->andReturnFalse();

        $this->passwordExpiryListenerMock->onKernelRequest($responseEventMock);

        $this->assertTrue(true);
    }

    public function testOnKernelRequestAsSubRequest(): void
    {
        $responseEventMock = Mockery::mock(GetResponseEvent::class);
        $responseEventMock->shouldReceive('isMasterRequest')
                          ->andReturn(false);

        $this->passwordExpiryServiceMock->shouldNotReceive('isPasswordExpired');

        $this->passwordExpiryListenerMock->onKernelRequest($responseEventMock);

        $this->assertTrue(true);
    }
}
