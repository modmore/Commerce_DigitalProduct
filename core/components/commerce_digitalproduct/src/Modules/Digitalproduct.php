<?php

namespace RogueClarity\Digitalproduct\Modules;

use modmore\Commerce\Modules\BaseModule;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use modmore\Commerce\Events\Checkout;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

class Digitalproduct extends BaseModule {

    public function getName()
    {
        $this->adapter->loadLexicon('commerce_digitalproduct:default');
        return $this->adapter->lexicon('commerce_digitalproduct');
    }

    public function getAuthor()
    {
        return 'Tony Klapatch';
    }

    public function getDescription()
    {
        return $this->adapter->lexicon('commerce_digitalproduct.description');
    }

    public function initialize(EventDispatcher $dispatcher)
    {
        // Load our lexicon
        $this->adapter->loadLexicon('commerce_digitalproduct:default');

        $dispatcher->addListener(\Commerce::EVENT_CHECKOUT_AFTER_STEP, [$this, 'addCheckoutPlaceholders']);

        // Add the xPDO package, so Commerce can detect the derivative classes
        $root = dirname(dirname(__DIR__));
        $path = $root . '/model/';
        $this->adapter->loadPackage('commerce_digitalproduct', $path);

        // Add template path to twig
        /** @var ChainLoader $loader */
        $root = dirname(dirname(__DIR__));
        $loader = $this->commerce->twig->getLoader();
        $loader->addLoader(new FilesystemLoader($root . '/templates/'));

        // Load class for static methods.
        $this->adapter->loadClass('DigitalproductOrderShipment', $path . 'commerce_digitalproduct/');
    }

    /**
     * Get the digital product order items inside an order
     *
     * @param \comOrder $order
     * @return array
     */
    public function getOrderDigitalItems(\comOrder $order)
    {
        $items = $order->getItems();
        $digitalItems = [];

        foreach ($items as $item) {
            $deliveryType = $item->getOne('DeliveryType');
            if (!$deliveryType || $deliveryType->get('shipment_type') !== 'DigitalproductOrderShipment') {
                continue;
            }

            $digitalItems[] = $item;
        }

        return $digitalItems;
    }

    /**
     * Add placeholders to checkout for templating
     *
     * @param Checkout $event
     * @return void
     */
    public function addCheckoutPlaceholders(Checkout $event)
    {
        $step = $event->getStep();
        $order = $event->getOrder();

        $digitalItems = $this->getOrderDigitalItems($order);

        if (empty($digitalItems)) {
            return;
        }

        // Allow for templating cart/checkout order items for digital products
        foreach ($digitalItems as $item) {
            $items[$item->get('id')] = true;
        }

        $step->setPlaceholder('digital_items', $items);
    }

    public function getModuleConfiguration(\comModule $module)
    {
        $fields = [];
        return $fields;
    }
}
