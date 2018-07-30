<?php

$chunks = array();
$chunks[0] = $modx->newObject('modChunk');
$chunks[0]->fromArray(array(
    'id' => 0,
    'name' => 'digitalproduct.file_error',
    'description' => 'Default error template for Commerce Digital Product.',
    'snippet' => getChunkContent($sources['source_core'] . '/elements/chunks/digitalproduct.file_error.tpl'),
), '', true, true);
return $chunks;