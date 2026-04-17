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

use Mwf\Cornerstone\Services\Compiler;
use Mwf\Cornerstone\Services\FilePathResolver;
use Mwf\Cornerstone\PHPUnit\Abstracts;

final class CompilerTest extends Abstracts\ModuleTestCase
{
    /**
     * Setup the test case with a new instance of the class
     *
     * @return void
     */
    public function setUp(): void
    {
        $file_path_resolver = \Mockery::mock( FilePathResolver::class );
        
        $file_path_resolver->allows()->resolve( '/templates' )->andReturns( dirname( __DIR__, 3 ) . '/templates/' );

        $this->setModule( Compiler::class, $file_path_resolver );

        parent::setUp();
    }
    /**
     * Test the template location filters adds the correct locations
     * 
     * @covers Mwf\Cornerstone\Services\Compiler::templateLocations
     * @return void
     */
    public function testTemplateLocations(): void
    {
        /**
         * Array structure passed from timber through the filter
         */
        $timber_stub = [
            '__main__' => [
                WP_CONTENT_DIR,
            ],
        ];
        /**
         * Expected array structure after the filter
         */
        $expected = array_merge( $timber_stub, [
            'mwf_cornerstone' => [
                WP_CONTENT_DIR,
                dirname( __DIR__, 3 ) . '/templates/'
            ],
        ] );

        $this->assertEquals( $expected, $this->module->templateLocations( $timber_stub ) );
    }
}