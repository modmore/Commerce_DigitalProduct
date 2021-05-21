<?php

use modmore\Commerce\Admin\Widgets\Form\NumberField;
use modmore\Commerce\Admin\Widgets\Form\SelectField;
use modmore\Commerce_DigitalProduct\Admin\Widgets\Form\ResourceField;
use modmore\Commerce_DigitalProduct\Admin\Widgets\Form\FileLinksField;

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
class DigitalproductOrderShipment extends comOrderShipment
{
    public static function getFieldsForProduct(Commerce $commerce, comProduct $product, comDeliveryType $deliveryType)
    {
        $fields = [];

        $fields[] = new SelectField($commerce, [
            'label' => $commerce->adapter->lexicon('commerce_digitalproduct.user_group'),
            'name' => 'properties[usergroup]',
            'description' => $commerce->adapter->lexicon('commerce_digitalproduct.user_group_desc'),
            'optionsClass' => 'modUserGroup',
            'emptyOption' => true,
            'value' => $product->getProperty('usergroup')
        ]);

        $fields[] = new ResourceField($commerce, [
            'label' => $commerce->adapter->lexicon('commerce_digitalproduct.resources'),
            'name' => 'properties[resources]',
            'description' => $commerce->adapter->lexicon('commerce_digitalproduct.resources_desc'),
            'value' => $product->getProperty('resources')
        ]);

        $fields[] = new FileLinksField($commerce, [
            'label' => $commerce->adapter->lexicon('commerce_digitalproduct.files'),
            'name' => 'properties[files]',
            'description' => $commerce->adapter->lexicon('commerce_digitalproduct.files_desc'),
            'value' => $product->getProperty('files')
        ]);

        // Get required options
        $expTimes = self::explodeSetting($commerce->adapter->getOption('commerce_digitalproduct.expiration_times', null, ''));
        $methods = self::explodeSetting($commerce->adapter->getOption('commerce_digitalproduct.download_methods', null, ''));

        $fields[] = new NumberField($commerce, [
            'label' => $commerce->adapter->lexicon('commerce_digitalproduct.dl_limit'),
            'name' => 'properties[download_limit]',
            'description' => $commerce->adapter->lexicon('commerce_digitalproduct.dl_limit_desc'),
            'value' => $product->getProperty('download_limit')
        ]);

        $fields[] = new SelectField($commerce, [
            'label' => $commerce->adapter->lexicon('commerce_digitalproduct.dl_expiry'),
            'name' => 'properties[download_expiry]',
            'options' => $expTimes,
            'description' => $commerce->adapter->lexicon('commerce_digitalproduct.dl_expiry_desc'),
            'value' => $product->getProperty('download_expiry')
        ]);

        $fields[] = new SelectField($commerce, [
            'label' => $commerce->adapter->lexicon('commerce_digitalproduct.dl_method'),
            'name' => 'properties[download_method]',
            'options' => $methods,
            'description' => $commerce->adapter->lexicon('commerce_digitalproduct.dl_method_desc'),
            'value' => $product->getProperty('download_method')
        ]);

        return $fields;
    }

    /**
     * Turns setting string into select options array
     *
     * @param [type] $setting
     * @return array
     */
    public static function explodeSetting($setting): array
    {
        $options = explode('||', $setting);
        $output = [];

        foreach ($options as $option) {
            $opt = explode('==', $option);
            $label = $opt[0];
            $value = count($opt) > 1 ? $opt[1] : $opt[0];

            $output[] = [
                'label' => $label,
                'value' => $value
            ];
        }

        return $output;
    }

    public function onOrderStateProcessing()
    {
        $order = $this->getOrder();
        $this->processDigitalProducts($order);

        $order->setProperty('has_digital_products', true);
        $order->save();

        return true;
    }

    /**
     * Computes digital products
     *
     * @param comOrder $order
     * @return array
     */
    private function processDigitalProducts($order)
    {
        $output = [];
        $orderItems = $order->getItems();
        $user = $order->getUser();

        foreach ($orderItems as $orderItem) {
            // Determine if item is a digital product
            $deliveryType = $orderItem->getOne('DeliveryType');
            if ($deliveryType->get('shipment_type') !== 'DigitalproductOrderShipment') {
                continue;
            }

            $product = $orderItem->getProduct();

            // Add the product to the digitalproduct table for tracking
            /** @var Digitalproduct $digitalProduct */
            $digitalProduct = $this->adapter->newObject('Digitalproduct', [
                'order' => $order->get('id'),
                'product' => $product->get('id'),
                'user' => $order->get('user'),
            ]);
            $digitalProduct->save();

            // Get the digital items
            $resources = $this->getDigitalProductResources($product, $digitalProduct);
            $files = $this->getDigitalProductFiles($product, $digitalProduct);
            $all = array_merge($resources, $files);

            // In twig, you can see which by checking for an empty array.
            $output[] = [
                'resources' => $resources,
                'files' => $files,
                'all' => $all,
                'product' => $product->toArray()
            ];

            // Joins the user to the product's usergroup if they are logged in
            if ($user && $product->getProperty('usergroup')) {
                $user->joinGroup((int)$product->getProperty('usergroup'));
            }
        }

        return $output;
    }

    /**
     * Gets resources attached to the product.
     *
     * @param comProduct $product
     * @param Digitalproduct $digitalProduct
     * @return array
     */
    private function getDigitalProductResources($product, $digitalProduct)
    {
        $output = [];
        $resources = $product->getProperty('resources');

        foreach ((array)$resources as $resource) {
            if ($resource) {
                $page = $this->adapter->getObject('modResource', (int)$resource);

                if (!$page) {
                    $this->adapter->log(1, '[Digitalproduct] Could not find resource with ID of ' . $resource);
                    continue;
                }

                $digitalProductFile = $this->adapter->newObject('DigitalproductFile', [
                    'digitalproduct_id' => $digitalProduct->get('id'),
                    'name' => $page->get('pagetitle'), //@todo, make custom setting. Maybe let it be set by TV?
                    'file' => $page->get('id'),
                    'download_method' => $product->getProperty('download_method'),
                    'download_expiry' => $this->getDownloadExpiry($product),
                    'download_limit' => $this->getDownloadLimit($product),
                    'secret' => $this->generateSecret()
                ]);
                $digitalProductFile->save();

                $output[] = $digitalProductFile->toArray();
            }
        }

        return $output;
    }

    /**
     * Gets files attached to the product.
     *
     * @param comProduct $product
     * @param Digitalproduct $digitalProduct
     * @return array
     */
    private function getDigitalProductFiles($product, $digitalProduct)
    {
        $output = [];
        $files = $product->getProperty('files');

        foreach ((array)$files as $file) {
            if ($file['display_name'] && $file['url']) {
                $output[] = [
                    'display_name' => $file['display_name'],
                    'url' => $file['url']
                ];

                $digitalProductFile = $this->adapter->newObject('DigitalproductFile', [
                    'digitalproduct_id' => $digitalProduct->get('id'),
                    'name' => $file['display_name'],
                    'file' => $file['url'],
                    'download_method' => $product->getProperty('download_method'),
                    'download_expiry' => $this->getDownloadExpiry($product),
                    'download_limit' => $this->getDownloadLimit($product),
                    'secret' => $this->generateSecret()
                ]);
                $digitalProductFile->save();

                $output[] = $digitalProductFile->toArray();
            }
        }

        return $output;
    }

    /**
     * Computes the expiration of a product
     *
     * @param comProduct $product
     * @return int
     */
    public function getDownloadExpiry($product)
    {
        $expiration = $product->getProperty('download_expiry');
        return $expiration ? strtotime($expiration) : 0;
    }

    /**
     * Gets the download limit of a product
     *
     * @param comProduct $product
     * @return int
     */
    public function getDownloadLimit($product)
    {
        $limit = $product->getProperty('download_limit');
        return $limit ? : 0;
    }

    /**
     * Generates secret to use for tracking and viewing order products.
     *
     * @return string
     */
    public function generateSecret($secret = null, $bytes = 40, $check = true)
    {
        // Allow future customization of secret for custom downloads.
        if (!$secret) {
             $secret = bin2hex(random_bytes($bytes));
        }
        // Check to ensure random generated string has not been used before
        if ($check) {
            $query = $this->adapter->getObject('DigitalproductFile', ['secret' => $secret]);

            if ($query) {
                // Generate a new one if it is being used.
                $secret = $this->generateSecret($bytes, $check);
            }
        }

        return $secret;
    }
}
