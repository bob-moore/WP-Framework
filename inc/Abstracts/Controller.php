<?php
/**
 * MountableComponent definition file
 *
 * PHP Version 8.2
 *
 * @package Bmd_WPFramework
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\Abstracts;

use Bmd\WPFramework\ {
	Interfaces,
	Traits
};
use DI\Attribute\Inject;
/**
 * Abstract ActionModule class
 *
 * Action modules are modules that have actions and filters that need to be mounted.
 *
 * @subpackage Abstracts
 */
abstract class Controller extends Module implements Interfaces\Controller, Interfaces\Mountable
{
	use Traits\Mountable;
	use Traits\ActionLoader;
	use Traits\FilterLoader;

	/**
	 * Get definitions that should be added to the service container
	 *
	 * @return array<string, mixed>
	 */
	public static function getServiceDefinitions(): array
	{
		return [];
	}
	/**
	 * Mount actions and filters
	 *
	 * @return void
	 */
	public function onMount(): void
	{
		$this->mountActions();
		$this->mountFilters();
	}
}
