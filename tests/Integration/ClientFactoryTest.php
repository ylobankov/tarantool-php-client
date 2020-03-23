<?php

/**
 * This file is part of the Tarantool Client package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tarantool\Client\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tarantool\Client\Client;
use Tarantool\Client\Packer\Packer;
use Tarantool\Client\Packer\PurePacker;

final class ClientFactoryTest extends TestCase
{
    /**
     * @group only-pure-packer
     */
    public function testFromDefaultsCreatesClientWithPurePacker() : void
    {
        $client = Client::fromDefaults();

        self::assertInstanceOf(PurePacker::class, $client->getHandler()->getPacker());
    }

    /**
     * @group only-pecl-packer
     */
    public function testFromDefaultsCreatesClientWithPeclPacker() : void
    {
        $client = Client::fromDefaults();

        self::assertInstanceOf(PurePacker::class, $client->getHandler()->getPacker());
    }

    /**
     * @group only-pure-packer
     */
    public function testFromOptionsCreatesClientWithPurePacker() : void
    {
        $client = Client::fromOptions(['uri' => 'tcp://tnt']);

        self::assertInstanceOf(PurePacker::class, $client->getHandler()->getPacker());
    }

    /**
     * @group only-pecl-packer
     */
    public function testFromOptionsCreatesClientWithPeclPacker() : void
    {
        $client = Client::fromOptions(['uri' => 'tcp://tnt']);

        self::assertInstanceOf(PurePacker::class, $client->getHandler()->getPacker());
    }

    public function testFromOptionsCreatesClientWithCustomPacker() : void
    {
        $packer = $this->createMock(Packer::class);
        $client = Client::fromOptions(['uri' => 'tcp://tnt'], $packer);

        self::assertSame($packer, $client->getHandler()->getPacker());
    }

    /**
     * @group only-pure-packer
     */
    public function testFromDsnCreatesClientWithPurePacker() : void
    {
        $client = Client::fromDsn('tcp://tnt');

        self::assertInstanceOf(PurePacker::class, $client->getHandler()->getPacker());
    }

    /**
     * @group only-pecl-packer
     */
    public function testFromDsnCreatesClientWithPeclPacker() : void
    {
        $client = Client::fromDsn('tcp://tnt');

        self::assertInstanceOf(PurePacker::class, $client->getHandler()->getPacker());
    }

    public function testFromDsnCreatesClientWithCustomPacker() : void
    {
        $packer = $this->createMock(Packer::class);
        $client = Client::fromDsn('tcp://tnt', $packer);

        self::assertSame($packer, $client->getHandler()->getPacker());
    }
}
