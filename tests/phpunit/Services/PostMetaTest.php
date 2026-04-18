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
namespace Mwf\Cornerstone\PHPUnit\Services;

use Mwf\Cornerstone\Services\PostMeta, Mwf\Cornerstone\Interfaces, Mwf\Cornerstone\PHPUnit\Partials;
use WP_Mock, WP_Mock\Tools\TestCase as TestCase;
/**
 * Services/PostMeta Test Case
 * 
 * @subpackage PHPUnit/Tests/Services
 */
final class PostMetaTest extends TestCase
{
    use Partials\ModuleInterfaceTests;
    /**
     * Instance of the module being tested
     *
     * @var \Mwf\Cornerstone\Services\PostMeta
     */
    protected ?Interfaces\Module $module;
    /**
     * Setup the test case with a new instance of the class
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->module = new PostMeta(TEST_UNIT_PACKAGE_NAME);
    }
    /**
     * Nullify the service class to start fresh on the next test
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        $this->module = null;
    }
    /**
     * Test the fetch method
     * 
     * This test will verify that the fetch method returns the correct value.
     * 
     * @covers Mwf\Cornerstone\Services\PostMeta::fetch
     *
     * @return void
     */
    public function testFetch(): void
    {
        WP_Mock::userFunction('get_post_meta', ['return' => function ($post_id, $key, $single) {
            return match (\true) {
                $post_id === 0 => \false,
                // post does not exist.
                $key === 'meta_key' => 'Meta Value',
                // post and key exist.
                default => '',
            };
        }]);
        /**
         * Test the fetch method with a post and key that exist
         */
        $this->assertEquals('Meta Value', $this->module->fetch(1, 'meta_key'));
        /**
         * Test the fetch method with a post that exists and a key that does not
         */
        $this->assertEquals('', $this->module->fetch(1, 'non_existing_key'));
        /**
         * Test the fetch method with a post that does not exist
         */
        $this->assertFalse($this->module->fetch(0, 'meta_key'));
    }
    /**
     * Test the fetch method with a filter
     * 
     * This test will verify that the fetch method returns the correct value 
     * when a filter is applied.
     * 
     * @covers Mwf\Cornerstone\Services\PostMeta::fetch
     *
     * @return void
     */
    public function testFetchFilterApplies(): void
    {
        WP_Mock::userFunction('get_post_meta')->andReturn('Meta Value');
        WP_Mock::onFilter("{$this->module->getPackage()}_post_meta")->with('Meta Value', 1, 'meta_key')->reply('Filtered Value');
        $this->assertEquals('Filtered Value', $this->module->fetch(1, 'meta_key'));
    }
}
