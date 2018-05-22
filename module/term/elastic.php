<?php return array (
  'term' => 
  array (
    'term_id' => 
    array (
      'type' => 'integer',
    ),
    'term_active' => 
    array (
      'type' => 'short',
    ),
    'term_created' => 
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'term_updated' => 
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'term_name' => 
    array (
      'type' => 'string',
      'fields' => 
      array (
        'keyword' =>  
        array (
          'type' => 'keyword',
        ),
      ),
      'analyzer' => 'simple',
    ),
    'term_hits' => 
    array (
      'type' => 'integer',
    ),
    'term_type' => 
    array (
      'type' => 'string',
    ),
    'term_flag' => 
    array (
      'type' => 'integer',
    ),
  ),
);
