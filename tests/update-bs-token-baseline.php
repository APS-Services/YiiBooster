<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 *
 * Regenerates tests/bs-token-baseline.php from the current state of the source tree.
 *
 * Run it after migrating a widget, once you have verified that the tokens it lost are gone
 * because the markup was actually updated - not because a file was renamed or the pattern
 * stopped matching. Review the resulting diff: it should only ever shrink.
 *
 *     php tests/update-bs-token-baseline.php
 */

require_once(__DIR__ . '/bs-token-lint.php');

$previous = file_exists(__DIR__ . '/bs-token-baseline.php')
	? require(__DIR__ . '/bs-token-baseline.php')
	: array();

$current = BsTokenLint::scan();

$header = <<<'PHPDOC'
<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 *
 * Bootstrap 2/3 markup still present in the source tree, recorded per token.
 *
 * This is a RATCHET, not a permanent allowlist. Bootstrap5TokenLintTest asserts the tree matches
 * this file exactly, so migrating a widget fails the suite until you delete its entries here.
 * That is intentional: every migration commit should shrink this file, and it should be an empty
 * list per token by the time 5.0.0 ships. Never add an entry to silence a new violation.
 *
 * Regenerate with: php tests/update-bs-token-baseline.php
 */
return array(

PHPDOC;

$body = '';
$before = 0;
$after = 0;
foreach ($current as $token => $files) {
	$before += isset($previous[$token]) ? count($previous[$token]) : 0;
	$after += count($files);

	$body .= sprintf("\t%-24s => array(\n", "'" . $token . "'");
	foreach ($files as $file) {
		$body .= "\t\t'" . $file . "',\n";
	}
	$body .= "\t),\n";
}

file_put_contents(__DIR__ . '/bs-token-baseline.php', $header . $body . ");\n");

echo "Baseline regenerated.\n";
echo "  file-hits before: {$before}\n";
echo "  file-hits after:  {$after}\n";

if ($after > $before) {
	echo "\nWARNING: the baseline GREW. Something reintroduced Bootstrap 2/3 markup.\n";
	exit(1);
}

echo "\nRemaining work:\n";
foreach ($current as $token => $files) {
	if (!empty($files)) {
		printf("  %-24s %d file(s)\n", $token, count($files));
	}
}
