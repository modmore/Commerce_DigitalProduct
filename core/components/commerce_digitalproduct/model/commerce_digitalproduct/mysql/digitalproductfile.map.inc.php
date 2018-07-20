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

$xpdo_meta_map['DigitalproductFile']= array (
  'package' => 'commerce_digitalproduct',
  'version' => '1.1',
  'table' => 'commerce_digitalproduct_file',
  'extends' => 'comSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'MyISAM',
  ),
  'fields' => 
  array (
    'digitalproduct_id' => NULL,
    'secret' => '',
    'resource' => NULL,
    'file' => NULL,
    'download_count' => 0,
    'download_expiry' => 0,
  ),
  'fieldMeta' => 
  array (
    'digitalproduct_id' => 
    array (
      'dbtype' => 'int',
      'attributes' => 'unsigned',
      'precision' => '10',
      'phptype' => 'int',
      'null' => false,
    ),
    'secret' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '190',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'resource' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
    ),
    'file' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
    ),
    'download_count' => 
    array (
      'dbtype' => 'int',
      'attributes' => 'unsigned',
      'precision' => '10',
      'phptype' => 'int',
      'null' => false,
      'default' => 0,
    ),
    'download_expiry' => 
    array (
      'dbtype' => 'int',
      'attributes' => 'unsigned',
      'precision' => '10',
      'phptype' => 'int',
      'null' => false,
      'default' => 0,
    ),
  ),
  'aggregates' => 
  array (
    'resource' => 
    array (
      'class' => 'modResource',
      'local' => 'resource',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'Digitalproduct' => 
    array (
      'class' => 'Digitalproduct',
      'local' => 'digitalproduct_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
