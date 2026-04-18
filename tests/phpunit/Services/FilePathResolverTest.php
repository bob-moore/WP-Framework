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
namespace Mwf\Cornerstone\PHPUnit\Services;

use Mwf\Cornerstone\Services\FilePathResolver;
use Mwf\Cornerstone\PHPUnit\Abstracts;
final class FilePathResolverTest extends Abstracts\ModuleTestCase
{
    /**
     * Setup the test case with a new instance of the class
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->setModule(FilePathResolver::class, __DIR__);
        parent::setUp();
    }
    /**
     * Test the package name
     * 
     * This test will verify that the package name is set and retrieved correctly.
     * 
     * @covers Mwf\Cornerstone\Services\FilePathResolver::resolve
     *
     * @return void
     */
    public function testResolve(): void
    {
        /**
         * Ensure return is the same as the root directory
         */
        $this->assertEquals(__DIR__, $this->module->resolve());
        /**
         * Ensure result never has a trailing slash
         */
        $this->assertEquals(__DIR__ . '/Routes/Error404', $this->module->resolve('Routes/Error404/'));
        $this->assertEquals(__DIR__ . '/Routes/Error404', $this->module->resolve('Routes/Error404'));
        /**
         * Ensure blank characters are removed
         */
        $this->assertEquals(__DIR__, $this->module->resolve('  '));
    }
}
