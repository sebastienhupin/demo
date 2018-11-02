<?php

namespace Opentalent\OtWebservice\ViewHelpers\Utility;

/**
 * Description of IfHasActionViewHelper
 *
 * @author Sébastien Hupin <sebastien.hupin at 2iopenservice.fr>
 */
class IfHasActionViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

    /**
     * Initializes the "pluginName" argument.
     * Initializes the "action" argument.
     * Renders <f:then> if the plugin has the action
     * otherwise renders <f:else> child.
     */
    public function initializeArguments() {
        $this->registerArgument('pluginName', 'string', 'The plugin name).');
        $this->registerArgument('action', 'string', 'The plugin action).');
    }

    /**
     * This method decides if the condition is TRUE or FALSE. It can be overridden in extending viewhelpers to adjust functionality.
     *
     * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexiblity in overriding this method.
     * @return bool
     */
    protected static function evaluateCondition($arguments = null) {
        $piVars = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP($arguments['pluginName']);

        if ($piVars['action'] === $arguments['action']) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * @return mixed 
     */
    public function render() {
        if (static::evaluateCondition($this->arguments)) {
            return $this->renderThenChild();
        }
        return $this->renderElseChild();
    }

}
