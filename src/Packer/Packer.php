<?php

declare(strict_types=1);

/*
 * This file is part of the Tarantool Client package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tarantool\Client\Packer;

use Tarantool\Client\Request\Request;
use Tarantool\Client\Response;

interface Packer
{
    public function pack(Request $request, int $sync = null) : string;

    public function unpack(string $data) : Response;
}
