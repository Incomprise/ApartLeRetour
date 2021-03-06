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

namespace Thelia\Core\HttpKernel\HttpCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\HttpCache\HttpCache as BaseHttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Thelia\Core\HttpFoundation\Request as TheliaRequest;
use Thelia\Core\HttpFoundation\Response;

/**
 * Class HttpCache.
 *
 * @author manuel raynaud <manu@raynaud.io>
 */
class HttpCache extends BaseHttpCache implements HttpKernelInterface
{
    public function __construct(HttpKernelInterface $kernel, $options = [])
    {
        parent::__construct(
            $kernel,
            new Store($kernel->getCacheDir().'/http_cache'),
            new Esi(),
            array_merge(
                ['debug' => $kernel->isDebug()],
                $options
            )
        );
    }

    public function handle(Request $request, $type = HttpKernelInterface::MAIN_REQUEST, $catch = true): Response
    {
        if (!($request instanceof \Thelia\Core\HttpFoundation\Request)) {
            $request = TheliaRequest::create(
                $request->getUri(),
                $request->getMethod(),
                $request->getMethod() == 'GET' ? $request->query->all() : $request->request->all(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all(),
                $request->getContent()
            );
        }

        return parent::handle($request, $type, $catch);
    }
}
