<?php
/**
 * ProviderController tests.
 *
 * @package WPFramework
 * @subpackage PHPUnit/Tests/Controllers
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\PHPUnit\Controllers;

use Bmd\WPFramework\Controllers\ProviderController;
use Bmd\WPFramework\Providers;
use DI\Definition\Helper\AutowireDefinitionHelper;
use WP_Mock;
use WP_Mock\Tools\TestCase;

/**
 * Test suite for ProviderController.
 */
final class ProviderControllerTest extends TestCase
{
    /**
     * @covers \Bmd\WPFramework\Controllers\ProviderController::getServiceDefinitions
     */
    public function testGetServiceDefinitionsIncludesContextProvider(): void
    {
        $definitions = ProviderController::getServiceDefinitions();

        $this->assertArrayHasKey( Providers\Context::class, $definitions );
        $this->assertInstanceOf( AutowireDefinitionHelper::class, $definitions[ Providers\Context::class ] );
    }

    /**
     * @covers \Bmd\WPFramework\Controllers\ProviderController::mountContext
     */
    public function testMountContextRegistersBothDispatchHooks(): void
    {
        $provider = $this->getMockBuilder( Providers\Context::class )
            ->disableOriginalConstructor()
            ->getMock();

        $controller = new ProviderController( 'bmd_wp_framework' );

        WP_Mock::expectActionAdded( 'wp', [ $provider, 'dispatch' ], 4 );
        WP_Mock::expectActionAdded( 'current_screen', [ $provider, 'dispatch' ], 4 );

        $controller->mountContext( $provider );

        $this->addToAssertionCount( 1 );
    }
}