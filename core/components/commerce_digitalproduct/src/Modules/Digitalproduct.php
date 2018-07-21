<?php
namespace RogueClarity\Digitalproduct\Modules;
use modmore\Commerce\Modules\BaseModule;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use modmore\Commerce\Events\Checkout;
use modmore\Commerce\Frontend\Steps\ThankYou;
use modmore\Commerce\Frontend\Steps\Payment;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

class Digitalproduct extends BaseModule {

    public function getName()
    {
        $this->adapter->loadLexicon('commerce_digitalproduct:default');
        return $this->adapter->lexicon('commerce_digitalproduct');
    }

    public function getAuthor()
    {
        return 'Tony Klapatch - Rogue Clarity';
    }

    public function getDescription()
    {
        return $this->adapter->lexicon('commerce_digitalproduct.description');
    }

    public function initialize(EventDispatcher $dispatcher)
    {
        // Load our lexicon
        $this->adapter->loadLexicon('commerce_digitalproduct:default');

        $dispatcher->addListener(\Commerce::EVENT_CHECKOUT_AFTER_STEP, [$this, 'getDigitalProducts']);

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

    public function getDigitalProducts(Checkout $event)
    {
        $step = $event->getStep();
        //if (!($step instanceof ThankYou)) {
        if (!($step instanceof Payment)) {
            return;
        }

        $order = $event->getOrder();
        if ($order->getState() !== \comOrder::STATE_CART) {
            return;
        }

        $digitalProducts = $this->processDigitalProducts($order);

        $step->setPlaceholder('digitalProducts', $digitalProducts);
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
            $product = $orderItem->getProduct();
            // This could support class variations in the future?
            if ($product->get('class_key') !== 'DigitalproductProduct') {
                continue;
            }

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
                $user->joinGroup(intval($product->getProperty('usergroup')));
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
                    continue;
                }

                $digitalProductFile = $this->adapter->newObject('DigitalproductFile', [
                    'digitalproduct_id' => $digitalProduct->get('id'),
                    'name' => $page->get('pagetitle'), //@todo, make custom setting. Maybe let it be set by TV?
                    'resource' => $page->get('id'),
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
        return $limit ? $limit : 0;
    }

    /**
     * Generates secret to use for tracking and viewing order products.
     * @todo support modifying bytes via system setting
     * 
     * @return string
     */
    public function generateSecret($secret = null, $bytes = 20, $check = true)
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
