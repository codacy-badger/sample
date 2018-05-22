<?php return array (
  'area' => 
  array (
    'area_id' => 
    array (
      'type' => 'integer',
    ),
    'area_name' => 
    array (
      'type' => 'string',
      'fields' =>
      array (
        'keyword' =>
        array (
          'type' => 'keyword',
        ),
      ),
    ),
    'area_type' => 
    array (
      'type' => 'string',
    ),
    'area_parent' => 
    array (
      'type' => 'integer',
    ),
    'area_postal' => 
    array (
      'type' => 'integer',
    ),
    'area_location' => 
    array (
      'type' => 'geo_point',
    ),
    'area_flag' => 
    array (
      'type' => 'integer',
    ),
    'area_active' => 
    array (
      'type' => 'short',
    ),
    'area_created' => 
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'area_updated' => 
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
  ),
);