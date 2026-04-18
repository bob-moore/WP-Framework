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
namespace Mwf\Cornerstone\PHPUnit\Middleware;

use Mwf\Cornerstone\Middleware\Scope;
use Mwf\Cornerstone\PHPUnit\Abstracts;
final class ScopeTest extends Abstracts\ModuleTestCase
{
    /**
     * Setup the test case with a new instance of the class
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->setModule(Scope::class);
        parent::setUp();
    }
    /**
     * Test the title method
     * 
     * Ensure the method return the original title, sans filter.
     * Ensure when the filter is applied, the method returns the filtered title.
     * 
     * @covers Mwf\Cornerstone\Middleware\Timber::title
     *
     * @return void
     */
    public function testTitle(): void
    {
        $this->assertEquals('Title', $this->module->title('Title'));
        \WP_Mock::onFilter("{$this->package}_page_title")->with('Title')->reply('Filtered Title');
        $this->assertEquals('Filtered Title', $this->module->title('Title'));
    }
    /**
     * Test __call magic method
     * 
     * 1. Ensure the method returns the value of a function that returns a value.
     * 2. Ensure the method returns the value of a function that echoes a value.
     * 3. Ensure the method returns an empty string when the function is not callable.
     * 
     * @covers Mwf\Cornerstone\Middleware\Timber::__call
     *
     * @return void
     */
    public function testFunctionCall(): void
    {
        $this->assertEquals(get_the_title(), $this->module->get_the_title());
        $this->assertEquals(get_the_title(), $this->module->the_title());
        $this->assertEmpty($this->module->non_existing_function());
    }
}
