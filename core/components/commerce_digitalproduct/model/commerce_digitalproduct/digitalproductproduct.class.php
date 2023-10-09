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
class DigitalproductProduct extends comProduct
{
    // May have been necessary previously, but it's not necessary to use the special type anymore.
    // Only benefit is hiding the weight/weight_unit fields but that's probably better handled in Commerce
    // itself at some point by moving those into the standard shipment type.
    public static $visibleType = false;

    public function getModelFields()
    {
        $fields = parent::getModelFields();

        foreach ($fields as $idx => $field) {
            // We don't need weight or weight unit on digital products.
            if ($field->getName() === 'weight' || $field->getName() === 'weight_unit') {
                unset($fields[$idx]);
            }
        }

        return $fields;
    }
}
