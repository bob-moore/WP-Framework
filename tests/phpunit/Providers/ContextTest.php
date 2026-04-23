<?php
/**
 * Context provider tests.
 *
 * @package WPFramework
 * @subpackage PHPUnit/Tests/Providers
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\PHPUnit\Providers;

use Bmd\WPFramework\Providers\Context;
use WP_Mock;
use WP_Mock\Tools\TestCase;

/**
 * Test suite for the Context provider.
 */
final class ContextTest extends TestCase
{
    /**
     * Register default conditional function mocks used by all tests.
     */
    private function mockDefaultContextConditionals(): void
    {
        $GLOBALS['wp_framework_is_admin'] = false;
        $GLOBALS['wp_framework_current_screen'] = null;

        WP_Mock::userFunction( 'is_front_page', [ 'return' => false ] );
        WP_Mock::userFunction( 'is_home', [ 'return' => false ] );
        WP_Mock::userFunction( 'is_search', [ 'return' => false ] );
        WP_Mock::userFunction( 'is_archive', [ 'return' => false ] );
        WP_Mock::userFunction( 'is_singular', [ 'return' => false ] );
        WP_Mock::userFunction( 'is_404', [ 'return' => false ] );
        WP_Mock::userFunction( 'wp_doing_ajax', [ 'return' => false ] );
        WP_Mock::userFunction( 'wp_doing_cron', [ 'return' => false ] );
    }

    /**
     * @covers \Bmd\WPFramework\Providers\Context::getContext
     */
    public function testGetContextReturnsEditorAdminChainForBlockEditor(): void
    {
        $this->mockDefaultContextConditionals();

        $GLOBALS['wp_framework_is_admin'] = true;
        $GLOBALS['wp_framework_current_screen'] = (object) [ 'base' => 'post' ];

        $provider = new Context( 'bmd_wp_framework' );

        $this->assertSame( [ 'EDITOR', 'ADMIN' ], $provider->getContext() );
    }

    /**
     * @covers \Bmd\WPFramework\Providers\Context::getContext
     */
    public function testGetContextReturnsAdminChainForAdminArea(): void
    {
        $this->mockDefaultContextConditionals();

        $GLOBALS['wp_framework_is_admin'] = true;

        $provider = new Context( 'bmd_wp_framework' );

        $this->assertSame( [ 'ADMIN' ], $provider->getContext() );
    }

    /**
     * @covers \Bmd\WPFramework\Providers\Context::getContext
     */
    public function testGetContextReturnsFrontendChainByDefault(): void
    {
        $this->mockDefaultContextConditionals();

        $provider = new Context( 'bmd_wp_framework' );

        $this->assertSame( [ 'FRONTEND' ], $provider->getContext() );
    }

    /**
     * @covers \Bmd\WPFramework\Providers\Context::getContext
     */
    public function testGetContextReturnsAjaxChain(): void
    {
        $this->mockDefaultContextConditionals();

        WP_Mock::userFunction( 'wp_doing_ajax', [ 'return' => true ] );

        $provider = new Context( 'bmd_wp_framework' );

        $this->assertSame( [ 'AJAX' ], $provider->getContext() );
    }

    /**
     * @covers \Bmd\WPFramework\Providers\Context::getContext
     */
    public function testGetContextReturnsCronChain(): void
    {
        $this->mockDefaultContextConditionals();

        WP_Mock::userFunction( 'wp_doing_cron', [ 'return' => true ] );

        $provider = new Context( 'bmd_wp_framework' );

        $this->assertSame( [ 'CRON' ], $provider->getContext() );
    }

    /**
     * @covers \Bmd\WPFramework\Providers\Context::dispatch
     */
    public function testDispatchTriggersContextHandlerActionWithArray(): void
    {
        $provider = $this->getMockBuilder( Context::class )
            ->setConstructorArgs( [ 'bmd_wp_framework' ] )
            ->onlyMethods( [ 'getContext' ] )
            ->getMock();

        $provider->expects( $this->once() )
            ->method( 'getContext' )
            ->willReturn( [ 'ADMIN' ] );

        WP_Mock::expectAction( 'bmd_wp_framework_dispatch_context_handler', [ 'ADMIN' ] );

        $provider->dispatch();
    }

    /**
     * @covers \Bmd\WPFramework\Providers\Context::dispatch
     */
    public function testDispatchAlwaysFiresAction(): void
    {
        $provider = $this->getMockBuilder( Context::class )
            ->setConstructorArgs( [ 'bmd_wp_framework' ] )
            ->onlyMethods( [ 'getContext' ] )
            ->getMock();

        $provider->expects( $this->once() )
            ->method( 'getContext' )
            ->willReturn( [ 'FRONTEND' ] );

        WP_Mock::expectAction( 'bmd_wp_framework_dispatch_context_handler', [ 'FRONTEND' ] );

        $provider->dispatch();
    }
}
