<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Fluid\\View\\TemplateView'] =
	array('className' => 'TYPO3\\CMS\\View\\Override\\View\\ExtendedTemplateView');