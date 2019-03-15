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
class Digitalproduct extends comSimpleObject
{
    protected $_order;
    protected $_shipment;
    protected $_product;
    protected $_user;

    /**
     * Gets the order instance this digital product was ordered in.
     *
     * @return comOrder
     */
    public function getOrder()
    {
        if (!$this->_order) {
            $this->_order = $this->adapter->getObject('comOrder', $this->get('order'));
        }

        return $this->_order;
    }

    /**
     * Gets the order shipment this digital product belongs to.
     *
     * @return comOrderShipment
     */
    public function getShipment()
    {
        if (!$this->_shipment) {
            $this->_shipment = $this->adapter->getObject('comOrderShipment', $this->get('shipment'));
        }

        return $this->_shipment;
    }

    /**
     * Gets the product that the digital product belongs to.
     *
     * @return comProduct
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->adapter->getObject('comProduct', $this->get('product'));
        }

        return $this->_product;
    }

    /**
     * Gets the user of the product.
     *
     * @return modUser|null
     */
    public function getUser()
    {
        if ($this->_user) {
            $this->_user = $this->get('user');
        }

        return $this->_user;
    }

}
