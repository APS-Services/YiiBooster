<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(__DIR__ . '/../bs-token-lint.php');

/**
 * Ratchet test for the Bootstrap 5 migration.
 *
 * Rather than asserting the rendered output of 79 widgets one by one, this scans the whole source
 * tree for Bootstrap 2/3 markup and compares it against a recorded baseline. It fails in both
 * directions on purpose:
 *
 *  - a file gaining a legacy token (a regression) fails, and
 *  - a file losing one (progress) also fails, until its baseline entry is removed.
 *
 * The second half is the point: it makes the shrinking baseline a visible part of every migration
 * commit, and turns "did we get them all?" from a code-review question into a build failure.
 */
class Bootstrap5TokenLintTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @return array
	 */
	protected function baseline()
	{
		return require(__DIR__ . '/../bs-token-baseline.php');
	}

	/**
	 * @test
	 */
	public function baselineCoversEveryDefinedToken()
	{
		$baseline = $this->baseline();
		foreach (array_keys(BsTokenLint::patterns()) as $token) {
			$this->assertArrayHasKey(
				$token,
				$baseline,
				"Token '{$token}' is defined in BsTokenLint but missing from bs-token-baseline.php."
			);
		}
	}

	/**
	 * @test
	 */
	public function sourceTreeMatchesRecordedBaseline()
	{
		$baseline = $this->baseline();
		$actual = BsTokenLint::scan();

		foreach ($actual as $token => $files) {
			$expected = isset($baseline[$token]) ? $baseline[$token] : array();

			$added = array_values(array_diff($files, $expected));
			$removed = array_values(array_diff($expected, $files));

			$this->assertEmpty(
				$added,
				"Bootstrap 2/3 token '{$token}' appeared in files that were previously clean:\n  "
				. implode("\n  ", $added)
				. "\nThis is a regression - use the Bootstrap 5 equivalent instead of adding a baseline entry."
			);

			$this->assertEmpty(
				$removed,
				"Bootstrap 2/3 token '{$token}' is gone from these files:\n  "
				. implode("\n  ", $removed)
				. "\nThat is progress - delete those entries from tests/bs-token-baseline.php to lock it in."
			);
		}
	}

	/**
	 * Guards the scanner itself: a pattern that silently stops matching would make the ratchet
	 * pass while the markup is still there.
	 *
	 * @test
	 */
	public function scannerStillDetectsKnownLegacyMarkup()
	{
		$patterns = BsTokenLint::patterns();

		$this->assertRegExp($patterns['glyphicon'], '<span class="glyphicon glyphicon-trash"></span>');
		$this->assertRegExp($patterns['data-api-attribute'], '<a data-toggle="dropdown">');
		$this->assertRegExp($patterns['panel'], '<div class="panel-heading">');
		$this->assertRegExp($patterns['hide-utility'], '<div class="modal hide fade">');
		$this->assertRegExp($patterns['caret'], '<span class="caret"></span>');

		// ...and does not fire on the Bootstrap 5 replacements, or on prose.
		$this->assertNotRegExp($patterns['data-api-attribute'], '<a data-bs-toggle="dropdown">');
		$this->assertNotRegExp($patterns['panel'], '<div class="card-header">');
		$this->assertNotRegExp($patterns['hide-utility'], "\$el->popover('hide');");
		$this->assertNotRegExp($patterns['hide-utility'], '* Button to show/hide the panel');
		$this->assertNotRegExp($patterns['caret'], 'class="caret-down-icon"');
	}
}
