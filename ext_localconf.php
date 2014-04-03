<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
$script = t3lib_div::getIndpEnv('SCRIPT_FILENAME');
if ('sysext/install/Start/Install.php' !== substr($script, -32)) {
	$version = TYPO3_version;
	if ($version{0} >= 6) {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['Tx_Fluid_View_TemplateView'] =
			array('className' => 'Tx_View_Override_View_ExtendedTemplateView');
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Fluid\\View\\TemplateView'] =
			array('className' => 'FluidTYPO3\\View\\Override\\View\\ExtendedTemplateView');
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode'] =
			array('className' => 'Tx_View_Override_Parser_SyntaxTree_ExtendedViewHelperNode');
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\ViewHelperNode'] =
			array('className' => 'FluidTYPO3\\View\\Override\\Parser\\SyntaxTree\\ExtendedViewHelperNode');
	} else {
		class_alias('Tx_View_Override_View_ExtendedTemplateView', 'ux_Tx_Fluid_View_TemplateView');
		class_alias('Tx_View_Override_Parser_SyntaxTree_ExtendedViewHelperNode', 'ux_Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode');
	}
}
unset($version, $script);
