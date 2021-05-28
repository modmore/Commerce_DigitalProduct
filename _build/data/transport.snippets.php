<?php

$snippets = array();
$snippets[0] = $modx->newObject('modSnippet');
$snippets[0]->fromArray([
    'name' => 'digitalproduct.get_file',
    'description' => 'Insert into a resource uncached to get a file based on secret.',
    'snippet' => getSnippetContent($sources['source_core'] . '/elements/snippets/digitalproduct.get_file.php'),
], '', true, true);

$snippets[1] = $modx->newObject('modSnippet');
$snippets[1]->fromArray([
    'name' => 'digitalproduct.get_user_files',
    'description' => 'Place on an access-protected resource to list the logged-in users\' previously purchased digital products.',
    'snippet' => getSnippetContent($sources['source_core'] . '/elements/snippets/digitalproduct.get_user_files.php'),
], '', true, true);
return $snippets;
