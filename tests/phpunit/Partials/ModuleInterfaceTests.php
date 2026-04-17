<?php
/**
 * File Path Resolver Service Test
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

namespace Mwf\Cornerstone\PHPUnit\Partials;

use Mwf\Cornerstone\Core\Interfaces;

trait ModuleInterfaceTests
{
    /**
     * Asserts that the module implements the Module interface
     *
     * @param object|null $module : optional module object to test.
     * @covers Interfaces\Module
     *
     * @return void
     */
    public function testImplementsModuleInterface( ?object $module = null ): void
    {
        $this->assertInstanceOf( Interfaces\Module::class, $module ?? $this->module );
    }
    /**
     * Test the package setter
     * 
     * This test will verify that the package name is set.
     * 
     * @covers Mwf\Cornerstone\Services\PostMeta::setPackage
     * @param object|null $module : optional module object to test.
     *
     * @return void
     */
    public function testModuleSettersGetters( ?object $module = null ): void
    {
        $testModule = $module ?? $this->module;

        $testModule->setPackage( 'new_package_name' );

        $this->assertEquals( 'new_package_name', $testModule->getPackage() );

        $testModule->setPackage( 'new/package/name' );

        $this->assertEquals( 'new_package_name', $testModule->getPackage() );

        $testModule->setPackage( 'New\\Package\\Name' );

        $this->assertEquals( 'new_package_name', $testModule->getPackage() );
    }

}