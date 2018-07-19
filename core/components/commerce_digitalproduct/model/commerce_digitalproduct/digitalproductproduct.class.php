<?php
use modmore\Commerce\Admin\Widgets\Form\TextField;
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

        $fields[] = new Tab($this->commerce, [
            'label' => $this->adapter->lexicon('commerce_digitalproduct.product_tab')
        ]);

        $fields[] = new ResourceField($this->commerce, [
            'label' => $this->adapter->lexicon('commerce_digitalproduct.resources'),
            'name' => 'properties[resources]',
            'description' => $this->adapter->lexicon('commerce_digitalproduct.resources_desc'),
            'value' => $this->getProperty('resources')
        ]);

        $fields[] = new FileLinksField($this->commerce, [
            'label' => $this->adapter->lexicon('commerce_digitalproduct.files'),
            'name' => 'properties[files]',
            'description' => $this->adapter->lexicon('commerce_digitalproduct.files_desc'),
            'value' => $this->getProperty('files')
        ]);

        return $fields;
    }
}
