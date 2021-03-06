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

class ConfigurationAdvancedConfigurationTest extends WebTestCase
{
    public function testOpen(): void
    {
        $this->loginAdmin();

        self::$client->request('GET', '/admin/configuration/advanced');

        self::assertResponseIsSuccessful();

        self::$client->request('GET', '/admin/configuration/advanced/flush-cache');

        self::assertResponseRedirects();

        self::$client->request('GET', '/admin/configuration/advanced/flush-assets');

        self::assertResponseRedirects();

        self::$client->request('GET', '/admin/configuration/advanced/flush-images-and-documents');

        self::assertResponseRedirects();
    }
}
