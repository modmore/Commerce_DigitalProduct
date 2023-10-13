<?php

namespace modmore\Commerce_DigitalProduct\Admin\Widgets\Form;

use modmore\Commerce\Admin\Widgets\Form\DeliveryTypeField;

class SecondaryDeliveryTypeField extends DeliveryTypeField
{
    public function getDeliveryTypes()
    {
        $c = $this->adapter->newQuery('comDeliveryType');
        $c->where([
            'removed' => false,
            'shipment_type' => 'DigitalproductOrderShipment',
        ]);
        $c->sortby('name');

        $this->options[] = [
            'value' => 0,
            'label' => $this->adapter->lexicon('commerce_digitalproduct.no_secondary_delivery_type')
        ];

        foreach ($this->adapter->getIterator('comDeliveryType', $c) as $delType) {
            $this->options[] = [
                'value' => $delType->get('id'),
                'label' => $delType->get('name')
            ];
        }
    }
}
