<?php
/**
 * Digitalproduct for Commerce.
 *
 * Copyright 2018 by Tony Klapatch <tony.k@rogueclarity.com>
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
        if ($this->_digitalProduct) {
            return $this->_digitalProduct;
        }

        return $this->adapter->getObject('Digitalproduct', $this->get('digitalproduct_id'));
    }

    /**
     * If the user can access the file.
     *
     * @return boolean
     */
    public function hasPermission()
    {
        return true;
    }
}
