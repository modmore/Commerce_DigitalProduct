<?php
namespace RogueClarity\Digitalproduct\Modules;
use modmore\Commerce\Modules\BaseModule;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use modmore\Commerce\Events\Checkout;
use modmore\Commerce\Events\Payment;

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

        $dispatcher->addListener(\Commerce::EVENT_ORDER_PAYMENT_RECEIVED, [$this, 'getDigitalProducts']);
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

    public function getDigitalProducts(Payment $event)
    {
        $order = $event->getOrder();
        $digitalProducts = $this->processDigitalProducts($order);

        $order->setProperty('digital_items', $digitalProducts);
    }

    /**
     * Computes digital products
     *
     * @param comOrder $order
     * @return array
     */
    public function processDigitalProducts($order)
    {
        $output = [];
        $orderItems = $order->getItems();
        $user = $this->adapter->getUser();

        foreach ($orderItems as $orderItem) {
            // Determine if item is a digital product
            $deliveryType = $orderItem->getOne('DeliveryType');
            if ($deliveryType->get('shipment_type') !== 'DigitalproductOrderShipment') {
                continue;
            }

            $product = $orderItem->getProduct();

            // Add the product to the digitalproduct table for tracking
            $digitalProduct = $this->adapter->newObject('Digitalproduct', [
                'order' => $order->get('id'),
                'product' => $product->get('id'),
                'user' => $user ? $user->get('id') : 0,
            ]);
            $digitalProduct->save();

            // Get the digital items
            $resources = $this->getDigitalProductResources($product, $digitalProduct);
            $files = $this->getDigitalProductFiles($product, $digitalProduct);
            $all = array_merge($resources, $files);

            // In twig, you can see which by checking for an empty array.
            $output[] = [
                'resources' => $resources,
                'files' => $files,
                'all' => $all,
                'product' => $product->toArray()
            ];

            // Joins the user to the product's usergroup if they are logged in
            if ($user && $product->getProperty('usergroup')) {
                $user->joinGroup((int) $product->getProperty('usergroup'));
            }
        }
        
        return $output;
    }

    /**
     * Gets resources attached to the product.
     *
     * @param comProduct $product
     * @param Digitalproduct $digitalProduct object
     * @return array
     */
    public function getDigitalProductResources($product, $digitalProduct) {
        $output = [];
        $resources = $product->getProperty('resources');

        foreach ((array)$resources as $resource) {
            if ($resource) {
                $page = $this->adapter->getObject('modResource', $resource);

                if (!$page) {
                    $this->adapter->log(1, '[Digitalproduct] Could not find resource with ID of ' . $resource);
                    continue;
                }

                $digitalProductFile = $this->adapter->newObject('DigitalproductFile', [
                    'digitalproduct_id' => $digitalProduct->get('id'),
                    'name' => $page->get('pagetitle'), //@todo, make custom setting. Maybe let it be set by TV?
                    'file' => $page->get('id'),
                    'download_method' => $product->getProperty('download_method'),
                    'download_expiry' => $this->getDownloadExpiry($product),
                    'download_limit' => $this->getDownloadLimit($product),
                    'secret' => $this->generateSecret()
                ]);
                $digitalProductFile->save();

                $output[] = $digitalProductFile->toArray();
            }
        }

        return $output;
    }

    /**
     * Gets files attached to the product.
     * 
     * @param comProduct $product
     * @return array
     */
    public function getDigitalProductFiles($product, $digitalProduct) {
        $output = [];
        $files = $product->getProperty('files');

        foreach ((array)$files as $file) {
            if ($file['display_name'] && $file['url']) {
                $output[] = [
                    'display_name' => $file['display_name'],
                    'url' => $file['url']
                ];

                $digitalProductFile = $this->adapter->newObject('DigitalproductFile', [
                    'digitalproduct_id' => $digitalProduct->get('id'),
                    'name' => $file['display_name'],
                    'file' => $file['url'],
                    'download_method' => $product->getProperty('download_method'),
                    'download_expiry' => $this->getDownloadExpiry($product),
                    'download_limit' => $this->getDownloadLimit($product),
                    'secret' => $this->generateSecret()
                ]);
                $digitalProductFile->save();

                $output[] = $digitalProductFile->toArray();
            }
        }

        return $output;
    }

    /**
     * Computes the expiration of a product
     *
     * @param comProduct $product
     * @return int
     */
    public function getDownloadExpiry($product)
    {
        $expiration = $product->getProperty('download_expiry');
        return $expiration ? strtotime($expiration) : 0;
    }

    /**
     * Gets the download limit of a product
     *
     * @param comProduct $product
     * @return int
     */
    public function getDownloadLimit($product)
    {
        $limit = $product->getProperty('download_limit');
        return $limit ?: 0;
    }

    /**
     * Generates secret to use for tracking and viewing order products.
     *
     * @return string
     */
    public function generateSecret($secret = null, $bytes = 40, $check = true)
    {
        // Allow future customization of secret for custom downloads. 
        if (!$secret) {
            // $secret = random_bytes($bytes);
            $secret = bin2hex(openssl_random_pseudo_bytes($bytes));
        }
        // Check to ensure random generated string has not been used before
        if ($check) {
            $query = $this->adapter->getObject('DigitalproductFile', ['secret' => $secret]);

            if ($query) {
                // Generate a new one if it is being used.
                $secret = $this->generateSecret($bytes, $check);
            }
        }
        return $secret;
    }

    public function getModuleConfiguration(\comModule $module)
    {
        $fields = [];
        return $fields;
    }
}
