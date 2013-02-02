<?php
namespace TYPO3\CMS\View\Override\View;
$version = TYPO3_version;
$shouldMapClassesForV6 = $version{0} >= 6;
if ($shouldMapClassesForV6) {
	class_alias('TYPO3\\CMS\\Fluid\\View\\TemplateView', 'TYPO3\\CMS\\View\\Override\\View\\TemplateViewProxy');
	class_alias('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface', 'TYPO3\\CMS\\View\\Override\\View\\ViewInterfaceProxy');
} else {
	class_alias('\\Tx_Fluid_View_TemplateView', 'TYPO3\\CMS\\View\\Override\\View\\TemplateViewProxy');
	class_alias('\\Tx_Extbase_Mvc_View_ViewInterface', 'TYPO3\\CMS\\View\\Override\\View\\ViewInterfaceProxy');
}
class ExtendedTemplateViewProxy extends TemplateViewProxy implements ViewInterfaceProxy {

	/**
	 * @var boolean
	 */
	protected $versionIsAtLeastSixPointZero = FALSE;

	/**
	 * @var boolean
	 */
	protected $versionIsFourPointFive = FALSE;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		parent::__construct();
		$version = TYPO3_version;
		$this->versionIsAtLeastSixPointZero = ($version{0} >= 6);
		$this->versionIsFourPointFive = ($version{0} == 4 && $version{2} == 5);
	}

	/**
	 * @param string $pattern Pattern to be resolved
	 * @param boolean $bubbleControllerAndSubpackage if TRUE, then we successively split off parts from "@controller" and "@subpackage" until both are empty.
	 * @param boolean $formatIsOptional if TRUE, then half of the resulting strings will have ."@format" stripped off, and the other half will have it.
	 * @return array unix style path
	 */
	protected function expandGenericPathPattern($pattern, $bubbleControllerAndSubpackage, $formatIsOptional) {
		$subpackageKey = $this->controllerContext->getRequest()->getControllerSubpackageKey();
		$pathOverlayConfigurations = $this->buildPathOverlayConfigurations($subpackageKey);
		$templateRootPath = $backupTemplateRootPath = $this->getTemplateRootPath();
		$partialRootPath = $backupPartialRootPath = $this->getPartialRootPath();
		$layoutRootPath = $backupLayoutRootPath = $this->getLayoutRootPath();
		$subpackageKey = $this->controllerContext->getRequest()->getControllerSubpackageKey();
		$paths = $this->expandGenericPathPatternReal($pattern, $bubbleControllerAndSubpackage, $formatIsOptional);
		foreach ($pathOverlayConfigurations as $overlayPaths) {
			list ($templateRootPath, $partialRootPath, $layoutRootPath) = array_values($overlayPaths);
			$this->setTemplateRootPath($templateRootPath);
			$this->setPartialRootPath($partialRootPath);
			$this->setLayoutRootPath($layoutRootPath);
			$subset = $this->expandGenericPathPatternReal($pattern, $bubbleControllerAndSubpackage, $formatIsOptional);
			$paths = array_merge($paths, $subset);
		}
		$paths = array_reverse($paths);
		$paths = array_unique($paths);
		$this->setTemplateRootPath($backupTemplateRootPath);
		$this->setPartialRootPath($backupPartialRootPath);
		$this->setLayoutRootPath($backupLayoutRootPath);
		return $paths;
	}

	/**
	 * @param string $pattern Pattern to be resolved
	 * @param boolean $bubbleControllerAndSubpackage if TRUE, then we successively split off parts from "@controller" and "@subpackage" until both are empty.
	 * @param boolean $formatIsOptional if TRUE, then half of the resulting strings will have ."@format" stripped off, and the other half will have it.
	 * @return array unix style path
	 */
	protected function expandGenericPathPatternReal($pattern, $bubbleControllerAndSubpackage, $formatIsOptional) {
		$pattern = str_replace('@templateRoot', $this->getTemplateRootPath(), $pattern);
		$pattern = str_replace('@partialRoot', $this->getPartialRootPath(), $pattern);
		$pattern = str_replace('@layoutRoot', $this->getLayoutRootPath(), $pattern);
		$subpackageKey = $this->controllerContext->getRequest()->getControllerSubpackageKey();
		$controllerName = $this->controllerContext->getRequest()->getControllerName();
		if ($subpackageKey !== NULL) {
			if (strpos($subpackageKey, $this->versionIsAtLeastSixPointZero ? \TYPO3\CMS\Fluid\Fluid::NAMESPACE_SEPARATOR : \Tx_Fluid_Fluid::NAMESPACE_SEPARATOR) !== FALSE) {
				$namespaceSeparator = $this->versionIsAtLeastSixPointZero ? \TYPO3\CMS\Fluid\Fluid::NAMESPACE_SEPARATOR : \Tx_Fluid_Fluid::NAMESPACE_SEPARATOR;
			} else {
				$namespaceSeparator = $this->versionIsAtLeastSixPointZero ? \TYPO3\CMS\Fluid\Fluid::LEGACY_NAMESPACE_SEPARATOR : \Tx_Fluid_Fluid::NAMESPACE_SEPARATOR;
			}
			$subpackageParts = explode($namespaceSeparator, $subpackageKey);
		} else {
			$subpackageParts = array();
		}
		$results = array();
		$i = $controllerName === NULL ? 0 : -1;
		do {
			$temporaryPattern = $pattern;
			if ($i < 0) {
				$temporaryPattern = str_replace('@controller', $controllerName, $temporaryPattern);
			} else {
				$temporaryPattern = str_replace('//', '/', str_replace('@controller', '', $temporaryPattern));
			}
			$temporaryPattern = str_replace('@subpackage', implode('/', $i < 0 ? $subpackageParts : array_slice($subpackageParts, $i)), $temporaryPattern);
			$results[] = $this->fixWindowsFilePathProxy(str_replace('@format', $this->controllerContext->getRequest()->getFormat(), $temporaryPattern));
			if ($formatIsOptional) {
				$results[] = $this->fixWindowsFilePathProxy(str_replace('.@format', '', $temporaryPattern));
			}
		} while ($i++ < count($subpackageParts) && $bubbleControllerAndSubpackage);
		return $results;
	}

	/**
	 * @param string $subpackageKey
	 * @return array
	 */
	private function buildPathOverlayConfigurations($subpackageKey) {
		if ($this->versionIsAtLeastSixPointZero) {
			$configurationManagerInterfaceName = 'TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface';
			$configurationType = constant('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK');
		} else {
			$configurationManagerInterfaceName = 'Tx_Extbase_Configuration_ConfigurationManagerInterface';
			$configurationType = constant('Tx_Extbase_Configuration_ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK');
		}
		$configurations = $this->objectManager->get($configurationManagerInterfaceName)->getConfiguration($configurationType);
		$configurations = $configurations['view'];
		$templateRootPath = $this->getTemplateRootPath();
		$partialRootPath = $this->getPartialRootPath();
		$layoutRootPath = $this->getLayoutRootPath();
		$overlays = array();
		$paths = array();
		if (TRUE === isset($configurations['overlays'])) {
			$overlays = $configurations['overlays'];
		}
		foreach ($overlays as $overlaySubpackageKey => $configuration) {
			if (TRUE === isset($configuration['templateRootPath'])) {
				$templateRootPath = $this->getFileAbsFileNameProxy($configuration['templateRootPath']);
			}
			if (TRUE === isset($configuration['partialRootPath'])) {
				$partialRootPath = $this->getFileAbsFileNameProxy($configuration['partialRootPath']);
			}
			if (TRUE === isset($configuration['layoutRootPath'])) {
				$layoutRootPath = $this->getFileAbsFileNameProxy($configuration['layoutRootPath']);
			}
			$paths[$overlaySubpackageKey] = array(
				'templateRootPath' => $templateRootPath,
				'partialRootPath' => $partialRootPath,
				'layoutRootPath' => $partialRootPath
			);
		}
		$paths = array_reverse($paths);
		$paths[] = array(
			'templateRootPath' => $templateRootPath,
			'partialRootPath' => $partialRootPath,
			'layoutRootPath' => $partialRootPath
		);
		return $paths;
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	protected function getFileAbsFileNameProxy($filename) {
		if ($this->versionIsFourPointFive) {
			return \t3lib_div::getFileAbsFileName($filename);
		}
		if ($this->versionIsAtLeastSixPointZero) {
			return \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($filename);
		}
		return \t3lib_div::getFileAbsFileName($filename);
	}

	/**
	 * @param string $path
	 * @return string
	 */
	protected function fixWindowsFilePathProxy($path) {
		if ($this->versionIsAtLeastSixPointZero) {
			return \TYPO3\CMS\Core\Utility\GeneralUtility::fixWindowsFilePath($path);
		}
		return \t3lib_div::fixWindowsFilePath($path);
	}

}

if ($shouldMapClassesForV6) {
	class_alias('TYPO3\\CMS\\View\\Override\\View\\ExtendedTemplateViewProxy', 'TYPO3\\CMS\\View\\Override\\View\\ExtendedTemplateView');
} else {
	class_alias('TYPO3\\CMS\\View\\Override\\View\\ExtendedTemplateViewProxy', 'Tx_View_Override_View_ExtendedTemplateView');
}
unset($shouldMapClassesForV6, $version);