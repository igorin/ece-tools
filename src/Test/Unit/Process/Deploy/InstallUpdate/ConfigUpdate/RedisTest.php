<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MagentoCloud\Test\Unit\Process\Deploy\InstallUpdate\ConfigUpdate;

use Magento\MagentoCloud\Process\Deploy\InstallUpdate\ConfigUpdate\Redis;
use PHPUnit\Framework\TestCase;
use Magento\MagentoCloud\Config\Environment;
use Magento\MagentoCloud\Config\Deploy\Writer as ConfigWriter;
use Magento\MagentoCloud\Config\Deploy\Reader as ConfigReader;
use Psr\Log\LoggerInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * @inheritdoc
 */
class RedisTest extends TestCase
{
    /**
     * @var Environment|Mock
     */
    private $environmentMock;

    /**
     * @var LoggerInterface|Mock
     */
    private $loggerMock;

    /**
     * @var ConfigWriter|Mock
     */
    private $configWriterMock;

    /**
     * @var ConfigReader|Mock
     */
    private $configReaderMock;

    /**
     * @var Redis
     */
    private $process;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->environmentMock = $this->getMockBuilder(Environment::class)
            ->setMethods(['getRelationships', 'getAdminUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();
        $this->configWriterMock = $this->createMock(ConfigWriter::class);
        $this->configReaderMock = $this->createMock(ConfigReader::class);

        $this->process = new Redis(
            $this->environmentMock,
            $this->configReaderMock,
            $this->configWriterMock,
            $this->loggerMock
        );
    }

    public function testExecute()
    {
        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('Updating env.php Redis cache configuration.');
        $this->environmentMock->expects($this->any())
            ->method('getRelationships')
            ->willReturn([
                'redis' => [
                    0 => [
                        'host' => '127.0.0.1',
                        'port' => '6379'
                    ]
                ],
            ]);
        $this->environmentMock->expects($this->any())
            ->method('getAdminUrl')
            ->willReturn('admin');

        $this->configReaderMock->expects($this->once())
            ->method('read')
            ->willReturn([]);

        $this->configWriterMock->expects($this->once())
            ->method('write')
            ->with([
                'cache' => [
                    'frontend' => [
                        'default' => [
                            'backend' => 'Cm_Cache_Backend_Redis',
                            'backend_options' => [
                                'server' => '127.0.0.1',
                                'port' => '6379',
                                'database' => 1,
                            ],
                        ],
                        'page_cache' => [
                            'backend' => 'Cm_Cache_Backend_Redis',
                            'backend_options' => [
                                'server' => '127.0.0.1',
                                'port' => '6379',
                                'database' => 1,
                            ],
                        ],
                    ],
                ],
                'session' => [
                    'save' => 'redis',
                    'redis' => [
                        'host' => '127.0.0.1',
                        'port' => '6379',
                        'database' => 0,
                    ],
                ],
            ]);

        $this->process->execute();
    }

    public function testExecuteRemovingRedis()
    {
        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('Removing redis cache and session configuration from env.php.');
        $this->environmentMock->expects($this->any())
            ->method('getRelationships')
            ->willReturn([]);
        $this->environmentMock->expects($this->any())
            ->method('getAdminUrl')
            ->willReturn('admin');

        $this->configReaderMock->expects($this->once())
            ->method('read')
            ->willReturn([
                'cache' => [
                    'frontend' => [
                        'default' => [
                            'backend' => 'Cm_Cache_Backend_Redis',
                            'backend_options' => [
                                'server' => '127.0.0.1',
                                'port' => '6379',
                                'database' => 1,
                            ],
                        ],
                        'page_cache' => [
                            'backend' => 'Cm_Cache_Backend_Redis',
                            'backend_options' => [
                                'server' => '127.0.0.1',
                                'port' => '6379',
                                'database' => 1,
                            ],
                        ],
                    ],
                ],
                'session' => [
                    'save' => 'redis',
                    'redis' => [
                        'host' => '127.0.0.1',
                        'port' => '6379',
                        'database' => 0,
                    ],
                ],
            ]);

        $this->configWriterMock->expects($this->once())
            ->method('write')
            ->with([
                'cache' => [
                    'frontend' => [],
                ],
                'session' => [
                    'save' => 'db',
                ],
            ]);

        $this->process->execute();
    }
}