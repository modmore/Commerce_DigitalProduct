<?php
$xpdo_meta_map['Digitalproduct']= array (
  'package' => 'commerce_digitalproduct',
  'version' => '1.1',
  'table' => 'commerce_digitalproduct',
  'extends' => 'comSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'MyISAM',
  ),
  'fields' => 
  array (
    'order' => 0,
    'product' => 0,
    'user' => 0,
  ),
  'fieldMeta' => 
  array (
    'order' => 
    array (
      'dbtype' => 'int',
      'attributes' => 'unsigned',
      'precision' => '10',
      'phptype' => 'int',
      'null' => false,
      'default' => 0,
    ),
    'product' => 
    array (
      'dbtype' => 'int',
      'attributes' => 'unsigned',
      'precision' => '10',
      'phptype' => 'int',
      'null' => false,
      'default' => 0,
    ),
    'user' => 
    array (
      'dbtype' => 'int',
      'attributes' => 'unsigned',
      'precision' => '10',
      'phptype' => 'int',
      'null' => false,
      'default' => 0,
    ),
  ),
  'composites' => 
  array (
    'File' => 
    array (
      'class' => 'DigitalproductFile',
      'local' => 'id',
      'foreign' => 'digitalproduct_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
  'aggregates' => 
  array (
    'Order' => 
    array (
      'class' => 'comOrder',
      'local' => 'order',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'Product' => 
    array (
      'class' => 'comProduct',
      'local' => 'product',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'User' => 
    array (
      'class' => 'modUser',
      'local' => 'user',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
