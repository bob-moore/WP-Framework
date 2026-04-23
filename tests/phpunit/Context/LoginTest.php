<?php
/**
 * Login context tests.
 *
 * @package WPFramework
 * @subpackage PHPUnit/Tests/Context
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\PHPUnit\Context;

use Bmd\WPFramework\Context\Login;
use Bmd\WPFramework\Services\ScriptLoader;
use Bmd\WPFramework\Services\StyleLoader;
use WP_Mock\Tools\TestCase;

/**
 * Test suite for Login context handler.
 */
final class LoginTest extends TestCase
{
    /**
     * @covers \Bmd\WPFramework\Context\Login::enqueueAssets
     */
    public function testEnqueueAssetsLoadsLoginBundles(): void
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
            ->with( 'bmd_wp_framework-login', 'dist/build/login.js', [], '', true );

        $styleLoader->expects( $this->once() )
            ->method( 'enqueue' )
            ->with( 'bmd_wp_framework-login', 'dist/build/login.css', [], null, 'all' );

        $handler = new Login( $styleLoader, $scriptLoader, 'bmd_wp_framework' );
        $handler->enqueueAssets();
    }
}