<?php

use modmore\Commerce\Admin\Widgets\Form\TextField;
use modmore\Commerce\Admin\Widgets\Form\Tab;

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
