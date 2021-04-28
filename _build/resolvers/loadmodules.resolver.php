<?php
/* @var modX $modx */

if ($object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_UPGRADE:
        case xPDOTransport::ACTION_INSTALL:
            $modx =& $object->xpdo;

            $corePath = $modx->getOption('commerce.core_path', null, $modx->getOption('core_path') . 'components/commerce/');
            $commerce = $modx->getService('commerce', 'Commerce', $corePath . 'model/commerce/' , ['isSetup' => true]);


            // Update the v1.1- module, if it exists
            $oldModule = $modx->getObject('comModule', ['class_name' => 'RogueClarity\Digitalproduct\Modules\Digitalproduct']);
            if ($oldModule) {
                $oldModule->set('class_name', \modmore\Commerce_DigitalProduct\Modules\Digitalproduct::class);
                $oldModule->save();
                $modx->log(modX::LOG_LEVEL_WARN, 'Migrated module from the v1.1 (or before) class name to the new class name since v1.2');
            }

            // Add the database tables
            $modx->log(modX::LOG_LEVEL_INFO, 'Creating database tables...');
            $modelPath = $modx->getOption('core_path').'components/commerce_digitalproduct/model/';
            $modx->addPackage('commerce_digitalproduct', $modelPath);
            $manager = $modx->getManager();
            $manager->createObjectContainer('Digitalproduct');
            $manager->createObjectContainer('DigitalproductFile');

            // Load the module
            $modx->log(modX::LOG_LEVEL_INFO, 'Loading/updating available modules...');
            if ($commerce instanceof Commerce) {
                // Grab the path to our namespaced files
                $basePath = $modx->getOption('core_path') . 'components/commerce_digitalproduct/';
                include $basePath . 'vendor/autoload.php';
                $modulePath = $basePath . 'src/Modules/';
                // Instruct Commerce to load modules from our directory, providing the base namespace and module path twice
                $commerce->loadModulesFromDirectory($modulePath, 'modmore\\Commerce_DigitalProduct\\Modules\\', $modulePath);
                $modx->log(modX::LOG_LEVEL_INFO, 'Synchronised modules.');
            } else {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Could not load Commerce service to load module');
            }

        break;
    }
}
return true;

