<?php
/**
 * Admin context tests.
 *
 * @package WPFramework
 * @subpackage PHPUnit/Tests/Context
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\PHPUnit\Context;

use Bmd\WPFramework\Context\Admin;
use Bmd\WPFramework\Services\ScriptLoader;
use Bmd\WPFramework\Services\StyleLoader;
use WP_Mock\Tools\TestCase;

/**
 * Test suite for Admin context handler.
 */
final class AdminTest extends TestCase
{
    /**
     * @covers \Bmd\WPFramework\Context\Admin::enqueueAssets
     */
    public function testEnqueueAssetsLoadsAdminBundles(): void
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
            ->with( 'bmd_wp_framework-admin', 'dist/build/scripts/admin.bundle.js', [], '', true );

        $styleLoader->expects( $this->once() )
            ->method( 'enqueue' )
            ->with( 'bmd_wp_framework-admin', 'dist/build/admin.css', [], null, 'all' );

        $handler = new Admin( $styleLoader, $scriptLoader, 'bmd_wp_framework' );
        $handler->enqueueAssets();
    }

    /**
     * @covers \Bmd\WPFramework\Context\Admin::enqueueEditorAssets
     */
    public function testEnqueueEditorAssetsLoadsEditorBundles(): void
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
            ->with( 'bmd_wp_framework-editor', 'dist/build/editor.js', [], '', true );

        $styleLoader->expects( $this->once() )
            ->method( 'enqueue' )
            ->with( 'bmd_wp_framework-editor', 'dist/build/editor.css', [], null, 'all' );

        $handler = new Admin( $styleLoader, $scriptLoader, 'bmd_wp_framework' );
        $handler->enqueueEditorAssets();
    }
}