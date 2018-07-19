<?php
use modmore\Commerce\Admin\Widgets\Form\NumberField;
use modmore\Commerce\Admin\Widgets\Form\SelectField;

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

        $fields[] = new NumberField($commerce, [
            'label' => $commerce->adapter->lexicon('commerce_digitalproduct.dl_limit'),
            'name' => 'properties[download_limit]',
            'description' => $commerce->adapter->lexicon('commerce_digitalproduct.dl_limit_desc'),
            'value' => $product->getProperty('download_limit')
        ]);

        // Get and format the set expirations options
        $expOptions = explode('||', $commerce->adapter->getOption('commerce_digitalproduct.expiration_times', null, ''));
        foreach ($expOptions as $expOption) {
            $time = explode('==', $expOption);
            $label = $time[0];
            $value = count($time) > 1 ? $time[1] : $time[0];

            $expTimes[] = [
                'label' => $label,
                'value' => $value
            ];
        }

        $fields[] = new SelectField($commerce, [
            'label' => $commerce->adapter->lexicon('commerce_digitalproduct.dl_expiry'),
            'name' => 'properties[download_expiry]',
            'options' => $expTimes,
            'description' => $commerce->adapter->lexicon('commerce_digitalproduct.dl_expiry_desc'),
            'value' => $product->getProperty('download_expiry')
        ]);

        // We're not using SelectField's modUserGroup options class because we also want a none selector 
        $modUserGroups = $commerce->adapter->getCollection('modUserGroup');
        $userGroups[] = [
            'label' => $commerce->adapter->lexicon('commerce_digitalproduct.user_group_none'),
            'value' => ''
        ];
        foreach ($modUserGroups as $modUserGroup) {
            $userGroups[] = [
                'label' => $modUserGroup->get('name'),
                'value' => $modUserGroup->get('id')
            ];
        }

        $fields[] = new SelectField($commerce, [
            'label' => $commerce->adapter->lexicon('commerce_digitalproduct.user_group'),
            'name' => 'properties[usergroup]',
            'description' => $commerce->adapter->lexicon('commerce_digitalproduct.user_group_desc'),
            'options' => $userGroups,
            'value' => $product->getProperty('usergroup')
        ]);

        return $fields;
    }
}
