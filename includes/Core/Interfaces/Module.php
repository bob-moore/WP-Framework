<?php
/**
 * Module interface definition
 *
 * PHP Version 8.2
 *
 * @package Bmd_WPFramework
 * @author  Bob Moore <bob.moore@midwestfamilymadison.com>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.midwestfamilymadison.com
 * @since   1.0.0
 */

namespace Bmd\WPFramework\Core\Interfaces;

/**
 * Module interface requirements
 *
 * @subpackage Interfaces
 */
interface Module
{
	/**
	 * Setter for package name
	 *
	 * @param string $package : string name of the package.
	 *
	 * @return void
	 */
	public function setPackage( string $package ): void;
	/**
	 * Getter for package name
	 *
	 * @return string
	 */
	public function getPackage(): string;
}
