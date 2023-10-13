<?php

use modmore\Commerce\Admin\Widgets\Form\DeliveryTypeField;
use modmore\Commerce_DigitalProduct\Admin\Widgets\Form\SecondaryDeliveryTypeField;

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
class DigitalProductBundle extends comProductBundle
{
    public function getModelFields()
    {
        $fields = parent::getModelFields();

        foreach ($fields as $k => $field) {
            if ($field instanceof DeliveryTypeField && $field->getName() === 'delivery_type') {
                // Alter delivery type label and description
                $field->setLabel($this->adapter->lexicon('commerce_digitalproduct.primary_delivery_type'));
                $field->setDescription($this->adapter->lexicon('commerce_digitalproduct.primary_delivery_type.field_desc'));

                // Add secondary delivery type (digital only)
                array_splice( $fields, $k + 1, 0, [
                    new SecondaryDeliveryTypeField($this->commerce, [
                        'name' => 'properties[digital_bundle_delivery_type]',
                        'label' => $this->adapter->lexicon('commerce_digitalproduct.secondary_delivery_type'),
                        'description' => $this->adapter->lexicon('commerce_digitalproduct.secondary_delivery_type.field_desc'),
                        'value' => $this->getProperty('digital_bundle_delivery_type'),
                    ])
                ]);
            }
        }

        return $fields;
    }
}
