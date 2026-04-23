<?php
/**
 * Frontend context tests.
 *
 * @package WPFramework
 * @subpackage PHPUnit/Tests/Context
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\PHPUnit\Context;

use Bmd\WPFramework\Context\Frontend;
use Bmd\WPFramework\Services\ScriptLoader;
use Bmd\WPFramework\Services\StyleLoader;
use WP_Mock\Tools\TestCase;

/**
 * Test suite for Frontend context handler.
 */
final class FrontendTest extends TestCase
{
    /**
     * @covers \Bmd\WPFramework\Context\Frontend::enqueueAssets
     */
    public function testEnqueueAssetsLoadsFrontendBundles(): void
    {
        $scriptLoader = $this->getMockBuilder( ScriptLoader::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'enqueue' ] )
            ->getMock();
        $styleLoader = $this->getMockBuilder( StyleLoader::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'enqueue' ] )
            ->getMock();

        $scriptLoader->expects( $this->once() )
            ->method( 'enqueue' )
            ->with( 'bmd_wp_framework-frontend', 'dist/build/frontend.js', [], '', true );

        $styleLoader->expects( $this->once() )
            ->method( 'enqueue' )
            ->with( 'bmd_wp_framework-frontend', 'dist/build/frontend.css', [], null, 'all' );

        $handler = new Frontend( $styleLoader, $scriptLoader, 'bmd_wp_framework' );
        $handler->enqueueAssets();
    }
}