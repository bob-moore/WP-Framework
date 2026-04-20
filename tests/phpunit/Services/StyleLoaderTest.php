<?php
/**
 * StyleLoader service tests.
 *
 * Verifies registration/enqueue behavior and style normalization.
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
use Bmd\WPFramework\Services\FilePathResolver;
use Bmd\WPFramework\Services\StyleLoader;
use Bmd\WPFramework\Services\UrlResolver;
use WP_Mock;
use WP_Mock\Tools\TestCase as TestCase;

/**
 * Test suite for the StyleLoader service.
 */
final class StyleLoaderTest extends TestCase
{
    use ModuleTrait;

    /**
     * Fully qualified class name used by shared module trait assertions.
     *
     * @var class-string<StyleLoader>
     */
    const TEST_CLASS = StyleLoader::class;

    /**
     * Ensures register() builds normalized style data for local files.
     *
     * @covers \Bmd\WPFramework\Services\StyleLoader::register
     */
    public function testRegisterUsesResolvedLocalFileData(): void
    {
        $tmpFile = tempnam( sys_get_temp_dir(), 'mwf-style-' );
        file_put_contents( $tmpFile, 'body { color: #111; }' );

        $handle       = 'My_Handle/Name';
        $src          = 'assets/css/main.css';
        $dependencies = [ 'dep-a' ];
        $expectedVer  = filemtime( $tmpFile );

        $urlResolver      = $this->getMockBuilder( UrlResolver::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'resolve' ] )
            ->getMock();
        $filePathResolver = $this->getMockBuilder( FilePathResolver::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'resolve' ] )
            ->getMock();

        $filePathResolver->expects( $this->once() )
            ->method( 'resolve' )
            ->with( $src )
            ->willReturn( $tmpFile );

        $urlResolver->expects( $this->once() )
            ->method( 'resolve' )
            ->with( $src )
            ->willReturn( 'https://example.com/plugin/assets/css/main.css' );

        WP_Mock::userFunction(
            'wp_register_style',
            [
                'times' => 1,
                'args'  => [
                    'my-handle-name',
                    'https://example.com/plugin/assets/css/main.css',
                    $dependencies,
                    $expectedVer,
                    'all',
                ],
            ]
        );

        $loader = new StyleLoader( $urlResolver, $filePathResolver, 'mwf_blocks' );
        $loader->register( $handle, $src, $dependencies );

        unlink( $tmpFile );
    }

    /**
     * Ensures register() bails when source is not a valid URL and not a local file.
     *
     * @covers \Bmd\WPFramework\Services\StyleLoader::register
     */
    public function testRegisterSkipsInvalidSource(): void
    {
        $urlResolver      = $this->getMockBuilder( UrlResolver::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'resolve' ] )
            ->getMock();
        $filePathResolver = $this->getMockBuilder( FilePathResolver::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'resolve' ] )
            ->getMock();

        $filePathResolver->expects( $this->once() )
            ->method( 'resolve' )
            ->with( 'not-a-url' )
            ->willReturn( '/definitely/not/a/file.css' );

        $urlResolver->expects( $this->never() )
            ->method( 'resolve' );

        WP_Mock::userFunction( 'wp_register_style', [ 'times' => 0 ] );

        $loader = new StyleLoader( $urlResolver, $filePathResolver, 'mwf_blocks' );
        $loader->register( 'bad_handle', 'not-a-url' );

        $this->addToAssertionCount( 1 );
    }

    /**
     * Ensures enqueue() passes normalized data to wp_enqueue_style() for remote URLs.
     *
     * @covers \Bmd\WPFramework\Services\StyleLoader::enqueue
     */
    public function testEnqueueUsesRemoteUrlData(): void
    {
        $urlResolver      = $this->getMockBuilder( UrlResolver::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'resolve' ] )
            ->getMock();
        $filePathResolver = $this->getMockBuilder( FilePathResolver::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'resolve' ] )
            ->getMock();

        $filePathResolver->expects( $this->once() )
            ->method( 'resolve' )
            ->with( 'https://cdn.example.com/theme.css' )
            ->willReturn( '/not/a/local/file.css' );

        $urlResolver->expects( $this->never() )
            ->method( 'resolve' );

        WP_Mock::userFunction(
            'wp_enqueue_style',
            [
                'times' => 1,
                'args'  => [
                    'theme-styles',
                    'https://cdn.example.com/theme.css',
                    [ 'dep-core' ],
                    '1.2.3',
                    'screen',
                ],
            ]
        );

        $loader = new StyleLoader( $urlResolver, $filePathResolver, 'mwf_blocks' );
        $loader->enqueue( 'Theme_Styles', 'https://cdn.example.com/theme.css', [ 'dep-core' ], '1.2.3', 'screen' );
    }

    /**
     * Ensures empty local files are skipped.
     *
     * @covers \Bmd\WPFramework\Services\StyleLoader::register
     */
    public function testRegisterSkipsEmptyLocalFile(): void
    {
        $tmpFile = tempnam( sys_get_temp_dir(), 'mwf-empty-style-' );
        file_put_contents( $tmpFile, '' );

        $urlResolver      = $this->getMockBuilder( UrlResolver::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'resolve' ] )
            ->getMock();
        $filePathResolver = $this->getMockBuilder( FilePathResolver::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'resolve' ] )
            ->getMock();

        $filePathResolver->expects( $this->once() )
            ->method( 'resolve' )
            ->with( 'assets/css/empty.css' )
            ->willReturn( $tmpFile );

        $urlResolver->expects( $this->never() )
            ->method( 'resolve' );

        WP_Mock::userFunction( 'wp_register_style', [ 'times' => 0 ] );

        $loader = new StyleLoader( $urlResolver, $filePathResolver, 'mwf_blocks' );
        $loader->register( 'empty_style', 'assets/css/empty.css' );

        unlink( $tmpFile );
        $this->addToAssertionCount( 1 );
    }
}
