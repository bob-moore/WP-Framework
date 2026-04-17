<?php
/**
 * Frontend Context Handler Definition
 *
 * PHP Version 8.2
 *
 * @package Bmd_WPFramework
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @since   1.0.0
 */

namespace Bmd\WPFramework\Context;

use Bmd\WPFramework\Core\Abstracts,
	Bmd\WPFramework\Services;

/**
 * Frontend context handler
 *
 * Handles frontend-specific functionality and asset loading
 *
 * @subpackage Context
 */
class Frontend extends Abstracts\ContextHandler
{
	/**
	 * Enqueue frontend styles and JS bundles
	 *
	 * Loads the main frontend JavaScript and CSS files
	 *
	 * @return void
	 */
	public function enqueueAssets(): void
	{
		$this->enqueueScript(
			handle: "{$this->package}-frontend",
			path: 'dist/build/frontend.js'
		);
		$this->enqueueStyle(
			handle: "{$this->package}-frontend",
			path: 'dist/build/frontend.css'
		);
	}
}
