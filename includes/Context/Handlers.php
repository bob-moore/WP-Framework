<?php

/**
 * Context Type Enum
 *
 * Defines the available context types for the application
 *
 * @package Bmd_WPFramework
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
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
enum Handlers : string
{
    /** WordPress admin area handler */
    case ADMIN = Admin::class;
    /** General frontend handler */
    case FRONTEND = Frontend::class;
    /** Login page handler */
    case LOGIN = Login::class;
    /** No specific handler */
    case NONE = '';
}
