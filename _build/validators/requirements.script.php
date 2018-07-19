<?php
/** @var modX $modx */
$modx =& $object->xpdo;
$success = false;
switch($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        $success = true;
        $modx->log(xPDO::LOG_LEVEL_INFO, 'Checking if server meets the minimum requirements...');

        // Check for MODX 2.5.2 or higher
        $level = xPDO::LOG_LEVEL_INFO;
        $modxVersion = $modx->getVersionData();
        if (version_compare($modxVersion['full_version'], '2.5.2') < 0) {
            $level = xPDO::LOG_LEVEL_ERROR;
            $success = false;
        }
        $modx->log($level, '- MODX Revolution 2.5.2+: ' . $modxVersion['full_version']);

        // Check for PHP 5.5 or higher
        $level = xPDO::LOG_LEVEL_INFO;
        if (version_compare(PHP_VERSION, '5.5.0') < 0) {
            $level = xPDO::LOG_LEVEL_ERROR;
            $success = false;
        }
        $modx->log($level, '- PHP version 5.5+: ' . PHP_VERSION);

        // Check for Commerce 0.11 +
        $corePath = $modx->getOption('commerce.core_path', null, $modx->getOption('core_path') . 'components/commerce/');
        $corePath .= 'model/commerce/';
        $installed = true;
        $params = ['mode' => $modx->getOption('commerce.mode'), 'isSetup' => true];
        /** @var Commerce|null $commerce */
        $commerce = $modx->getService('commerce', 'Commerce', $corePath, $params);
        if (!$commerce) {
            $level = xPDO::LOG_LEVEL_ERROR;
            $success = false;
            $installed = false;
        }
        $modx->log($level, '- Commerce installed: ' . ($installed ? 'yes' : 'no'));
        if ($commerce instanceof Commerce) {
            $installed = version_compare((string)$commerce->version, '0.11.0-rc1', '>=');
            $level = $installed ? xPDO::LOG_LEVEL_INFO : xPDO::LOG_LEVEL_ERROR;
            if (!$installed) {
                $success = false;
            }
            $modx->log($level, '- Commerce version 0.11+: ' . (string)$commerce->version);
        }


        if ($success) {
            $modx->log(xPDO::LOG_LEVEL_INFO, 'Requirements look good! Visit Extras > Commerce > Configuration > Commerce after installation to enable the module.');
        }
        else {
            $modx->log(xPDO::LOG_LEVEL_ERROR, 'Unfortunately not all requirements have been met. Please correct the missing requirements, listed above, and run the install again.');
        }

        break;
    case xPDOTransport::ACTION_UNINSTALL:
        $success = true;
        break;
}
return $success;