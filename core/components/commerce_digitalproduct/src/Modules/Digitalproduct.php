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
        if (!($step instanceof ThankYou)) {
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

            $id = $product->get('id');
            // Get the resources attached to this product
            $resources = $this->getDigitalProductResources($product);
            if ($resources) {
                $output['resources'][$id] = [
                    'resources' => $resources,
                    'product' => $product->toArray(),
                ];
            }

            // Get the files attached to this product
            $files = $this->getDigitalProductFiles($product);
            if ($files) {
                $output['files'][$id] = [
                    'files' => $files,
                    'product' => $product->toArray(),
                ];
            }

            // Add the files/resources
            if ($resource || $files) {
                $digitalProductFile = $this->adapter->newObject('DigitalproductFile', [
                    'digitalproduct_id' => $digitalProduct->get('id'),
                    'resource' => $resources ? serialize($resources) : '',
                    'file' => $files ? serialize($files) : '',
                    'download_expiry' => $this->getDownloadExpiry($product),
                    'secret' => $this->generateSecret()
                ]);
                $digitalProductFile->save();

                if (!empty($digitalProductFile->get('file'))) {
                    $output['resources'][$id]['data'] = $digitalProductFile->toArray();
                }
                if (!empty($digitalProductFile->get('resource'))) {
                    $output['files'][$id]['data'] = $digitalProductFile->toArray();
                }

                // Also make these accessible in the all array in twig
                $output['all'][$id] = array_merge($resources, $files);
            }

            // Joins the user to the product's usergroup if they are logged in
            if ($user && $product->getProperty('usergroup')) {
                $user->joinGroup($product->getProperty('usergroup'));
            }
        }
        
        return $output;
    }

    /**
     * Gets resources attached to the product.
     *
     * @param comProduct $product
     * @return array
     */
    public function getDigitalProductResources($product) {
        $output = [];
        $resources = $product->getProperty('resources');

        foreach ((array)$resources as $resource) {
            if ($resource) {
                $page = $this->adapter->getObject('modResource', $resource);

                if ($page) {
                    $output[] = $page->toArray();
                }
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
    public function getDigitalProductFiles($product) {
        $output = [];
        $files = $product->getProperty('files');

        foreach ((array)$files as $file) {
            if ($file['display_name'] && $file['url']) {
                $output[] = [
                    'display_name' => $file['display_name'],
                    'url' => $file['url']
                ];
            }
        }

        return $output;
    }

    /**
     * Computes the expiration of the download
     *
     * @param [type] $product
     * @return void
     */
    public function getDownloadExpiry($product)
    {
        $expiration = $product->getProperty('download_expiry');

        if (!$expiration) {
            return 0;
        }

        return strtotime($expiration);
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
