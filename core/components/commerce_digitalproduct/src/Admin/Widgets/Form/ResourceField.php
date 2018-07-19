<?php
namespace RogueClarity\Digitalproduct\Admin\Widgets\Form;

use modmore\Commerce\Admin\Widgets\Form\SelectMultipleField;

class ResourceField extends SelectMultipleField
{
    /**
     * Fetches all resources under the selected parents in system settings.
     * Ideally we don't want the user to have to configure parents in the system settings.
     * Performance with large sites leaves alot to be desired.
     * This is something that can be enhanced after release.
     *
     * @return array
     */
    public function getOptions()
    {
        $parents = $this->adapter->getOption('commerce_digitalproduct.resource_parents');

        if (!$parents) {
            return [];
        }

        // Get only resources that the user wants
        $parents = array_map('trim', explode(',', $parents));
        $resources = $this->adapter->getCollection('modResource', [
            'parent:IN' => $parents,
            'published' => 1,
            'deleted' => 0
        ]);

        if (!$resources) {
            return [];
        }

        foreach ($resources as $resource) {
            $options[] = [
                // Let users search by pagetitle or ID in select search box
                'label' => $resource->get('pagetitle') . ' (' . $resource->get('id') . ')',
                'value' => $resource->get('id')
            ];
        }

        return $options;
    }
}