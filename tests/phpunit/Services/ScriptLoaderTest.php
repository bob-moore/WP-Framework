<?php
/**
 * ScriptLoader service tests.
 *
 * Verifies registration/enqueue behavior and script asset handling.
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
use Bmd\WPFramework\Services\ScriptLoader;
use Bmd\WPFramework\Services\UrlResolver;
use WP_Mock;
use WP_Mock\Tools\TestCase as TestCase;

/**
 * Test suite for the ScriptLoader service.
 */
final class ScriptLoaderTest extends TestCase
{
    use ModuleTrait;

    /**
     * Fully qualified class name used by shared module trait assertions.
     *
     * @var class-string<ScriptLoader>
     */
    const TEST_CLASS = ScriptLoader::class;

    /**
     * Ensures register() uses .asset.php dependencies when available.
     *
     * @covers \Bmd\WPFramework\Services\ScriptLoader::register
     */
    public function testRegisterUsesAssetFileDependencies(): void
    {
        $tmpScript    = tempnam( sys_get_temp_dir(), 'mwf-script-' );
        $assetFile    = str_replace( '.js', '', $tmpScript ) . '.asset.php';
        $scriptAssets = [
            'dependencies' => [ 'wp-dom-ready', 'wp-i18n' ],
            'version'      => '1.5.0',
        ];

        file_put_contents( $tmpScript, 'console.log("test");' );
        file_put_contents(
            $assetFile,
            '<?php return ' . var_export( $scriptAssets, true ) . ';'
        );

        $handle       = 'My_App_Script';
        $src          = 'assets/js/app.js';
        $dependencies = [];
        $expectedVer  = '1.5.0';

        $urlResolver      = $this->getMockBuilder( UrlResolver::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'resolve' ] )
            ->getMock();
        $filePathResolver = $this->getMockBuilder( FilePathResolver::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'resolve' ] )
            ->getMock();

        $filePathResolver->expects( $this->exactly( 2 ) )
            ->method( 'resolve' )
            ->willReturnMap( [
                [ $src, $tmpScript ],
                [ str_replace( '.js', '.asset.php', $src ), $assetFile ],
            ] );

        $urlResolver->expects( $this->once() )
            ->method( 'resolve' )
            ->with( $src )
            ->willReturn( 'https://example.com/plugin/assets/js/app.js' );

        WP_Mock::userFunction(
            'wp_register_script',
            [
                'times' => 1,
                'args'  => [
                    'my-app-script',
                    'https://example.com/plugin/assets/js/app.js',
                    $scriptAssets['dependencies'],
                    $expectedVer,
                    true,
                ],
            ]
        );

        $loader = new ScriptLoader( $urlResolver, $filePathResolver, 'mwf_blocks' );
        $loader->register( $handle, $src, $dependencies );

        unlink( $tmpScript );
        unlink( $assetFile );
    }

    /**
     * Ensures register() bails when source is not a valid URL and not a local file.
     *
     * @covers \Bmd\WPFramework\Services\ScriptLoader::register
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
            ->willReturn( '/definitely/not/a/file.js' );

        $urlResolver->expects( $this->never() )
            ->method( 'resolve' );

        WP_Mock::userFunction( 'wp_register_script', [ 'times' => 0 ] );

        $loader = new ScriptLoader( $urlResolver, $filePathResolver, 'mwf_blocks' );
        $loader->register( 'bad_handle', 'not-a-url' );

        $this->addToAssertionCount( 1 );
    }

    /**
     * Ensures enqueue() passes normalized data to wp_enqueue_script() for remote URLs.
     *
     * @covers \Bmd\WPFramework\Services\ScriptLoader::enqueue
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
            ->with( 'https://cdn.example.com/lib.js' )
            ->willReturn( '/not/a/local/file.js' );

        $urlResolver->expects( $this->never() )
            ->method( 'resolve' );

        WP_Mock::userFunction(
            'wp_enqueue_script',
            [
                'times' => 1,
                'args'  => [
                    'external-lib',
                    'https://cdn.example.com/lib.js',
                    [ 'jquery' ],
                    '2.1.0',
                    false,
                ],
            ]
        );

        $loader = new ScriptLoader( $urlResolver, $filePathResolver, 'mwf_blocks' );
        $loader->enqueue( 'External_Lib', 'https://cdn.example.com/lib.js', [ 'jquery' ], '2.1.0', false );
    }

    /**
     * Ensures empty local files are skipped.
     *
     * @covers \Bmd\WPFramework\Services\ScriptLoader::register
     */
    public function testRegisterSkipsEmptyLocalFile(): void
    {
        $tmpFile = tempnam( sys_get_temp_dir(), 'mwf-empty-script-' );
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
            ->with( 'assets/js/empty.js' )
            ->willReturn( $tmpFile );

        $urlResolver->expects( $this->never() )
            ->method( 'resolve' );

        WP_Mock::userFunction( 'wp_register_script', [ 'times' => 0 ] );

        $loader = new ScriptLoader( $urlResolver, $filePathResolver, 'mwf_blocks' );
        $loader->register( 'empty_script', 'assets/js/empty.js' );

        unlink( $tmpFile );
        $this->addToAssertionCount( 1 );
    }

    /**
     * Ensures enqueue() defaults to footer=true for local scripts.
     *
     * @covers \Bmd\WPFramework\Services\ScriptLoader::enqueue
     */
    public function testEnqueueDefaultsToFooter(): void
    {
        $tmpScript = tempnam( sys_get_temp_dir(), 'mwf-script-' );
        file_put_contents( $tmpScript, 'console.log("test");' );

        $urlResolver      = $this->getMockBuilder( UrlResolver::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'resolve' ] )
            ->getMock();
        $filePathResolver = $this->getMockBuilder( FilePathResolver::class )
            ->disableOriginalConstructor()
            ->onlyMethods( [ 'resolve' ] )
            ->getMock();

        $filePathResolver->expects( $this->exactly( 2 ) )
            ->method( 'resolve' )
            ->willReturnMap( [
                [ 'assets/js/footer.js', $tmpScript ],
                [ 'assets/js/footer.asset.php', '/not/found.asset.php' ],
            ] );

        $urlResolver->expects( $this->once() )
            ->method( 'resolve' )
            ->with( 'assets/js/footer.js' )
            ->willReturn( 'https://example.com/plugin/assets/js/footer.js' );

        WP_Mock::userFunction(
            'wp_enqueue_script',
            [
                'times' => 1,
                'args'  => [
                    'footer-script',
                    'https://example.com/plugin/assets/js/footer.js',
                    [],
                    filemtime( $tmpScript ),
                    true,
                ],
            ]
        );

        $loader = new ScriptLoader( $urlResolver, $filePathResolver, 'mwf_blocks' );
        // No in_footer argument passed, should default to true.
        $loader->enqueue( 'Footer_Script', 'assets/js/footer.js' );

        unlink( $tmpScript );
    }
}
