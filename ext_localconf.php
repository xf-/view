<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
$version = TYPO3_version;
if ($version{0} >= 6) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Fluid\\View\\TemplateView'] =
		array('className' => 'TYPO3\\CMS\\View\\Override\\View\\ExtendedTemplateView');
} else {
	class_alias('Tx_View_Override_View_ExtendedTemplateView', 'ux_Tx_Fluid_View_TemplateView');
}
unset($version);
