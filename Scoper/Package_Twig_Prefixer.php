<?php
/**
 * Special twig scoper
 *
 * PHP Version 8.1

 * @package Bmd_WPFramework
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link    https://www.bobmoore.dev
 * @see     https://github.com/OnTheGoSystems/twig-scoper
 * @since   1.0.0
 */

namespace Bmd\WPFramework\Scoper;

class Package_Twig_Prefixer
{
	/**
	 * Construct new instance and set prefix
	 *
	 * @param string $prefix : prefix to use.
	 */
	public function __construct( protected string $prefix = '' ) {}
	/**
	 * Setter for prefix
	 *
	 * @param string $prefix : prefix to use.
	 *
	 * @return void
	 */
	public function setPrefix( string $prefix ): void
	{
		$this->prefix = $prefix;
	}
	/**
	 * Getter for the prefix.
	 *
	 * @return string
	 */
	public function getPrefix(): string
	{
		return $this->prefix;
	}
	/**
	 * Check if string ends with a given substring.
	 *
	 * @param string $haystack : string to search.
	 * @param string $needle : substring to search for.
	 *
	 * @return boolean
	 */
	public function endsWith( string $haystack, string $needle ): bool
	{
		$length = strlen( $needle );

		if ( 0 === $length ) {
			return true;
		}
		return ( substr( $haystack, - $length ) === $needle );
	}
	/**
	 * Get formatted prefix
	 *
	 * @param integer $backslashDuplicationFactor : number of backslashes to duplicate.
	 * @param boolean $includeInitialBackslash : include initial backslash.
	 *
	 * @return string
	 */
	public function getFormattedPrefix(
		int $backslashDuplicationFactor = 0,
		bool $includeInitialBackslash = true
	): string {
		$prefix = $this->getPrefix();

		if ($includeInitialBackslash) {
			$prefix = '\\' . $prefix;
		}

		for ($i = 0; $i < $backslashDuplicationFactor; $i++) {
			$prefix = str_replace('\\', '\\\\', $prefix);
		}

		return $prefix;
	}
	/**
	 * Patch for all
	 *
	 * @param string $contents : contents to patch.
	 *
	 * @return string
	 */
	public function patchForAll(string $contents): string
	{
		// Hardcoded class names in code
		$contents = preg_replace(
			'/("|\')((\\\\){1,2}Twig(\\\\){1,2}[A-Za-z\\\\]+)\1/m',
			'$1' . $this->getFormattedPrefix(2) . '$2$1',
			$contents
		);

		// Hardcoded "use" statements
		$contents = preg_replace(
			'/use\s+(Twig)(\\\\){1,2}/m',
			'use ' . $this->getFormattedPrefix(2) . '\\\\\\\\Twig\\\\\\\\',
			$contents
		);

		// Addnamespaces to generated Twig template names
		$contents = preg_replace(
			'/(\'|")(__TwigTemplate_)\1/m',
			'$1' . $this->getFormattedPrefix(2) . '\\\\\\\\$2$1',
			$contents
		);

		return $contents;
	}
	/**
	 * Patch for module node
	 *
	 * @param string $contents : contents to patch.
	 * @param string $filePath : file path to patch.
	 *
	 * @return string
	 */
	public function patchForModuleNode(string $contents, string $filePath): string
	{
		if (!$this->endsWith(
			$filePath,
			'src' . DIRECTORY_SEPARATOR . 'Node' . DIRECTORY_SEPARATOR . 'ModuleNode.php'
		)) {
			return $contents;
		}

		// Fix template compilation - add thenamespace to the template file.
		// _custom, originally was 1 for backslashDuplicationFactor, it caused issues e.g. 'org\some\vendor' with the '\v' case
		$contents = preg_replace(
			'/(compileClassHeader\s*\([^\)]+\)\s*{\s*\s*\$compiler\s*->\s*write\s*\(\s*)"\\\\n\\\\n"(\s*\)\s*;)/m',
			'$1"\\n\\nnamespace ' . $this->getFormattedPrefix(2, false) . ';\\n\\n"$2',
			$contents
		);

		// When generating the PHP template, make sure its class declaration doesn't contain thenamespace.
		// That's the only place where we don't want to have it.
		$string_to_remove = $this->getFormattedPrefix() . '\\';
		$contents = preg_replace(
			'/(->write\s*\(\s*\'class \'\s*\.\s*)(\$compiler\s*->\s*getEnvironment\s*\(\s*\)\s*->\s*getTemplateClass\s*\(\s*\$this\s*->\s*getSourceContext\s*\(\s*\)\s*->\s*getName\s*\(\s*\)\s*,\s*\$this\s*->\s*getAttribute\s*\(\s*\'index\'\s*\)\s*\))/m',
			'$1 \\substr( $2, ' . strlen($string_to_remove) . ' ) ',
			$contents
		);

		return $contents;
	}
	/**
	 * Patch for core extension
	 *
	 * @param string $contents : contents to patch.
	 * @param string $filePath : file path to patch.
	 *
	 * @return string
	 */
	public function patchForCoreExtension(string $contents, string $filePath): string
	{
		// Fix the usage of global twig_* and _twig_* functions.
		if (!$this->endsWith(
			$filePath,
			'src' . DIRECTORY_SEPARATOR . 'Extension' . DIRECTORY_SEPARATOR . 'CoreExtension.php'
		)) {
			return $contents;
		}

		$contents = preg_replace(
			'/(TwigFilter\(\s*\'[^\']+\'\s*,\s*\')((_)?twig_[^\']+\')/m',
			'$1' . $this->getFormattedPrefix(2) . '\\\\\\\\$2',
			$contents
		);

		// Handle the occurrence in the is_safe_callback array element.
		$contents = preg_replace(
			'/(new ' . $this->getFormattedPrefix(
				1
			) . '\\\\Twig\\\\TwigFilter\(\s*\'[^\']+\'\s*,\s*\'.*twig_[^\']+\',\s*\[[^\]]*,\s*\'is_safe_callback\'\s*=>\s*\')((_)?twig_[^\']+\'\s*\]\s*\))/m',
			'$1' . $this->getFormattedPrefix(2) . '\\\\\\\\$2',
			$contents
		);

		// Handle the occurrence in the preg_replace_callback.
		$contents = preg_replace(
			'/(preg_replace_callback.*,\s*\')(.+\'.*;)/m',
			'$1' . $this->getFormattedPrefix(2) . '\\\\\\\\$2',
			$contents
		);

		// Handle the occurrence in the array_walk_recursive.
		$contents = preg_replace(
			'/(array_walk_recursive.*,\s*\')(.+\'.*;)/m',
			'$1' . $this->getFormattedPrefix(2) . '\\\\\\\\$2',
			$contents
		);

		// remove the global call of local functions within the file
		// \twig_convert_encoding( => twig_convert_encoding(
		preg_match_all('/function ([_]*twig_[a-z-_]+)\(/', $contents, $twigFunctions, PREG_SET_ORDER);

		foreach ($twigFunctions as $twigFunction) {
			$twigFunctionName = $twigFunction[1];

			$contents = str_replace('\\' . $twigFunctionName . '(', $twigFunctionName . '(', $contents);
		}

		return $contents;
	}
	/**
	 * Patch for environment
	 *
	 * @param string $contents : contents to patch.
	 * @param string $filePath : file path to patch.
	 *
	 * @return string
	 */
	public function patchForEnvironment(string $contents, string $filePath): string
	{
		// Fix the usage of Twig\\Extension\\AbstractExtension.
		if (!$this->endsWith($filePath, 'src' . DIRECTORY_SEPARATOR . 'Environment.php')) {
			return $contents;
		}
		$contents = preg_replace(
			'/(Twig\\\\.+\\\\AbstractExtension)/m',
			$this->getFormattedPrefix(2, false) . '\\\\\\\\$1',
			$contents
		);

		return $contents;
	}
	/**
	 * Patch for call expression - fixes static callable class names in compiled templates
	 *
	 * When a filter/function callable is [ClassName::class, 'method'], the compiled template
	 * outputs ClassName::method() without a leading backslash. In the namespaced compiled
	 * template (namespace Bmd\WPFramework\Deps), this causes the class to be resolved relative
	 * to the current namespace, resulting in a doubled prefix error.
	 *
	 * @param string $contents : contents to patch.
	 * @param string $filePath : file path to patch.
	 *
	 * @return string
	 */
	public function patchForCallExpression(string $contents, string $filePath): string
	{
		if (!$this->endsWith(
			$filePath,
			'src' . DIRECTORY_SEPARATOR . 'Node' . DIRECTORY_SEPARATOR . 'Expression' . DIRECTORY_SEPARATOR . 'CallExpression.php'
		)) {
			return $contents;
		}

		// Fix: prepend \ to class name in static callable output to make it absolute
		// Original: sprintf('%s::%s', $callable[0], $callable[1])
		// Fixed:    sprintf('\\%s::%s', \ltrim($callable[0], '\\'), $callable[1])
		$contents = str_replace(
			'$compiler->raw(\\sprintf(\'%s::%s\', $callable[0], $callable[1]));',
			'$compiler->raw(\\sprintf(\'\\\\%s::%s\', \\ltrim($callable[0], \'\\\\\'), $callable[1]));',
			$contents
		);

		return $contents;
	}
	/**
	 * Patch for capture node - fixes unscoped CoreExtension reference
	 *
	 * When use_yield is false, CaptureNode outputs \Twig\Extension\CoreExtension::captureOutput()
	 * which needs to be the scoped class name.
	 *
	 * @param string $contents : contents to patch.
	 * @param string $filePath : file path to patch.
	 *
	 * @return string
	 */
	public function patchForCaptureNode(string $contents, string $filePath): string
	{
		if (!$this->endsWith(
			$filePath,
			'src' . DIRECTORY_SEPARATOR . 'Node' . DIRECTORY_SEPARATOR . 'CaptureNode.php'
		)) {
			return $contents;
		}

		$contents = str_replace(
			"'\\\\Twig\\\\Extension\\\\CoreExtension::captureOutput('",
			"'\\\\" . $this->getFormattedPrefix(1, false) . "\\\\\\\\Twig\\\\\\\\Extension\\\\\\\\CoreExtension::captureOutput('",
			$contents
		);

		return $contents;
	}
}