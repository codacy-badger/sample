<?php return array (
  'post' =>
  array (
    'post_id' =>
    array (
      'type' => 'integer',
    ),
    'post_active' =>
    array (
      'type' => 'short',
    ),
    'post_created' =>
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'post_updated' =>
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'post_name' =>
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
    'post_email' =>
    array (
      'type' => 'string',
    ),
    'post_phone' =>
    array (
      'type' => 'string',
    ),
    'post_position' =>
    array (
      'type' => 'string',
      'analyzer' => 'simple',
    ),
    'post_location' =>
    array (
      'type' => 'string',
      'analyzer' => 'simple',
    ),
    'post_geo_location' =>
    array (
      'type' => 'geo_point',
    ),
    'post_experience' =>
    array (
      'type' => 'integer',
    ),
    'post_resume' =>
    array (
      'type' => 'string',
    ),
    'post_detail' =>
    array (
      'type' => 'text',
      'fields' =>
      array (
        'keyword' =>
        array (
          'type' => 'keyword',
          'index' => 'not_analyzed',
          'ignore_above' => 20,
        ),
      ),
    ),
    'post_notify' =>
    array (
      'type' => 'string',
    ),
    'post_expires' =>
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'post_banner' =>
    array (
      'type' => 'string',
    ),
    'post_salary_min' =>
    array (
      'type' => 'integer',
    ),
    'post_salary_max' =>
    array (
      'type' => 'integer',
    ),
    'post_link' =>
    array (
      'type' => 'string',
    ),
    'post_like_count' =>
    array (
      'type' => 'integer',
    ),
    'post_download_count' =>
    array (
      'type' => 'integer',
    ),
    'post_email_count' =>
    array (
      'type' => 'integer',
    ),
    'post_phone_count' =>
    array (
      'type' => 'integer',
    ),
    'post_tags' =>
    array (
      'type' => 'string',
    ),
    'post_arrangement' =>
    array (
      'type' => 'string',
    ),
    'post_type' =>
    array (
      'type' => 'string',
      'fielddata' => true
    ),
    'post_flag' =>
    array (
      'type' => 'integer',
    ),
    'post_view' =>
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
    'profile_credits' =>
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
    'profile_address_street' =>
    array (
      'type' => 'string',
    ),
    'profile_address_city' =>
    array (
      'type' => 'string',
    ),
    'profile_address_state' =>
    array (
      'type' => 'string',
    ),
    'profile_address_country' =>
    array (
      'type' => 'string',
    ),
    'profile_address_postal' =>
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
