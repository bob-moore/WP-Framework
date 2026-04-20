<?php
/**
 * Main class tests.
 *
 * @package WPFramework
 * @subpackage PHPUnit/Tests
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\PHPUnit;

use Bmd\WPFramework\Controllers;
use Bmd\WPFramework\Main;
use Bmd\WPFramework\Services\ServiceLocator;
use WP_Mock\Tools\TestCase;

/**
 * Test suite for the Main entrypoint class.
 */
final class MainTest extends TestCase
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
     * @covers \Bmd\WPFramework\Main::registerControllers
     */
    public function testRegisterControllersAddsExpectedDefinitions(): void
    {
        $locator = $this->getMockBuilder( ServiceLocator::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'addDefinitions' ] )
            ->getMock();

        $locator->expects( $this->once() )
            ->method( 'addDefinitions' )
            ->with( $this->callback( function ( array $definitions ): bool {
                return isset( $definitions[ Controllers\ServiceController::class ] )
                    && isset( $definitions[ Controllers\ContextController::class ] )
                    && isset( $definitions[ Controllers\ProviderController::class ] );
            } ) );

        $reflection = new \ReflectionClass( Main::class );
        $locatorProperty = $reflection->getProperty( 'service_locator' );
        $locatorProperty->setValue( null, $locator );

        $main = new Main();
        $main->registerControllers();
    }

    /**
     * @covers \Bmd\WPFramework\Main::mount
     */
    public function testMountBuildsAndMountsControllers(): void
    {
        $mountedServices = [];

        $locator = $this->getMockBuilder( ServiceLocator::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'addDefinitions', 'build', 'mountService' ] )
            ->getMock();

        $locator->expects( $this->exactly( 2 ) )
            ->method( 'addDefinitions' );

        $locator->expects( $this->once() )
            ->method( 'build' );

        $locator->expects( $this->exactly( 3 ) )
            ->method( 'mountService' )
            ->willReturnCallback( static function ( string $service ) use ( &$mountedServices ): void {
                $mountedServices[] = $service;
            } );

        $reflection = new \ReflectionClass( Main::class );
        $locatorProperty = $reflection->getProperty( 'service_locator' );
        $locatorProperty->setValue( null, $locator );

        $main = new Main();
        $main->mount();

        $this->assertSame(
            [
                Controllers\ServiceController::class,
                Controllers\ContextController::class,
                Controllers\ProviderController::class,
            ],
            $mountedServices
        );
    }

    /**
     * @covers \Bmd\WPFramework\Main::locateService
     */
    public function testLocateServiceReturnsNullWhenLocatorIsMissing(): void
    {
        $reflection = new \ReflectionClass( Main::class );
        $locatorProperty = $reflection->getProperty( 'service_locator' );
        $locatorProperty->setValue( null, null );

        $this->assertNull( Main::locateService( 'AnyService' ) );
    }

    /**
     * @covers \Bmd\WPFramework\Main::locateService
     */
    public function testLocateServiceFallsBackToNamespacedServiceName(): void
    {
        $resolved = new \stdClass();
        $resolvedServices = [];

        $locator = $this->getMockBuilder( ServiceLocator::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'getService' ] )
            ->getMock();

        $locator->expects( $this->exactly( 2 ) )
            ->method( 'getService' )
            ->willReturnCallback( static function ( string $service ) use ( &$resolvedServices, $resolved ) {
                $resolvedServices[] = $service;

                if ( 'ServiceLocator' === $service ) {
                    return new \WP_Error( 'not_found', 'Missing short name' );
                }

                return $resolved;
            } );

        $reflection = new \ReflectionClass( Main::class );
        $locatorProperty = $reflection->getProperty( 'service_locator' );
        $locatorProperty->setValue( null, $locator );

        $this->assertSame( $resolved, Main::locateService( 'ServiceLocator' ) );
        $this->assertSame(
            [
                'ServiceLocator',
                'Bmd\\WPFramework\\ServiceLocator',
            ],
            $resolvedServices
        );
    }
}
