<?php
/**
 * 
 */

/**
 * 
 * @author amrbedair
 * @since v.4.0.0
 */
abstract class TbWidget extends CWidget {

	/**
	 * Contextual state names accepted in $context.
	 *
	 * Each has a matching CTX_*_CLASS constant giving the CSS suffix it renders as. The
	 * indirection is what lets a name outlive a Bootstrap rename: `default` is still an accepted
	 * context, but Bootstrap 5 has no `-default` variant, so it now renders as `secondary`.
	 */
	const CTX_DEFAULT = 'default';
	const CTX_PRIMARY = 'primary';
	const CTX_SECONDARY = 'secondary';
	const CTX_SUCCESS = 'success';
	const CTX_INFO = 'info';
	const CTX_WARNING = 'warning';
	const CTX_DANGER = 'danger';
	const CTX_LIGHT = 'light';
	const CTX_DARK = 'dark';

	/**
	 * @deprecated 5.0.0 Bootstrap 5 removed the `default` contextual variant that buttons and
	 * panels used. The name still works and now renders as `secondary`; prefer CTX_SECONDARY.
	 */
	const CTX_DEFAULT_CLASS = 'secondary';
	const CTX_PRIMARY_CLASS = 'primary';
	const CTX_SECONDARY_CLASS = 'secondary';
	const CTX_SUCCESS_CLASS = 'success';
	const CTX_INFO_CLASS = 'info';
	const CTX_WARNING_CLASS = 'warning';
	const CTX_DANGER_CLASS = 'danger';
	const CTX_LIGHT_CLASS = 'light';
	const CTX_DARK_CLASS = 'dark';
	
	/**
	 * easily make a widget more meaningful to a particular context by adding any of the contextual state classes
	 * @var string 
	 */
	public $context = self::CTX_DEFAULT;
	
	/**
	 * Utility function for appending class names for a generic $htmlOptions array.
	 *
	 * @param array $htmlOptions
	 * @param string $class
	 */
	protected static function addCssClass(&$htmlOptions, $class) {
		
		if (empty($class))
			return;
	
		if (isset($htmlOptions['class']))
			$htmlOptions['class'] .= ' ' . $class;
		else 
			$htmlOptions['class'] = $class;
	}

	/**
	 *
	 * @param bool|string $context
	 *
	 * @return bool
	 */
	protected function isValidContext($context = false) {
		if($context)
			return defined(get_called_class().'::CTX_'.strtoupper($context));
		else
			return defined(get_called_class().'::CTX_'.strtoupper($this->context));
	}

	/**
	 *
	 * @param bool|string $context
	 *
	 * @return mixed
	 */
	protected function getContextClass($context = false) {
		if($context)
			return constant(get_called_class().'::CTX_'.strtoupper($context).'_CLASS');
		else
			return constant(get_called_class().'::CTX_'.strtoupper($this->context).'_CLASS');
	}
}
