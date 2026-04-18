<?php

/**
 * File Path Resolver Service Test
 *
 * PHP Version 8.2
 *
 * @package mwf_cornerstone
 * @subpackage PHPUnit/Tests/Services
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */
namespace Mwf\Cornerstone\PHPUnit\Models;

use Mwf\Cornerstone\Models\Taxonomies as Taxonomy;
use WP_Mock\Tools\TestCase as TestCase;
final class TaxonomyTest extends TestCase
{
    const TAXONOMIES = [Taxonomy\PageCategory::class, Taxonomy\PageTag::class];
    /**
     * Test getter functions
     *
     * @covers Mwf\Cornerstone\Models\Taxonomies::getName,
     *         Mwf\Cornerstone\Models\Taxonomies::getPostTypes,
     * @return void
     */
    public function testGetters(): void
    {
        foreach (self::TAXONOMIES as $tax_class) {
            $taxonomy = new $tax_class();
            $this->assertIsString($taxonomy->getName());
            $this->assertIsArray($taxonomy->getPostTypes());
        }
    }
    /**
     * Test the definition method
     *
     * @covers Mwf\Cornerstone\Models\Taxonomies::getDefinition
     * @return void
     */
    public function testDefinition(): void
    {
        foreach (self::TAXONOMIES as $tax_class) {
            $taxonomy = new $tax_class();
            $definition = $taxonomy->getDefinition();
            $this->assertIsArray($definition);
            $this->assertArrayHasKey('labels', $definition);
            $this->assertArrayHasKey('post_types', $definition);
        }
    }
}
