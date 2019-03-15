<?php

/**
 * This snippet is part of the Commerce Digital Product module.
 * 
 * @author Tony Klapatch <tony@klapatch.net>
 */

$secret = $modx->getOption('secret', $_REQUEST, null);
$checkUser = (bool)$modx->getOption('checkUser', $scriptProperties, 0);
$checkCount = (bool)$modx->getOption('checkCount', $scriptProperties, 1);
$checkExpiry = (bool)$modx->getOption('checkExpiry', $scriptProperties, 1);
$errorTpl = $modx->getOption('errorTpl', $scriptProperties, 'digitalproduct.file_error');

$modx->lexicon->load('commerce_digtalproduct:default');

if (!$secret) {
    $modx->sendErrorPage();
}

// Initialize digital product service
$service = $modx->getService('digitalproductservice', 'DigitalproductService', $modx->getOption('commerce_digitalproduct.core_path', null, $modx->getOption('core_path') . 'components/commerce_digitalproduct/') . 'model/commerce_digitalproduct/', $scriptProperties);
if (!($service instanceof DigitalproductService)) return '';

// Try to get a file with the secret
$file = $service->getFile($secret);
if (!$file) {
    $modx->sendErrorPage();
}

// Check permissions
if (!$file->hasPermission($checkUser, $checkCount, $checkExpiry)) {
    return $modx->getChunk($errorTpl, [
        'error' => $modx->lexicon('commerce_digitalproduct.file_error_perms')
    ]);
}

// Gets URL path for files, integer for resources
$url = $file->getFile();
// Replaces whitespace with underscores for the file name when downloading
$fileName = preg_replace('/\s+/', '_', $file->get('name'));
$type = $file->getType();
$method = $file->getDownloadMethod();

switch ($method) {
    case 'redirect':
        $file->download();

        if ($type === 'resource') {
            $url = $modx->makeUrl($url, '', '', 'full');
        }
        $modx->sendRedirect($url);

        break;

    case 'force':
        $file->download();

        if ($type === 'resource') {
            $resource = $modx->getObject('modResource', $url);
            if (!$resource) {
                $modx->log(1, 'Error getting resource for digital product file ' . $file->get('id'));
                return $modx->sendErrorPage();
            }

            $resourceClass = $resource->get('class_key');
            
            // Get URL if resource is a web link
            if ($resourceClass === 'modWebLink' && filter_var($resource->get('content'), FILTER_VALIDATE_URL)) {
                $url = $resource->get('content');
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $result = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        $extension = pathinfo($url, PATHINFO_EXTENSION);
        header('Content-Disposition: attachment; filename="' . $fileName . '.' . $extension . '"');
        header('Content-Type: ' . $contentType);
        echo $result;
        exit();

        break;
        
    /* @todo For a future release
    
    case 'sendfile':
        $file->download();
        
        header('Content-Disposition: attachment; filename="' . $fileName . $extension . '"');
        header("Content-Type: ", true);
        header('X-Accel-Redirect: ' . $url);
        
        exit();
        break;
     */

    default:
        // Give the user the option to have their own download method via a hook, perhaps S3 + Cloudfront or some other generation
        $modx->runSnippet($method, [
            'file' => $file,
            'fileName' => $fileName,
            'type' => $type,
            'secret' => $secret,
            'checkUser' => $checkUser,
            'checkCount' => $checkCount,
            'checkExpiry' => $checkExpiry
        ]);
        break;
}