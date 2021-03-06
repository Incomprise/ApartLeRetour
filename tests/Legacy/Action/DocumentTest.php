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

namespace Thelia\Tests\Action;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Thelia\Action\Document;
use Thelia\Core\Event\Document\DocumentEvent;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Files\FileManager;
use Thelia\Model\ConfigQuery;
use Thelia\Tests\TestCaseWithURLToolSetup;

/**
 * Class DocumentTest.
 */
class DocumentTest extends TestCaseWithURLToolSetup
{
    protected $cache_dir_from_web_root;

    protected $request;

    protected $session;

    public function getContainer()
    {
        $container = new ContainerBuilder();

        $dispatcher = $this->createMock("Symfony\Component\EventDispatcher\EventDispatcherInterface");

        $container->set('event_dispatcher', $dispatcher);

        $fileManager = new FileManager([
            'document.product' => 'Thelia\\Model\\ProductDocument',
            'image.product' => 'Thelia\\Model\\ProductImage',
            'document.category' => 'Thelia\\Model\\CategoryDocument',
            'image.category' => 'Thelia\\Model\\CategoryImage',
            'document.content' => 'Thelia\\Model\\ContentDocument',
            'image.content' => 'Thelia\\Model\\ContentImage',
            'document.folder' => 'Thelia\\Model\\FolderDocument',
            'image.folder' => 'Thelia\\Model\\FolderImage',
            'document.brand' => 'Thelia\\Model\\BrandDocument',
            'image.brand' => 'Thelia\\Model\\BrandImage',
        ]);

        $container->set('thelia.file_manager', $this->getFileManager());

        $request = new Request();
        $request->setSession($this->session);

        $container->set('request', $request);

        return $container;
    }

    public function getFileManager()
    {
        $fileManager = new FileManager([
            'document.product' => 'Thelia\\Model\\ProductDocument',
            'image.product' => 'Thelia\\Model\\ProductImage',
            'document.category' => 'Thelia\\Model\\CategoryDocument',
            'image.category' => 'Thelia\\Model\\CategoryImage',
            'document.content' => 'Thelia\\Model\\ContentDocument',
            'image.content' => 'Thelia\\Model\\ContentImage',
            'document.folder' => 'Thelia\\Model\\FolderDocument',
            'image.folder' => 'Thelia\\Model\\FolderImage',
            'document.brand' => 'Thelia\\Model\\BrandDocument',
            'image.brand' => 'Thelia\\Model\\BrandImage',
        ]);

        return $fileManager;
    }

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());
        $this->request = new Request();

        $this->request->setSession($this->session);

        // mock cache configuration.
        $config = ConfigQuery::create()->filterByName('document_cache_dir_from_web_root')->findOne();

        if ($config != null) {
            $this->cache_dir_from_web_root = $config->getValue();

            $config->setValue(__DIR__.'/assets/documents/cache');

            $config->setValue($this->cache_dir_from_web_root)->save();
        }
    }

    public static function setUpBeforeClass(): void
    {
        $dir = THELIA_WEB_DIR.'/cache/tests';
        if ($dh = @opendir($dir)) {
            while ($file = readdir($dh)) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                unlink(sprintf('%s/%s', $dir, $file));
            }

            closedir($dh);
        }
    }

    protected function tearDown(): void
    {
        // restore cache configuration.
        $config = ConfigQuery::create()->filterByName('document_cache_dir_from_web_root')->findOne();

        if ($config != null) {
            $config->setValue($this->cache_dir_from_web_root)->save();
        }
    }

    /**
     * Documentevent is empty, mandatory parameters not specified.
     */
    public function testProcessEmptyDocumentEvent(): void
    {
        $event = new DocumentEvent($this->request);

        $document = new Document($this->getFileManager());

        $this->expectException(\InvalidArgumentException::class);
        $document->processDocument($event);
    }

    /**
     * Try to process a non-existent file.
     */
    public function testProcessNonExistentDocument(): void
    {
        $event = new DocumentEvent($this->request);

        $document = new Document($this->getFileManager());

        $event->setCacheFilepath('blablabla.txt');
        $event->setCacheSubdirectory('tests');

        $this->expectException(\InvalidArgumentException::class);
        $document->processDocument($event);
    }

    /**
     * Try to process a file outside of the cache.
     */
    public function testProcessDocumentOutsideValidPath(): void
    {
        $event = new DocumentEvent($this->request);

        $document = new Document($this->getFileManager());

        $event->setCacheFilepath('blablabla.pdf');
        $event->setCacheSubdirectory('../../../');

        $this->expectException(\InvalidArgumentException::class);
        $document->processDocument($event);
    }

    /**
     * No operation done on source file -> copie !
     */
    public function testProcessDocumentCopy(): void
    {
        $event = new DocumentEvent($this->request);

        $event->setSourceFilepath(__DIR__.'/assets/documents/sources/test-document-1.txt');
        $event->setCacheSubdirectory('tests');

        $document = new Document($this->getFileManager());

        // mock cache configuration.
        $config = ConfigQuery::create()->filterByName(Document::CONFIG_DELIVERY_MODE)->findOne();

        if ($config != null) {
            $oldval = $config->getValue();
            $config->setValue('copy')->save();
        }

        $document->processDocument($event);

        if ($config != null) {
            $config->setValue($oldval)->save();
        }

        $imgdir = ConfigQuery::read('document_cache_dir_from_web_root', null, true);

        $this->assertFileExists(THELIA_WEB_DIR."/$imgdir/tests/test-document-1.txt");
    }

    /**
     * No operation done on source file -> link !
     */
    public function testProcessDocumentSymlink(): void
    {
        $event = new DocumentEvent($this->request);

        $event->setSourceFilepath(__DIR__.'/assets/documents/sources/test-document-2.txt');
        $event->setCacheSubdirectory('tests');

        $document = new Document($this->getFileManager());

        // mock cache configuration.
        $config = ConfigQuery::create()->filterByName(Document::CONFIG_DELIVERY_MODE)->findOne();

        if ($config != null) {
            $oldval = $config->getValue();
            $config->setValue('symlink')->save();
        }

        $document->processDocument($event);

        if ($config != null) {
            $config->setValue($oldval)->save();
        }

        $imgdir = ConfigQuery::read('document_cache_dir_from_web_root', null, true);

        $this->assertFileExists(THELIA_WEB_DIR."/$imgdir/tests/test-document-2.txt");
    }

    public function testClearTestsCache(): void
    {
        $anExceptionWasThrown = false;

        try {
            $event = new DocumentEvent($this->request);

            $event->setCacheSubdirectory('tests');

            $document = new Document($this->getFileManager());

            $document->clearCache($event);
        } catch (\Exception $e) {
            $anExceptionWasThrown = true;
        }

        $this->assertFalse($anExceptionWasThrown);
    }

    public function testClearWholeCache(): void
    {
        $anExceptionWasThrown = false;

        try {
            $event = new DocumentEvent($this->request);

            $document = new Document($this->getFileManager());

            $document->clearCache($event);
        } catch (\Exception $e) {
            $anExceptionWasThrown = true;
        }

        $this->assertFalse($anExceptionWasThrown);
    }

    /**
     * Try to clear directory ouside of the cache.
     */
    public function testClearUnallowedPathCache(): void
    {
        $event = new DocumentEvent($this->request);

        $event->setCacheSubdirectory('../../../..');

        $document = new Document($this->getFileManager());

        $this->expectException(\InvalidArgumentException::class);
        $document->clearCache($event);
    }
}
