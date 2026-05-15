<?php
/**
 * Action Loader trait definition file
 *
 * PHP Version 8.2
 *
 * @package Bmd_WPFramework
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\Traits;

use Bmd\WPFramework\Helpers;
/**
 * Action Loader trait
 *
 * @subpackage Traits
 */
trait Mountable
{
	/**
	 * Name of class converted to usable slug
	 *
	 * Fully qualified class name, converted to lowercase with forward slashes.
	 *
	 * @var string
	 */
	protected string $class_slug = '';
	/**
	 * Set the class slug
	 *
	 * @param string $slug : slug to set.
	 *
	 * @return void
	 */
	public function setClassSlug( string $slug ): void
	{
		$this->class_slug = Helpers::slugify( static::class );
	}
	/**
	 * Get the class slug
	 *
	 * @return string
	 */
	public function getClassSlug(): string
	{
		if ( empty( $this->class_slug ) ) {
			$slug = Helpers::slugify( static::class );
			$this->setClassSlug( $slug );
		}
		return $this->class_slug;
	}
	/**
	 * Check if class has already mounted an instance
	 *
	 * @return int
	 */
	public function hasMounted(): int
	{
		return did_action( "{$this->getClassSlug()}_mount" );
	}
	/**
	 * Override to implement mounting logic
	 *
	 * @return void
	 */
	public function onMount(): void
	{
	}
	/**
	 * Entry point — registers and fires the mount action
	 *
	 * @return void
	 */
	public function mount(): void
	{
		if ( ! $this->hasMounted() ) {
			add_action( "{$this->getClassSlug()}_mount", [ $this, 'onMount' ], 5 );
			do_action( "{$this->getClassSlug()}_mount", $this );
		}
	}
}
