<?php return array (
  'transaction' =>
  array (
    'transaction_id' =>
    array (
      'type' => 'integer',
    ),
    'transaction_active' =>
    array (
      'type' => 'short',
    ),
    'transaction_created' =>
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'transaction_updated' =>
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'transaction_paid_date' =>
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'transaction_profile' =>
    array (
      'type' => 'object',
    ),
    'transaction_currency' =>
    array (
      'type' => 'string',
    ),
    'transaction_total' =>
    array (
      'type' => 'integer',
    ),
    'transaction_credits' =>
    array (
      'type' => 'integer',
    ),
    'transaction_type' =>
    array (
      'type' => 'string',
    ),
    'transaction_flag' =>
    array (
      'type' => 'integer',
    ),
    'profile_image' =>
    array (
      'type' => 'string',
    ),
    'profile_name' =>
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
    'profile_email' =>
    array (
      'type' => 'string',
    ),
    'profile_phone' =>
    array (
      'type' => 'string',
    ),
    'profile_slug' =>
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
    'profile_credit' =>
    array (
      'type' => 'integer',
    ),
    'profile_detail' =>
    array (
      'type' => 'text',
      'fields' =>
      array (
        'keyword' =>
        array (
          'type' => 'keyword',
        ),
      ),
    ),
    'profile_company' =>
    array (
      'type' => 'string',
    ),
    'profile_gender' =>
    array (
      'type' => 'string',
    ),
    'profile_birth' =>
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd',
    ),
    'profile_website' =>
    array (
      'type' => 'string',
    ),
    'profile_facebook' =>
    array (
      'type' => 'string',
    ),
    'profile_linkedin' =>
    array (
      'type' => 'string',
    ),
    'profile_twitter' =>
    array (
      'type' => 'string',
    ),
    'profile_google' =>
    array (
      'type' => 'string',
    ),
    'profile_type' =>
    array (
      'type' => 'string',
    ),
    'profile_tags' =>
    array (
      'type' => 'string',
    ),
    'profile_story' =>
    array (
      'type' => 'string',
    ),
    'profile_campaigns' =>
    array (
      'type' => 'string',
    ),
    'profile_subscribe' =>
    array (
      'type' => 'integer',
    ),
    'profile_bounce' =>
    array (
      'type' => 'integer',
    ),
    'profile_flag' =>
    array (
      'type' => 'integer',
    ),
  ),
);
