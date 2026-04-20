<?php
/**
 * Helper utility tests.
 *
 * @package WPFramework
 * @subpackage PHPUnit/Tests
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\PHPUnit;

use Bmd\WPFramework\Helpers;
use WP_Mock\Tools\TestCase;

trait ParentHelperTrait
{
}

trait ChildHelperTrait
{
}

interface HelperInterface
{
}

class HelperParentClass
{
    use ParentHelperTrait;
}

class HelperChildClass extends HelperParentClass implements HelperInterface
{
    use ChildHelperTrait;
}

/**
 * Test suite for Helpers.
 */
final class HelpersTest extends TestCase
{
    /**
     * @covers \Bmd\WPFramework\Helpers::className
     * @covers \Bmd\WPFramework\Helpers::classUses
     * @covers \Bmd\WPFramework\Helpers::uses
     * @covers \Bmd\WPFramework\Helpers::implements
     * @covers \Bmd\WPFramework\Helpers::getTraits
     * @covers \Bmd\WPFramework\Helpers::usesTrait
     */
    public function testClassInspectionHelpersReturnExpectedValues(): void
    {
        $instance = new HelperChildClass();

        $this->assertSame( HelperChildClass::class, Helpers::className( $instance ) );
        $this->assertSame( HelperChildClass::class, Helpers::className( HelperChildClass::class ) );
        $this->assertFalse( Helpers::className( 'MissingClass' ) );

        $this->assertTrue( Helpers::classUses( $instance, HelperParentClass::class ) );
        $this->assertTrue( Helpers::classUses( $instance, HelperInterface::class ) );
        $this->assertTrue( Helpers::classUses( $instance, ChildHelperTrait::class ) );
        $this->assertFalse( Helpers::classUses( 'MissingClass', HelperInterface::class ) );

        $this->assertTrue( Helpers::uses( $instance, ChildHelperTrait::class ) );
        $this->assertFalse( Helpers::uses( $instance, \ArrayAccess::class ) );

        $this->assertTrue( Helpers::implements( $instance, HelperInterface::class ) );
        $this->assertFalse( Helpers::implements( 'MissingClass', HelperInterface::class ) );

        $traits = Helpers::getTraits( $instance );
        $this->assertContains( ChildHelperTrait::class, $traits );
        $this->assertContains( ParentHelperTrait::class, $traits );

        $this->assertSame( [ ChildHelperTrait::class => ChildHelperTrait::class ], Helpers::usesTrait( $instance ) );
        $this->assertSame( [], Helpers::usesTrait( 'MissingClass' ) );
    }

    /**
     * @covers \Bmd\WPFramework\Helpers::isList
     */
    public function testIsListRecognizesListsAndAssociativeArrays(): void
    {
        $this->assertTrue( Helpers::isList( [] ) );
        $this->assertTrue( Helpers::isList( [ 'a', 'b' ] ) );
        $this->assertFalse( Helpers::isList( [ 'a' => 'b' ] ) );
        $this->assertFalse( Helpers::isList( 'not-an-array' ) );
    }

    /**
     * @covers \Bmd\WPFramework\Helpers::truthyFalsy
     */
    public function testTruthyFalsyNormalizesSupportedValues(): void
    {
        $this->assertTrue( Helpers::truthyFalsy( true ) );
        $this->assertTrue( Helpers::truthyFalsy( 'YES' ) );
        $this->assertTrue( Helpers::truthyFalsy( 1 ) );
        $this->assertFalse( Helpers::truthyFalsy( 'false' ) );
        $this->assertFalse( Helpers::truthyFalsy( 0 ) );
        $this->assertFalse( Helpers::truthyFalsy( null ) );
    }

    /**
     * @covers \Bmd\WPFramework\Helpers::slugify
     * @covers \Bmd\WPFramework\Helpers::hyphenate
     */
    public function testStringNormalizationHelpersReturnExpectedFormats(): void
    {
        $this->assertSame( 'my_http_class_name', Helpers::slugify( 'MyHTTP/Class Name' ) );
        $this->assertSame( 'my-class-name', Helpers::hyphenate( 'My_Class Name' ) );
    }

    /**
     * @covers \Bmd\WPFramework\Helpers::arrayMerge
     */
    public function testArrayMergeRecursivelyMergesNestedArrays(): void
    {
        $result = Helpers::arrayMerge(
            [
                'top' => [
                    'left' => 'a',
                    'keep' => 'x',
                ],
                'plain' => 'value',
            ],
            [
                'top' => [
                    'right' => 'b',
                    'keep' => 'y',
                ],
                'plain' => 'override',
            ]
        );

        $this->assertSame(
            [
                'top' => [
                    'left' => 'a',
                    'keep' => 'y',
                    'right' => 'b',
                ],
                'plain' => 'override',
            ],
            $result
        );
    }
}