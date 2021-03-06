<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Functional\Back;

use Thelia\Tests\Functional\WebTestCase;

class ConfigurationHookModuleTest extends WebTestCase
{
    public function testIndex(): void
    {
        $this->loginAdmin();

        self::$client->request('GET', '/admin/module-hooks');

        self::assertResponseIsSuccessful();
    }

    public function testOpen(): void
    {
        $this->loginAdmin();

        self::$client->request('GET', '/admin/module-hook/update/3');

        self::assertResponseIsSuccessful();

        self::$client->request('GET', '/admin/module-hook/update/15');

        self::assertResponseIsSuccessful();

        self::$client->request('GET', '/admin/module-hooks/get-module-hook-classnames/1');

        self::assertResponseIsSuccessful();
    }
}
