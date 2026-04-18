<?php

/**
 * PostMeta Service Test
 *
 * PHP Version 8.2
 *
 * @package mwf_cornerstone
 * @subpackage PHPUnit/Tests/Services
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */
namespace Mwf\Cornerstone\PHPUnit\Abstracts;

use Mwf\Cornerstone\Services\PostMeta;
use WP_Mock\Tools\TestCase as TestCase;
use ReflectionClass;
abstract class ModuleTestCase extends TestCase
{
    /**
     * Instance of the module being tested
     *
     * @var \Mwf\Cornerstone\Abstracts\Module
     */
    protected $module;
    /**
     * The mock package name
     *
     * @var string
     */
    protected $package = TEST_UNIT_PACKAGE_NAME;
    /**
     * Setup instance module
     *
     * @param string $class : name of the class being tested.
     *
     * @return void
     */
    protected function setModule(string $class, ...$args): void
    {
        array_push($args, $this->package);
        $this->module = new $class(...$args);
    }
    /**
     * Nullify the service class to start fresh on the next test
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->module = null;
        parent::tearDown();
    }
    /**
     * Test the package constructor
     * 
     * This test will verify that the package name is set and retrieved correctly.
     * 
     * @covers Mwf\Cornerstone\Services\PostMeta::__construct
     *
     * @return void
     */
    public function testPackageProperty(): void
    {
        $reflection = new ReflectionClass($this->module);
        $package = $reflection->getProperty('package');
        $package->setAccessible(\true);
        $this->assertEquals($this->package, $package->getValue($this->module));
    }
    /**
     * Test the package setter
     * 
     * This test will verify that the package name is set.
     * 
     * @covers Mwf\Cornerstone\Services\PostMeta::setPackage
     *
     * @return void
     */
    public function testSettersGetters(): void
    {
        $this->module->setPackage('new_package_name');
        $this->assertEquals('new_package_name', $this->module->getPackage());
        $this->module->setPackage('new/package/name');
        $this->assertEquals('new_package_name', $this->module->getPackage());
        $this->module->setPackage('New\Package\Name');
        $this->assertEquals('new_package_name', $this->module->getPackage());
    }
}
