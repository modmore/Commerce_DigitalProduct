<?php
/**
 * @var modX $modx
 * @var array $scriptProperties
 */

$userId = $modx->user && $modx->user->get('id') > 0 ? $modx->user->get('id') : false;
if (!$userId) {
    $modx->sendUnauthorizedPage();
    return;
}

// Instantiate the Commerce class
$path = $modx->getOption('commerce.core_path', null, MODX_CORE_PATH . 'components/commerce/') . 'model/commerce/';
$params = ['mode' => $modx->getOption('commerce.mode')];
/** @var Commerce|null $commerce */
$commerce = $modx->getService('commerce', 'Commerce', $path, $params);
if (!($commerce instanceof Commerce)) {
    return '<p class="error">Oops! It is not possible to view your cart currently. We\'re sorry for the inconvenience. Please try again later.</p>';
}

// Get our digitalproduct service class
$corePath = $modx->getOption('commerce_digitalproduct.core_path', null, $modx->getOption('core_path') . 'components/commerce_digitalproduct/');
$service = $modx->getService('digitalproductservice', 'DigitalproductService', $corePath . 'model/commerce_digitalproduct/');
if (!($service instanceof DigitalproductService)) {
    return 'Could not load Digital Product service.';
}


$tpl = $modx->getOption('tpl', $scriptProperties, 'digitalproduct/user_files.twig');

$c = $modx->newQuery(Digitalproduct::class);
$c->where([
    'user' => $userId,
]);

$output = [];
/** @var Digitalproduct $item */
foreach ($modx->getIterator(Digitalproduct::class, $c) as $item) {
    $itemOutput = $item->getPlaceholders();
    $itemOutput['order'] = $item->getOrder() ? $item->getOrder()->toArray() : [];
    $output[] = $itemOutput;
}

return $commerce->view()->render($tpl, [
    'digitalProducts' => $output,
]);
