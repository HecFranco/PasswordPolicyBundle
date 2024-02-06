<?php


namespace HecFranco\PasswordPolicyBundle\Tests\Unit\Service;


use HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use HecFranco\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use HecFranco\PasswordPolicyBundle\Service\PasswordHistoryService;
use HecFranco\PasswordPolicyBundle\Tests\UnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;

class PasswordHistoryServiceTest extends UnitTestCase
{
    /**
     * @var \HecFranco\PasswordPolicyBundle\Service\PasswordHistoryService|\Mockery\Mock
     */
    private $historyService;
    /**
     * @var \HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface|\Mockery\Mock
     */
    private $entityMock;

    protected function setUp()
    {
        $this->entityMock = \Mockery::mock(HasPasswordPolicyInterface::class);
        $this->historyService = \Mockery::mock(PasswordHistoryService::class)->makePartial();
    }

    public function testCleanupHistory()
    {
        $passwordHistory = $this->getDummyPasswordHistory();
        $this->entityMock->shouldReceive('getPasswordHistory')
                         ->andReturn($passwordHistory);

        $deletedItems = $this->historyService->getHistoryItemsForCleanup($this->entityMock, 3);

        $this->assertCount(7, $deletedItems);

        $actualTimestamps = array_map(function (PasswordHistoryInterface $item) {
            return $item->getCreatedAt()->format('U');
        }, $deletedItems);

        $expectedTimestamps = [];

        for ($i = 6; $i >= 0; $i--) {
            $expectedTimestamps[] = $passwordHistory->offsetGet($i)->getCreatedAt()->format('U');
        }


        $this->assertEquals($expectedTimestamps, $actualTimestamps);
    }

    public function testCleanupHistoryNoNeed()
    {
        $passwordHistory = $this->getDummyPasswordHistory();

        $this->entityMock->shouldReceive('getPasswordHistory')
                         ->andReturn($passwordHistory);

        $deletedItems = $this->historyService->getHistoryItemsForCleanup($this->entityMock, 20);

        $this->assertEmpty($deletedItems);
    }

    private function getDummyPasswordHistory(): ArrayCollection
    {
        $collection = new ArrayCollection();
        $time = time();

        for ($i = 0; $i < 10; $i++) {

            $time += $i * 100;

            $collection->add(\Mockery::mock(PasswordHistoryInterface::class)
                                     ->shouldReceive('getCreatedAt')
                                     ->andReturn((new \DateTime())->setTimestamp($time))
                                     ->getMock());
        }

        return $collection;
    }


}
