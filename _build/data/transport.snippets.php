<?php

$snippets = array();
$snippets[0] = $modx->newObject('modSnippet');
$snippets[0]->fromArray(array(
    'id' => 0,
    'name' => 'digitalproduct.get_file',
    'description' => 'Insert into a resource uncached to get a file based on secret.',
    'snippet' => getSnippetContent($sources['source_core'] . '/elements/snippets/digitalproduct.get_file.php'),
), '', true, true);
return $snippets;