<?php
/**
 * Frontend Context Handler Definition
 *
 * PHP Version 8.2
 *
 * @package bmd_wpframework
 * @author  Bob Moore <bob.moore@midwestfamilymadison.com>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.midwestfamilymadison.com
 * @since   1.0.0
 */

namespace Bmd\WPFramework\Context;

use Bmd\WPFramework\Core\Abstracts;


/**
 * Frontend context handler
 *
 * @subpackage Route
 */
class Login extends Abstracts\ContextHandler
{
	/**
	 * Enqueue styles and JS bundles
	 *
	 * @return void
	 */
	public function enqueueAssets(): void
	{
		$this->enqueueScript(
			handle: "{$this->package}-login",
			path: 'dist/build/login.js'
		);

		$this->enqueueStyle(
			handle: "{$this->package}-login",
			path: 'dist/build/login.css'
		);
	}
}
