<?php
/**
 * Digitalproduct for Commerce.
 *
 * Copyright 2018 by Tony Klapatch <tony.k@rogueclarity.com>
 *
 * This file is meant to be used with Commerce by modmore. A valid Commerce license is required.
 *
 * @package commerce_digitalproduct
 * @license See core/components/commerce_digitalproduct/docs/license.txt
 */

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
    'secret' => '',
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
    'secret' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '190',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
  ),
  'composites' => 
  array (
    'File' => 
    array (
      'class' => 'DigitalproductFile',
      'local' => 'id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
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
