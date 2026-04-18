<?php

/**
 * Admin Context Handler Definition
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

use Bmd\WPFramework\Abstracts;
/**
 * Admin context handler
 *
 * Handles WordPress admin area functionality and asset loading
 *
 * @subpackage Context
 */
class Admin extends Abstracts\ContextHandler
{
    /**
     * Enqueue admin styles and JS bundles
     *
     * Loads the main admin JavaScript and CSS files for the WordPress admin area
     *
     * @return void
     */
    public function enqueueAssets(): void
    {
        // do_action( 'qm/debug', $this );
        $this->enqueueScript(handle: "{$this->package}-admin", path: 'dist/build/scripts/admin.bundle.js');
        $this->enqueueStyle(handle: "{$this->package}-admin", path: 'dist/build/admin.css');
    }
    /**
     * Enqueue assets for the block editor
     *
     * @return void
     */
    public function enqueueEditorAssets(): void
    {
        $this->enqueueScript(handle: "{$this->package}-editor", path: 'dist/build/editor.js');
        $this->enqueueStyle(handle: "{$this->package}-editor", path: 'dist/build/editor.css');
    }
}
