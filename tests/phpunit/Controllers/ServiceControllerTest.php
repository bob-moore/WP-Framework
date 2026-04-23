<?php
/**
 * ServiceController tests.
 *
 * @package WPFramework
 * @subpackage PHPUnit/Tests/Controllers
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\PHPUnit\Controllers;

use Bmd\WPFramework\Controllers\ServiceController;
use Bmd\WPFramework\Services;
use DI\Definition\Helper\AutowireDefinitionHelper;
use WP_Mock\Tools\TestCase;

/**
 * Test suite for ServiceController.
 */
final class ServiceControllerTest extends TestCase
{
    /**
     * @covers \Bmd\WPFramework\Controllers\ServiceController::getServiceDefinitions
     */
    public function testGetServiceDefinitionsIncludesCoreServices(): void
    {
        $definitions = ServiceController::getServiceDefinitions();

        $expected = [
            Services\ScriptLoader::class,
            Services\StyleLoader::class,
            Services\FilePathResolver::class,
            Services\UrlResolver::class,
        ];

        foreach ( $expected as $service ) {
            $this->assertArrayHasKey( $service, $definitions );
            $this->assertInstanceOf( AutowireDefinitionHelper::class, $definitions[ $service ] );
        }
    }
}