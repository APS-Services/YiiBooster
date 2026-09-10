<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(__DIR__ . '/../../../src/helpers/TbIcon.php');

/**
 * TbIcon replaces eight hand-rolled icon-prefixing blocks that used to live in the widgets,
 * in two mutually inconsistent flavours. These tests pin the input spellings it has to keep
 * accepting, because every one of them appears in existing application code.
 */
class TbIconTest extends PHPUnit_Framework_TestCase
{
	protected function setUp(): void
	{
		TbIcon::$family = 'bi';
	}

	/**
	 * @return array
	 */
	public function equivalentSpellings()
	{
		return array(
			'bare name'                => array('trash'),
			'bootstrap 3 short'        => array('glyphicon-trash'),
			'bootstrap 3 qualified'    => array('glyphicon glyphicon-trash'),
			'bootstrap 2'              => array('icon-trash'),
			'already bootstrap icons'  => array('bi bi-trash'),
			'bootstrap icons short'    => array('bi-trash'),
			'padded'                   => array('  glyphicon glyphicon-trash  '),
		);
	}

	/**
	 * @test
	 * @dataProvider equivalentSpellings
	 *
	 * @param string $spelling
	 */
	public function everyAcceptedSpellingResolvesToTheSameClass($spelling)
	{
		$this->assertEquals('bi bi-trash', TbIcon::resolveCssClass($spelling));
	}

	/**
	 * @test
	 */
	public function resolvingIsIdempotent()
	{
		$once = TbIcon::resolveCssClass('trash');

		$this->assertEquals($once, TbIcon::resolveCssClass($once));
	}

	/**
	 * @test
	 */
	public function renamedGlyphiconsAreMapped()
	{
		$this->assertEquals('bi bi-check-lg', TbIcon::resolveCssClass('ok'));
		$this->assertEquals('bi bi-x-lg', TbIcon::resolveCssClass('remove'));
		$this->assertEquals('bi bi-person', TbIcon::resolveCssClass('glyphicon-user'));
		$this->assertEquals('bi bi-gear', TbIcon::resolveCssClass('icon-cog'));
		$this->assertEquals('bi bi-clock', TbIcon::resolveCssClass('time'));
		$this->assertEquals('bi bi-exclamation-triangle', TbIcon::resolveCssClass('warning-sign'));
	}

	/**
	 * Names the two icon sets happen to share need no map entry.
	 *
	 * @test
	 */
	public function sharedNamesPassThrough()
	{
		$this->assertEquals('bi bi-calendar', TbIcon::resolveCssClass('calendar'));
		$this->assertEquals('bi bi-search', TbIcon::resolveCssClass('search'));
		$this->assertEquals('bi bi-chevron-left', TbIcon::resolveCssClass('chevron-left'));
	}

	/**
	 * Applications already using Font Awesome must keep working untouched - the old widget code
	 * special-cased it with a `strpos($icon, 'fa') === false` test.
	 *
	 * @test
	 */
	public function foreignFamiliesArePassedThroughUnchanged()
	{
		$this->assertEquals('fa fa-trash', TbIcon::resolveCssClass('fa fa-trash'));
		$this->assertEquals('fas fa-trash', TbIcon::resolveCssClass('fas fa-trash'));
		$this->assertEquals('fab fa-github', TbIcon::resolveCssClass('fab fa-github'));
	}

	/**
	 * The old `strpos($icon, 'fa') === false` heuristic misfired on any name containing "fa",
	 * silently refusing to prefix it. This is the regression that fixes.
	 *
	 * @test
	 */
	public function namesContainingFaAreNotMistakenForFontAwesome()
	{
		$this->assertEquals('bi bi-fast-forward-fill', TbIcon::resolveCssClass('forward'));
		$this->assertEquals('bi bi-fan', TbIcon::resolveCssClass('fan'));
	}

	/**
	 * Likewise, the old heuristic treated any name containing "icon" as already prefixed.
	 *
	 * @test
	 */
	public function namesContainingIconAreStillPrefixed()
	{
		$this->assertEquals('bi bi-emoji-smile', TbIcon::resolveCssClass('emoji-smile'));
	}

	/**
	 * @test
	 */
	public function bootstrap2ColourModifiersAreDropped()
	{
		$this->assertEquals('bi bi-trash', TbIcon::resolveCssClass('icon-trash icon-white'));
		$this->assertEquals('bi bi-trash', TbIcon::resolveCssClass('glyphicon glyphicon-trash glyphicon-white'));
	}

	/**
	 * @test
	 */
	public function emptyInputProducesNothing()
	{
		$this->assertEquals('', TbIcon::resolveCssClass(''));
		$this->assertEquals('', TbIcon::resolveCssClass('   '));
		$this->assertEquals('', TbIcon::render(''));
	}

	/**
	 * @test
	 */
	public function rendersADecorativeIconElement()
	{
		$html = TbIcon::render('trash');

		$this->assertStringContainsString('class="bi bi-trash"', $html);
		$this->assertStringContainsString('aria-hidden="true"', $html);
		$this->assertStringStartsWith('<i', $html);
	}

	/**
	 * @test
	 */
	public function renderMergesCallerSuppliedAttributes()
	{
		$html = TbIcon::render('trash', array('class' => 'text-danger', 'title' => 'Delete'));

		$this->assertStringContainsString('bi bi-trash', $html);
		$this->assertStringContainsString('text-danger', $html);
		$this->assertStringContainsString('title="Delete"', $html);
	}

	/**
	 * An explicit aria-label means the icon carries meaning, so it must not be hidden.
	 *
	 * @test
	 */
	public function anExplicitAriaLabelSuppressesAriaHidden()
	{
		$html = TbIcon::render('trash', array('aria-label' => 'Delete'));

		$this->assertStringNotContainsString('aria-hidden', $html);
	}

	/**
	 * @test
	 */
	public function theIconFamilyIsConfigurable()
	{
		TbIcon::$family = 'fa';

		$this->assertEquals('fa fa-trash', TbIcon::resolveCssClass('trash'));
	}
}
