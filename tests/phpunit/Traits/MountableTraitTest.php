<?php
/**
 * Mountable trait tests.
 *
 * @package WPFramework
 * @subpackage PHPUnit/Tests/Traits
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\PHPUnit\Traits;

use Bmd\WPFramework\Abstracts\Module;
use Bmd\WPFramework\Interfaces;
use Bmd\WPFramework\Traits\Mountable;
use WP_Mock;
use WP_Mock\Tools\TestCase;

/**
 * Minimal harness for testing the Mountable trait.
 */
final class MountableTraitHarness extends Module implements Interfaces\Mountable
{
    use Mountable;

    /**
     * Tracks onMount calls for assertions when needed.
     *
     * @var int
     */
    public int $mount_calls = 0;

    /**
     * @return void
     */
    public function onMount(): void
    {
        $this->mount_calls++;
    }
}

/**
 * Test suite for the Mountable trait.
 */
final class MountableTraitTest extends TestCase
{
    /**
     * @covers \Bmd\WPFramework\Traits\Mountable::getClassSlug
     */
    public function testGetClassSlugLazilyBuildsNormalizedSlug(): void
    {
        $instance = new MountableTraitHarness( 'bmd_wp_framework' );

        $this->assertSame(
            'bmd_wpframework_phpunit_traits_mountable_trait_harness',
            $instance->getClassSlug()
        );
    }

    /**
     * @covers \Bmd\WPFramework\Traits\Mountable::hasMounted
     */
    public function testHasMountedChecksMountActionCount(): void
    {
        $instance = new MountableTraitHarness( 'bmd_wp_framework' );
        $hook = $instance->getClassSlug() . '_mount';

        WP_Mock::userFunction(
            'did_action',
            [
                'times' => 1,
                'args'  => [ $hook ],
                'return' => 2,
            ]
        );

        $this->assertSame( 2, $instance->hasMounted() );
    }

    /**
     * @covers \Bmd\WPFramework\Traits\Mountable::mount
     */
    public function testMountRegistersAndFiresMountActionWhenNotMounted(): void
    {
        $instance = new MountableTraitHarness( 'bmd_wp_framework' );
        $hook = $instance->getClassSlug() . '_mount';

        WP_Mock::userFunction(
            'did_action',
            [
                'times' => 1,
                'args'  => [ $hook ],
                'return' => 0,
            ]
        );

        WP_Mock::expectActionAdded( $hook, [ $instance, 'onMount' ], 5 );
        WP_Mock::expectAction( $hook, $instance );

        $instance->mount();

        $this->addToAssertionCount( 1 );
    }

    /**
     * @covers \Bmd\WPFramework\Traits\Mountable::mount
     */
    public function testMountSkipsRegistrationWhenAlreadyMounted(): void
    {
        $instance = new MountableTraitHarness( 'bmd_wp_framework' );
        $hook = $instance->getClassSlug() . '_mount';

        WP_Mock::userFunction(
            'did_action',
            [
                'times' => 1,
                'args'  => [ $hook ],
                'return' => 1,
            ]
        );

        WP_Mock::expectActionNotAdded( $hook, [ $instance, 'onMount' ], 5 );

        $instance->mount();

        $this->addToAssertionCount( 1 );
    }
}