<?php

use modmore\Commerce\Admin\Widgets\Form\NumberField;
use modmore\Commerce\Admin\Widgets\Form\SelectField;
use modmore\Commerce\Admin\Widgets\Form\Tab;
use RogueClarity\Digitalproduct\Admin\Widgets\Form\ResourceField;
use RogueClarity\Digitalproduct\Admin\Widgets\Form\FileLinksField;

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
class DigitalproductOrderShipment extends comOrderShipment
{
    public static function getFieldsForProduct(Commerce $commerce, comProduct $product, comDeliveryType $deliveryType)
    {
        $fields = [];

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

        $fields[] = new SelectField($commerce, [
            'label' => $commerce->adapter->lexicon('commerce_digitalproduct.user_group'),
            'name' => 'properties[usergroup]',
            'description' => $commerce->adapter->lexicon('commerce_digitalproduct.user_group_desc'),
            'optionsClass' => 'modUserGroup',
            'emptyOption' => true,
            'value' => $product->getProperty('usergroup')
        ]);

        $fields[] = new Tab($commerce, [
            'label' => $commerce->adapter->lexicon('commerce_digitalproduct.product_tab')
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

        return $fields;
    }

    /**
     * Turns setting string into select options array
     *
     * @param [type] $setting
     * @return void
     */
    public static function explodeSetting($setting)
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
}
