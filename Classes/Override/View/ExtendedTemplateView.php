<?php
namespace TYPO3\CMS\View\Override\View;

class ExtendedTemplateView extends \TYPO3\CMS\Fluid\View\TemplateView implements \TYPO3\CMS\Extbase\Mvc\View\ViewInterface {

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
			if (strpos($subpackageKey, \TYPO3\CMS\Fluid\Fluid::NAMESPACE_SEPARATOR) !== FALSE) {
				$namespaceSeparator = \TYPO3\CMS\Fluid\Fluid::NAMESPACE_SEPARATOR;
			} else {
				$namespaceSeparator = \TYPO3\CMS\Fluid\Fluid::LEGACY_NAMESPACE_SEPARATOR;
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
			$results[] = \TYPO3\CMS\Core\Utility\GeneralUtility::fixWindowsFilePath(str_replace('@format', $this->controllerContext->getRequest()->getFormat(), $temporaryPattern));
			if ($formatIsOptional) {
				$results[] = \TYPO3\CMS\Core\Utility\GeneralUtility::fixWindowsFilePath(str_replace('.@format', '', $temporaryPattern));
			}
		} while ($i++ < count($subpackageParts) && $bubbleControllerAndSubpackage);
		return $results;
	}

	/**
	 * @param string $subpackageKey
	 * @return array
	 */
	private function buildPathOverlayConfigurations($subpackageKey) {
		$configurations = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface')
			->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK);
		$configurations = $configurations['view'];
		$templateRootPath = $this->getTemplateRootPath();
		$partialRootPath = $this->getPartialRootPath();
		$layoutRootPath = $this->getLayoutRootPath();
		$overlays = NULL;
		$paths = array();
		if (isset($configurations['overlays']) === TRUE) {
			$overlays = $configurations['overlays'];
			foreach ($overlays as $overlaySubpackageKey => $configuration) {
				if (isset($configuration['templateRootPath'])  === TRUE) {
					$templateRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($configuration['templateRootPath']);
				}
				if (isset($configuration['partialRootPath']) === TRUE) {
					$partialRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($configuration['partialRootPath']);
				}
				if (isset($configuration['layoutRootPath']) === TRUE) {
					$layoutRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($configuration['layoutRootPath']);
				}
				$paths[$overlaySubpackageKey] = array(
					'templateRootPath' => $templateRootPath,
					'partialRootPath' => $partialRootPath,
					'layoutRootPath' => $partialRootPath
				);
			}
		}
		$paths = array_reverse($paths);
		$paths[] = array(
			'templateRootPath' => $templateRootPath,
			'partialRootPath' => $partialRootPath,
			'layoutRootPath' => $partialRootPath
		);
		return $paths;
	}

}