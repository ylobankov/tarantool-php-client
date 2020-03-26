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

use PHPUnit\Framework\TestCase as BaseTestCase;
use PHPUnit\Util\Test;
use Tarantool\Client\Client;
use Tarantool\Client\Connection\Connection;
use Tarantool\Client\Connection\StreamConnection;
use Tarantool\Client\Exception\CommunicationFailed;
use Tarantool\Client\Handler\Handler;
use Tarantool\Client\Request\Request;
use Tarantool\Client\Tests\PhpUnitCompat;

abstract class TestCase extends BaseTestCase
{
    use PhpUnitCompat;

    protected const STAT_REQUEST_SELECT = 'SELECT';
    protected const STAT_REQUEST_AUTH = 'AUTH';

    /**
     * @var Client
     */
    protected $client;

    private const REGEX_REQUIRES_TARANTOOL_VERSION = '/tarantool\s+(?P<operator>[<>=!]{0,2})\s*(?<version>.+)$/i';

    public static function setUpBeforeClass() : void
    {
        $annotations = Test::parseTestMethodAnnotations(static::class);

        self::handleCustomAnnotations($annotations['class']);
    }

    protected function setUp() : void
    {
        $this->client = ClientBuilder::createFromEnv()->build();

        $annotations = $this->getAnnotations();

        self::handleCustomAnnotations($annotations['method']);
    }

    private static function handleCustomAnnotations(array $annotations) : void
    {
        if (isset($annotations['requires'])) {
            foreach ($annotations['requires'] as $requirement) {
                if (!preg_match(self::REGEX_REQUIRES_TARANTOOL_VERSION, $requirement, $matches)) {
                    continue;
                }

                $operator = empty($matches['operator']) ? '>=' : $matches['operator'];
                if (version_compare(self::getTarantoolVersion(), $matches['version'], $operator)) {
                    continue;
                }

                self::markTestSkipped(sprintf('Tarantool %s %s is required', $operator, $matches['version']));
            }
        }

        if (isset($annotations['eval'])) {
            $client = ClientBuilder::createFromEnv()->build();
            foreach ($annotations['eval'] as $expr) {
                $client->evaluate($expr);
            }
        }
    }

    final protected static function getTotalCalls(string $requestName) : int
    {
        $client = ClientBuilder::createFromEnv()->build();

        return $client->evaluate("return box.stat().$requestName.total")[0];
    }

    final protected static function getTarantoolVersion() : string
    {
        [$info] = ClientBuilder::createFromEnv()->build()->call('box.info');

        return preg_replace('/-[^-]+$/', '', $info['version']);
    }

    final protected static function triggerUnexpectedResponse(Handler $handler, Request $initialRequest, int $sync = 0) : Connection
    {
        $connection = $handler->getConnection();
        $packer = $handler->getPacker();
        $rawRequest = $packer->pack($initialRequest, $sync);

        // write a request without reading a response
        $connection->open();
        if (!\fwrite(self::getRawStream($connection), $rawRequest)) {
            throw new CommunicationFailed('Unable to write request');
        }

        return $connection;
    }

    final public static function getRawStream(StreamConnection $connection)
    {
        $prop = (new \ReflectionObject($connection))->getProperty('stream');
        $prop->setAccessible(true);

        return $prop->getValue($connection);
    }
}
