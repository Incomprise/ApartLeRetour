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

namespace Thelia\Tests;

use PHPUnit\Framework\TestCase;
use Thelia\Model\ConfigQuery;

/**
 * Class WebTestCase.
 *
 * @author Manuel Raynaud <manu@raynaud.io>
 */
class WebTestCase extends TestCase
{
    protected $isMultiDomainActivated;

    protected function setUp(): void
    {
        // We have to shut down the "One domain for each lang feature" before tests,
        // to prevent 302 redirections during the tests.
        $this->isMultiDomainActivated = ConfigQuery::read('one_domain_foreach_lang', null, true);

        ConfigQuery::write('one_domain_foreach_lang', false);
    }

    /**
     * @var \Thelia\Core\Thelia
     */
    protected static $kernel;

    /**
     * Shuts the kernel down if it was used in the test.
     */
    protected function tearDown(): void
    {
        ConfigQuery::write('one_domain_foreach_lang', $this->isMultiDomainActivated);

        if (null !== static::$kernel) {
            static::$kernel->shutdown();
        }
    }
}
