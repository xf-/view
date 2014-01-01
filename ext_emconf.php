<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "view".
 *
 * Auto generated 04-12-2013 19:22
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'View: Extended Fluid Views',
	'description' => 'Extends the built-in Fluid Views with smart overlay capabilities',
	'category' => 'misc',
	'shy' => 0,
	'version' => '1.2.2',
	'dependencies' => 'cms,extbase,fluid',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Claus Due',
	'author_email' => 'claus@namelesscoder.net',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-6.2.99',
			'cms' => '',
			'extbase' => '',
			'fluid' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:9:{s:13:"composer.json";s:4:"88c0";s:16:"ext_autoload.php";s:4:"e0c9";s:12:"ext_icon.gif";s:4:"e922";s:17:"ext_localconf.php";s:4:"1b28";s:9:"README.md";s:4:"8454";s:61:"Classes/Override/Parser/SyntaxTree/ExtendedViewHelperNode.php";s:4:"cd68";s:46:"Classes/Override/View/ExtendedTemplateView.php";s:4:"fb66";s:33:"Documentation/ComplexityChart.png";s:4:"cc73";s:30:"Documentation/PyramidChart.png";s:4:"b36b";}',
	'suggests' => array(
	),
);

?>