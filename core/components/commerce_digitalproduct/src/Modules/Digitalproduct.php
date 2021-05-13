<?php

namespace modmore\Commerce_DigitalProduct\Modules;

use modmore\Commerce\Events\OrderPlaceholders;
use modmore\Commerce\Events\MessagePlaceholders;
use modmore\Commerce\Modules\BaseModule;
use Symfony\Component\EventDispatcher\EventDispatcher;
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
        $dispatcher->addListener(\Commerce::EVENT_ORDER_PLACEHOLDERS, [$this, 'addGetOrderPlaceholders']);
        $dispatcher->addListener(\Commerce::EVENT_ORDER_MESSAGE_PLACEHOLDERS, [$this, 'addMessagePlaceholders']);

        // Add the xPDO package, so Commerce can detect the derivative classes
        $root = dirname(dirname(__DIR__));
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

    public function addMessagePlaceholders(MessagePlaceholders $event): void
    {
        $event->setPlaceholder('digitalProducts', $this->getPlaceholders($event->getOrder()));
    }

    public function getModuleConfiguration(\comModule $module)
    {
        $fields = [];
        return $fields;
    }

    private function getPlaceholders(\comOrder $order)
    {
        $c = $this->adapter->newQuery(\Digitalproduct::class);
        $c->where([
            'order' => $order->get('id'),
        ]);
        /** @var \Digitalproduct[] $items */

        $output = [];

        $items = $this->adapter->getIterator(\Digitalproduct::class, $c);
        foreach ($items as $item) {
            $product = $item->getProduct();
            $itemOutput =  [
                'all' => [],
                'resources' => [],
                'files' => [],
                'product' => $product ? $product->toArray() : [],
            ];
            /** @var \DigitalproductFile[] $files */
            $files = $item->getMany('File');
            foreach ($files as $file) {
                $url = $file->get('url');
                $data = $file->toArray();

                $itemOutput[ is_numeric($url) ? 'resources' : 'files' ][] = $data;
                $itemOutput['all'][] = $data;
            }

            $output[] = $itemOutput;
        }

        return $output;
    }

    public function addGetOrderPlaceholders(OrderPlaceholders $event) {
        $order = $event->getOrder();

        // If there are no digital products, we can stop here.
        if(!$order->getProperty('has_digital_products')) {
            return;
        }

        $c = $this->adapter->newQuery(\Digitalproduct::class);
        $c->where([
            'order' => $order->get('id'),
        ]);

        /** @var \Digitalproduct[] $digitalProducts */
        $digitalProducts = $this->adapter->getIterator(\Digitalproduct::class, $c);
        $phs = [];
        foreach ($digitalProducts as $digitalProduct) {

            $data = $digitalProduct->toArray();

            /** @var \DigitalproductFile[] $files */
            $files = $digitalProduct->getMany('File');
            foreach ($files as $file) {
                $data['files'][] = $file->toArray();
            }
            $phs[] = $data;
        }
        $event->setPlaceholder('digital_products',$phs);
    }
}
