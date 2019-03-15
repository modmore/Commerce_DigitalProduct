<?php
/**
 * Digitalproduct for Commerce.
 *
 * Copyright 2019 by Tony Klapatch <tony@klapatch.net>
 *
 * This file is meant to be used with Commerce by modmore. A valid Commerce license is required.
 *
 * @package commerce_digitalproduct
 * @license See core/components/commerce_digitalproduct/docs/license.txt
 */
class DigitalproductService
{
    public $modx;
    public $user;
    public $commerce;
    public $config = [];

    public function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $this->user = $this->modx->user;

        $corePath = $this->modx->getOption('commerce_digitalproduct.core_path', $config, $this->modx->getOption('core_path') . 'components/commerce_digitalproduct/');
        $assetsUrl = $this->modx->getOption('commerce_digitalproduct.assets_url', $config, $this->modx->getOption('assets_url') . 'components/commerce_digitalproduct/');
        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',
            'controllersPath' => $corePath . 'controllers/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'baseUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
            'connectorUrl' => $assetsUrl . 'connector.php'
        ]);

        $this->modx->addPackage('commerce_digitalproduct', $this->config['modelPath']);

        $commercePath = $this->modx->getOption('commerce.core_path', null, $this->modx->getOption('core_path') . 'components/commerce/') . 'model/commerce/';
        $this->commerce = $this->modx->getService('commerce', 'Commerce', $commercePath, ['mode' => $this->modx->getOption('commerce.mode')]);
    }

    /**
     * Returns user id
     *
     * @return int
     */
    public function getUser()
    {
        return $this->user->get('id');
    }

    /**
     * Gets a single digital product based on secret
     *
     * @param string $secret
     * @return DigitalproductFile|null
     */
    public function getFile($secret)
    {
        return $this->modx->getObject('DigitalproductFile', [
            'secret' => $secret
        ]);
    }

    /**
     * Gets a user's files based on user id
     *
     * @param [type] $userId
     * @return DigitalproductFile[]
     */
    public function getUserFiles($userId)
    {
        $files = $this->modx->newQuery('DigitalproductFile');
        $files->innerJoin('Digitalproduct');
        $files->where([
            'Digitalproduct.user' => $userId
        ]);

        return $this->modx->getCollection('DigitalproductFile', $files);
    }
}