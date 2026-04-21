<?php
/**
 * ContextController tests.
 *
 * @package WPFramework
 * @subpackage PHPUnit/Tests/Controllers
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\PHPUnit\Controllers;

use Bmd\WPFramework\Context;
use Bmd\WPFramework\Controllers\ContextController;
use Bmd\WPFramework\Interfaces;
use Bmd\WPFramework\Main;
use Bmd\WPFramework\Services\ServiceLocator;
use WP_Mock;
use WP_Mock\Tools\TestCase;

/**
 * Test suite for ContextController.
 */
final class ContextControllerTest extends TestCase
{
    /**
     * Reset static locator between tests.
     */
    public function tearDown(): void
    {
        $reflection = new \ReflectionClass( Main::class );
        $locatorProperty = $reflection->getProperty( 'service_locator' );
        $locatorProperty->setValue( null, null );

        parent::tearDown();
    }

    /**
     * @covers \Bmd\WPFramework\Controllers\ContextController::getServiceDefinitions
     */
    public function testGetServiceDefinitionsReturnsCoreContextHandlers(): void
    {
        $definitions = ContextController::getServiceDefinitions();

        $this->assertArrayHasKey( Context\Frontend::class, $definitions );
        $this->assertArrayHasKey( Context\Admin::class, $definitions );
        $this->assertArrayHasKey( Context\Login::class, $definitions );
    }

    /**
     * @covers \Bmd\WPFramework\Controllers\ContextController::mountActions
     */
    public function testMountActionsRegistersExpectedHooks(): void
    {
        $controller = new ContextController( 'bmd_wp_framework' );

        WP_Mock::expectActionAdded( 'bmd_wp_framework_dispatch_context_handler', [ $controller, 'loadContextHandler' ] );
        WP_Mock::expectActionAdded( 'bmd_wp_framework_mount_context_handler', [ $controller, 'mountContextHandler' ] );

        $controller->mountActions();

        $this->addToAssertionCount( 1 );
    }

    /**
     * @covers \Bmd\WPFramework\Controllers\ContextController::loadContextHandler
     */
    public function testLoadContextHandlerSkipsNonContextHandlerClass(): void
    {
        $controller = new ContextController( 'bmd_wp_framework' );
        $controller->loadContextHandler( \stdClass::class );

        $this->addToAssertionCount( 1 );
    }

    /**
     * @covers \Bmd\WPFramework\Controllers\ContextController::loadContextHandler
     */
    public function testLoadContextHandlerDispatchesResolvedHandler(): void
    {
        $resolvedHandler = $this->getMockBuilder( Context\Admin::class )
            ->disableOriginalConstructor()
            ->getMock();

        $locator = $this->getMockBuilder( ServiceLocator::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'getService' ] )
            ->getMock();

        $locator->expects( $this->once() )
            ->method( 'getService' )
            ->with( Context\Admin::class )
            ->willReturn( $resolvedHandler );

        $reflection = new \ReflectionClass( Main::class );
        $locatorProperty = $reflection->getProperty( 'service_locator' );
        $locatorProperty->setValue( null, $locator );

        WP_Mock::expectAction( 'bmd_wp_framework_mount_context_handler', $resolvedHandler );

        $controller = new ContextController( 'bmd_wp_framework' );
        $controller->loadContextHandler( Context\Admin::class );
    }

    /**
     * @covers \Bmd\WPFramework\Controllers\ContextController::mountContextHandler
     */
    public function testMountContextHandlerRoutesFrontendToFrontendMount(): void
    {
        $frontend = $this->getMockBuilder( Context\Frontend::class )
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder( ContextController::class )
            ->setConstructorArgs( [ 'bmd_wp_framework' ] )
            ->onlyMethods( [ 'mountFrontendHandler', 'mountAdminHandler', 'mountLoginHandler' ] )
            ->getMock();

        $controller->expects( $this->once() )
            ->method( 'mountFrontendHandler' )
            ->with( $frontend );

        $controller->expects( $this->never() )->method( 'mountAdminHandler' );
        $controller->expects( $this->never() )->method( 'mountLoginHandler' );

        $controller->mountContextHandler( $frontend );
    }

    /**
     * @covers \Bmd\WPFramework\Controllers\ContextController::mountAdminHandler
     */
    public function testMountAdminHandlerRegistersAdminAndEditorHooks(): void
    {
        $adminHandler = $this->getMockBuilder( Context\Admin::class )
            ->disableOriginalConstructor()
            ->getMock();

        WP_Mock::expectActionAdded( 'admin_enqueue_scripts', [ $adminHandler, 'enqueueAssets' ] );
        WP_Mock::expectActionAdded( 'enqueue_block_editor_assets', [ $adminHandler, 'enqueueEditorAssets' ] );

        $controller = new ContextController( 'bmd_wp_framework' );
        $controller->mountAdminHandler( $adminHandler );

        $this->addToAssertionCount( 1 );
    }

    /**
     * @covers \Bmd\WPFramework\Controllers\ContextController::mountLoginHandler
     */
    public function testMountLoginHandlerRegistersExpectedHooks(): void
    {
        $loginHandler = $this->getMockBuilder( Context\Login::class )
            ->disableOriginalConstructor()
            ->getMock();

        WP_Mock::expectActionAdded( 'login_enqueue_scripts', [ $loginHandler, 'enqueueAssets' ] );

        $controller = new ContextController( 'bmd_wp_framework' );
        $controller->mountLoginHandler( $loginHandler );

        $this->addToAssertionCount( 1 );
    }
}
