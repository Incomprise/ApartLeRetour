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

namespace TheliaSmarty\Template;

/**
 * Class allowing to describe a smarty plugin.
 *
 * Class SmartyPluginDescriptor
 */
class SmartyPluginDescriptor
{
    /**
     * @var string Smarty plugin type (block, function, etc.)
     */
    protected $type;

    /**
     * @var string Smarty plugin name. This name will be used in Smarty templates.
     */
    protected $name;

    /**
     * @var AbstractSmartyPlugin plugin implmentation class
     */
    protected $class;

    /**
     * @var string plugin implmentation method in
     */
    protected $method;

    public function __construct($type, $name, $class, $method)
    {
        $this->type = $type;
        $this->name = $name;
        $this->class = $class;
        $this->method = $method;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setClass($class): void
    {
        $this->class = $class;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setMethod($method): void
    {
        $this->method = $method;
    }

    public function getMethod()
    {
        return $this->method;
    }
}
