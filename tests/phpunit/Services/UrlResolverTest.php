<?php
/**
 * UrlResolver service tests.
 *
 * Verifies URL normalization behavior for resolver output.
 *
 * @package WPFramework
 * @subpackage PHPUnit/Tests/Services
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\PHPUnit\Services;

use Bmd\WPFramework\PHPUnit\Traits\ModuleTrait;
use Bmd\WPFramework\Services\UrlResolver;
use WP_Mock\Tools\TestCase as TestCase;

/**
 * Test suite for the UrlResolver service.
 */
final class UrlResolverTest extends TestCase
{
    use ModuleTrait;

    /**
     * Fully qualified class name used by shared module trait assertions.
     *
     * @var class-string<UrlResolver>
     */
    const TEST_CLASS = UrlResolver::class;

    /**
     * Confirms resolve() returns normalized URLs for common input patterns.
     *
     * @covers \Bmd\WPFramework\Services\UrlResolver::resolve
     */
    public function testResolve(): void
    {
        $resolver = new UrlResolver( 'https://example.com/plugin/', 'mwf_blocks' );

        // No append argument should return the base URL without a trailing slash.
        $this->assertSame( 'https://example.com/plugin', $resolver->resolve() );

        // Appended segments should normalize leading and trailing slashes.
        $this->assertSame( 'https://example.com/plugin/assets/js/app.js', $resolver->resolve( 'assets/js/app.js' ) );
        $this->assertSame( 'https://example.com/plugin/assets/js/app.js', $resolver->resolve( '/assets/js/app.js' ) );
        $this->assertSame( 'https://example.com/plugin/assets/js/app.js', $resolver->resolve( 'assets/js/app.js/' ) );
    }
}
