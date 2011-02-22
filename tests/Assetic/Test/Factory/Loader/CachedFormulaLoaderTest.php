<?php

/*
 * This file is part of the Assetic package.
 *
 * (c) Kris Wallsmith <kris.wallsmith@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Test\Factory\Loader;

use Assetic\Factory\Loader\CachedFormulaLoader;

class CachedFormulaLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $loader;
    protected $configCache;

    protected function setUp()
    {
        $this->loader = $this->getMock('Assetic\\Factory\\Loader\\FormulaLoaderInterface');
        $this->configCache = $this->getMockBuilder('Assetic\\Cache\\ConfigCache')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testNotDebug()
    {
        $expected = array(
            'foo' => array(array(), array(), array()),
            'bar' => array(array(), array(), array()),
        );

        $this->configCache->expects($this->once())
            ->method('has')
            ->will($this->returnValue(false));
        $this->loader->expects($this->once())
            ->method('load')
            ->will($this->returnValue($expected));
        $this->configCache->expects($this->once())
            ->method('set')
            ->with($this->isType('string'), $expected);

        $loader = new CachedFormulaLoader($this->loader, $this->configCache);
        $this->assertEquals($expected, $loader->load(), '->load() returns formulae');
    }

    public function testNotDebugCached()
    {
        $expected = array(
            'foo' => array(array(), array(), array()),
            'bar' => array(array(), array(), array()),
        );

        $this->configCache->expects($this->once())
            ->method('has')
            ->will($this->returnValue(true));
        $this->loader->expects($this->never())
            ->method('isFresh');
        $this->configCache->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expected));

        $loader = new CachedFormulaLoader($this->loader, $this->configCache);
        $this->assertEquals($expected, $loader->load(), '->load() returns formulae');
    }

    public function testDebugCached()
    {
        $timestamp = 123;
        $expected = array(
            'foo' => array(array(), array(), array()),
            'bar' => array(array(), array(), array()),
        );

        $this->configCache->expects($this->once())
            ->method('has')
            ->will($this->returnValue(true));
        $this->configCache->expects($this->once())
            ->method('getTimestamp')
            ->with($this->isType('string'))
            ->will($this->returnValue($timestamp));
        $this->loader->expects($this->once())
            ->method('isFresh')
            ->with($timestamp)
            ->will($this->returnValue(true));
        $this->loader->expects($this->never())
            ->method('load');
        $this->configCache->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expected));

        $loader = new CachedFormulaLoader($this->loader, $this->configCache, true);
        $this->assertEquals($expected, $loader->load(), '->load() returns formulae');
    }

    public function testDebugCachedStale()
    {
        $timestamp = 123;
        $expected = array(
            'foo' => array(array(), array(), array()),
            'bar' => array(array(), array(), array()),
        );

        $this->configCache->expects($this->once())
            ->method('has')
            ->will($this->returnValue(true));
        $this->configCache->expects($this->once())
            ->method('getTimestamp')
            ->with($this->isType('string'))
            ->will($this->returnValue($timestamp));
        $this->loader->expects($this->once())
            ->method('isFresh')
            ->with($timestamp)
            ->will($this->returnValue(false));
        $this->loader->expects($this->once())
            ->method('load')
            ->will($this->returnValue($expected));
        $this->configCache->expects($this->once())
            ->method('set')
            ->with($this->isType('string'), $expected);

        $loader = new CachedFormulaLoader($this->loader, $this->configCache, true);
        $this->assertEquals($expected, $loader->load(), '->load() returns formulae');
    }
}
