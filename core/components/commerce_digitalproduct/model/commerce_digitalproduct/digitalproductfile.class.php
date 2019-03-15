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
class DigitalproductFile extends comSimpleObject
{
    protected $_digitalProduct;

    /**
     * Gets the digital product of this file.
     *
     * @return Digitalproduct
     */
    public function getDigitalProduct()
    {
        if (!$this->_digitalProduct) {
            $this->_digitalProduct = $this->adapter->getObject('Digitalproduct', $this->get('digitalproduct_id'));
        }

        return $this->_digitalProduct;
    }

    /**
     * If the user can access the file.
     *
     * @param boolean checks the user on the file
     * @param boolean checks the download count on the file
     * @param boolean checks the expiration date on the file
     * @return boolean
     */
    public function hasPermission($checkUser = false, $checkCount = true, $checkExpiry = true)
    {
        $product = $this->getDigitalProduct();
        $currentUser = $this->adapter->getUser() ? $this->adapter->getUser()->get('id') : 0;

        // Validate user is who bought the product
        if ($checkUser && $product->getUser() !== $currentUser) {
            return false;
        }

        // Check the download count
        if ($checkCount && $this->get('download_count') >= $this->get('download_limit') && $this->get('download_limit') !== 0) {
            return false;
        }

        // Check the expiry
        if ($checkExpiry && $this->get('download_expiry') !== 0 && $this->get('download_expiry') < time()) {
            return false;
        }

        return true;
    }

    /**
     * Returns the type of digital product
     *
     * @return string
     */
    public function getType()
    {
        if (intval($this->get('file'))) {
            return 'resource';
        }

        if (is_string($this->get('file'))) {
            return 'file';
        }

        $this->adapter->log(1, '[commerce_digitalproduct] Error in service class - could not determine type.');
        return 'unknown';
    }

    /**
     * Gets the file
     *
     * @return string URL
     */
    public function getFile()
    {
        return $this->get('file');
    }

    /**
     * Gets the download method
     *
     * @return void
     */
    public function getDownloadMethod()
    {
        return $this->get('download_method');
    }

    /**
     * Initiates the download. Recommended to call hasPermission before download for verification.
     *
     * @return string formatted url
     */
    public function download()
    {
        $downloadCount = $this->get('download_count');
        $this->set('download_count', $downloadCount + 1);
        $this->save();
    }
}
