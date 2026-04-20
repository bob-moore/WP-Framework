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
     * Tracks mount calls for assertions when needed.
     *
     * @var int
     */
    public int $mount_calls = 0;

    /**
     * @return void
     */
    public function mount(): void
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
     * @covers \Bmd\WPFramework\Traits\Mountable::onMount
     */
    public function testOnMountRegistersAndFiresMountActionWhenNotMounted(): void
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

        WP_Mock::expectActionAdded( $hook, [ $instance, 'mount' ], 5 );
        WP_Mock::expectAction( $hook, $instance );

        $instance->onMount();

        $this->addToAssertionCount( 1 );
    }

    /**
     * @covers \Bmd\WPFramework\Traits\Mountable::onMount
     */
    public function testOnMountSkipsRegistrationWhenAlreadyMounted(): void
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

        WP_Mock::expectActionNotAdded( $hook, [ $instance, 'mount' ], 5 );

        $instance->onMount();

        $this->addToAssertionCount( 1 );
    }
}