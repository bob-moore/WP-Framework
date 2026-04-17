<?php
/**
 * Editor Context Handler Definition
 *
 * PHP Version 8.2
 *
 * @package Bmd_WPFramework
 * @author  Bob Moore <bob.moore@midwestfamilymadison.com>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.midwestfamilymadison.com
 * @since   1.0.0
 */

namespace Bmd\WPFramework\Context;

/**
 * Editor context class
 *
 * @subpackage Context
 */
class Editor extends Admin
{
	/**
	 * Enqueue block editor styles and JS bundles
	 *
	 * @return void
	 */
	public function enqueueAssets(): void
	{
		$this->enqueueScript(
			handle: "{$this->package}-editor",
			path: 'dist/build/editor.js'
		);

		$this->enqueueStyle(
			handle: "{$this->package}-editor",
			path: 'dist/build/editor.css'
		);

		parent::enqueueAssets();
	}
}
