<?php
namespace modmore\Commerce_DigitalProduct\Admin\Widgets\Form;

use modmore\Commerce\Admin\Widgets\Form\Field;

class FileLinksField extends Field
{
    public function isValidValue($value)
    {
        return is_string($value) || is_array($value);
    }

    public function setValue($value)
    {
        if (is_string($value) && $array = json_decode($value, true)) {
            $value = $array;
        }
        if (is_array($value)) {
            foreach ($value as $k => $item) {
                if (empty($item['url'])) {
                    unset($value[$k]);
                }
            }
        }
        $this->value = $value;

        return $this;
    }

    public function getHTML()
    {
        return $this->commerce->view()->render('digitalproduct/admin/widgets/fields/filelinks.twig', ['field' => $this]);
    }

    public function getValueAsArray()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            return $value;
        }
        if ($array = json_decode($value, true)) {
            return $array;
        }
        return [];
    }
}
