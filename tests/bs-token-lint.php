<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 *
 * Shared definition of the Bootstrap 2/3 markup tokens we are migrating away from, plus the
 * scanner used by both Bootstrap5TokenLintTest and the baseline generator.
 *
 * Vendored assets under src/assets/ are deliberately NOT scanned: third-party libraries carry
 * their own markup and are dealt with by replacing or upgrading the library, not by editing it.
 */
class BsTokenLint
{
	/**
	 * Token name => PCRE matching its use in emitted markup.
	 *
	 * Patterns are deliberately narrow. A bare `well` or `controls` would match ordinary English in
	 * docblocks, so every token is anchored to the shape it actually takes in a class attribute.
	 *
	 * @return array
	 */
	public static function patterns()
	{
		return array(
			'glyphicon'            => '/glyphicon/',
			'bs2-icon-class'       => '/(?<![-\w])icon-(?!bar\b)[a-z]/',
			'data-api-attribute'   => '/data-(toggle|target|dismiss|slide|slide-to|spy|parent|ride)\b/',
			'panel'                => '/panel-(default|primary|success|info|warning|danger|heading|body|footer|title)/',
			'help-block'           => '/help-block/',
			'validation-state'     => '/has-(error|success|warning)/',
			'input-group-addon'    => '/input-group-(addon|btn)/',
			'btn-removed'          => '/btn-(default|xs|block)\b/',
			'caret'                => '/(?<![-\w])caret(?![-\w])/',
			'hero-unit'            => '/hero-unit/',
			'jumbotron'            => '/jumbotron/',
			'control-group'        => '/control-group/',
			'control-label'        => '/control-label/',
			'float-utility'        => '/pull-(left|right)/',
			'nav-stacked'          => '/nav-stacked/',
			'btn-group-justified'  => '/btn-group-justified/',
			'img-utility'          => '/img-(responsive|rounded|circle)/',
			'label-context'        => '/label-(default|primary|success|info|warning|danger)/',
			'navbar-removed'       => '/navbar-(default|inverse|header|toggle\b|fixed-)/',
			'progress-context'     => '/progress-(striped|success|info|warning|danger)/',
			'form-inline'          => '/form-inline/',
			// Anchored to a class attribute on purpose: a bare `hide` also matches the colorpicker's
			// JS event name, `.popover('hide')`, and the words "show/hide" in docblocks.
			'hide-utility'         => '/class\s*=\s*["\'][^"\']*(?<![-\w])hide(?![-\w])/',
		);
	}

	/**
	 * Directories scanned, relative to the repository root.
	 * @return array
	 */
	public static function scanRoots()
	{
		return array('src/widgets', 'src/components', 'src/helpers', 'src/views', 'src/actions', 'src/filters', 'src/gii');
	}

	/**
	 * Repo-relative files exempt from the scan.
	 *
	 * Only for code whose *job* is to know the legacy names. This is not an escape hatch for
	 * unmigrated widgets - those belong in the baseline, where they stay visible.
	 *
	 * @return array
	 */
	public static function excludedFiles()
	{
		return array(
			// The Glyphicons-to-Bootstrap-Icons compatibility layer necessarily spells out the
			// old class names; that is the feature, not a leftover.
			'src/helpers/TbIcon.php',
		);
	}

	/**
	 * @return string absolute path of the repository root.
	 */
	public static function rootDir()
	{
		return dirname(__DIR__);
	}

	/**
	 * Scans the source tree and returns token name => sorted list of repo-relative files using it.
	 *
	 * @return array
	 */
	public static function scan()
	{
		$root = self::rootDir();
		$found = array();
		foreach (self::patterns() as $token => $pattern) {
			$found[$token] = array();
		}

		foreach (self::scanRoots() as $relativeRoot) {
			$dir = $root . '/' . $relativeRoot;
			if (!is_dir($dir)) {
				continue;
			}
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
			foreach ($iterator as $file) {
				if (!$file->isFile() || substr($file->getFilename(), -4) !== '.php') {
					continue;
				}
				$path = str_replace($root . '/', '', $file->getPathname());
				if (in_array($path, self::excludedFiles(), true)) {
					continue;
				}
				$contents = file_get_contents($file->getPathname());
				foreach (self::patterns() as $token => $pattern) {
					if (preg_match($pattern, $contents)) {
						$found[$token][] = $path;
					}
				}
			}
		}

		foreach ($found as $token => $files) {
			sort($found[$token]);
		}
		ksort($found);

		return $found;
	}
}
