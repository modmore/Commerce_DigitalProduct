<?php

namespace modmore\Commerce_DigitalProduct\Modules;

use modmore\Commerce\Events\OrderPlaceholders;
use modmore\Commerce\Events\MessagePlaceholders;
use modmore\Commerce\Modules\BaseModule;
use modmore\Commerce\Dispatcher\EventDispatcher;
use modmore\Commerce\Events\Checkout;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

class Digitalproduct extends BaseModule {

    public function getName()
    {
        $this->adapter->loadLexicon('commerce_digitalproduct:default');
        return $this->adapter->lexicon('commerce_digitalproduct');
    }

    public function getAuthor()
    {
        return 'modmore (originally by Tony Klapatch)';
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
        $dispatcher->addListener(\Commerce::EVENT_ORDER_MESSAGE_PLACEHOLDERS, [$this, 'addMessagePlaceholders']);
        // Check if we're on Commerce 1.3+ to add placeholders to the get_order(s) snippet
        if (defined('\Commerce::EVENT_ORDER_PLACEHOLDERS')) {
            $dispatcher->addListener(\Commerce::EVENT_ORDER_PLACEHOLDERS, [$this, 'addGetOrderPlaceholders']);
        }

        // Add the xPDO package, so Commerce can detect the derivative classes
        $root = dirname(__DIR__, 2);
        $path = $root . '/model/';
        $this->adapter->loadPackage('commerce_digitalproduct', $path);

        // Add template path to the Commerce view
        $this->commerce->view()->addTemplatesPath($root . '/templates/');

        // Load model class for access to static methods.
        $this->adapter->loadClass('DigitalproductOrderShipment', $path . 'commerce_digitalproduct/');
    }

    /**
     * Add placeholders to checkout for templating
     *
     * @param Checkout $event
     * @return void
     */
    public function addCheckoutPlaceholders(Checkout $event)
    {
        if ($event->getStepKey() !== 'thank-you') {
            return;
        }
        $step = $event->getStep();

        $step->setPlaceholder('digitalProducts', $this->getPlaceholders($event->getOrder()));
    }

    /**
     * Add same placeholders to order messages
     *
     * @param MessagePlaceholders $event
     */
    public function addMessagePlaceholders(MessagePlaceholders $event): void
    {
        $event->setPlaceholder('digitalProducts', $this->getPlaceholders($event->getOrder()));
    }

    /**
     * Add same placeholders to the commerce.get_order(s) snippet output (in Commerce v1.3+)
     *
     * @param OrderPlaceholders $event
     */
    public function addGetOrderPlaceholders(OrderPlaceholders $event): void
    {
        $event->setPlaceholder('digitalProducts', $this->getPlaceholders($event->getOrder()));
    }

    private function getPlaceholders(\comOrder $order): array
    {
        $c = $this->adapter->newQuery(\Digitalproduct::class);
        $c->where([
            'order' => $order->get('id'),
        ]);

        /** @var \Digitalproduct[] $items */
        $items = $this->adapter->getIterator(\Digitalproduct::class, $c);

        $output = [];
        foreach ($items as $item) {
            $output[] = $item->getPlaceholders();
        }

        return $output;
    }
}
