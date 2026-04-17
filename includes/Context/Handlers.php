<?php
/**
 * Context Type Enum
 *
 * Defines the available context types for the application
 *
 * @package Bmd_WPFramework
 * @author  Bob Moore <bob.moore@midwestfamilymadison.com>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.midwestfamilymadison.com
 * @since   1.0.0
 */

namespace Bmd\WPFramework\Context;

/**
 * Context Type Enumeration
 *
 * Defines all available context handlers for different WordPress page types
 *
 * @subpackage Context
 */
enum Handlers: string
{
	/** Archive page handler */
	case ARCHIVE = Archive::class;
	/** Search results page handler */
	case SEARCH = Search::class;
	/** Blog posts page handler */
	case BLOG = Blog::class;
	/** Single post/page handler */
	case SINGLE = Single::class;
	/** WordPress admin area handler */
	case ADMIN = Admin::class;
	/** Block editor handler */
	case EDITOR = Editor::class;
	/** Front page handler */
	case FRONTPAGE = Frontpage::class;
	/** General frontend handler */
	case FRONTEND = Frontend::class;
	/** 404 error page handler */
	case ERROR404 = Error404::class;
	/** Login page handler */
	case LOGIN = Login::class;
	/** No specific handler */
	case NONE = '';
}
