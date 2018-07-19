<?php
/* @var modX $modx */

if ($object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_UPGRADE:
        case xPDOTransport::ACTION_INSTALL:
            $modx =& $object->xpdo;

            $modx->log(modX::LOG_LEVEL_INFO, 'Loading/updating available modules...');

            $corePath = $modx->getOption('commerce.core_path', null, $modx->getOption('core_path') . 'components/commerce/');
            $commerce = $modx->getService('commerce', 'Commerce', $corePath . 'model/commerce/' , ['isSetup' => true]);
            if ($commerce instanceof Commerce) {
                // Grab the path to our namespaced files
                $basePath = $modx->getOption('core_path') . 'components/commerce_digitalproduct/';
                include $basePath . 'vendor/autoload.php';
                $modulePath = $basePath . 'src/Modules/';
                // Instruct Commerce to load modules from our directory, providing the base namespace and module path twice
                $commerce->loadModulesFromDirectory($modulePath, 'RogueClarity\\Digitalproduct\\Modules\\', $modulePath);
                $modx->log(modX::LOG_LEVEL_INFO, 'Synchronised modules.');
            }
            else {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Could not load Commerce service to load module');
            }

        break;
    }

}
return true;

