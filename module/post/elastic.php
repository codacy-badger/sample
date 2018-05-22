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
    'post_restored' =>
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
    'post_name_exact' =>
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
    'post_position_exact' =>
    array (
      'type' => 'string',
    ),
    'post_location' =>
    array (
      'type' => 'string',
      'analyzer' => 'simple',
    ),
    'post_location_exact' =>
    array (
      'type' => 'string',
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
    'post_image' =>
    array (
      'type' => 'string',
    ),
    'post_banner' =>
    array (
      'type' => 'string',
    ),
    'post_currency' =>
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
    'post_sms_match_count' =>
    array (
      'type' => 'integer',
    ),
    'post_sms_interested_count' =>
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
    'post_package' =>
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
    'post_meta' =>
    array (
      'type' => 'object',
    ),
    'profile_id' =>
    array (
      'type' => 'integer',
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
    'profile_parent' =>
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
    'profile_image' =>
    array (
      'type' => 'string',
    ),
    'profile_company' =>
    array (
      'type' => 'string',
    ),
    'profile_banner' =>
    array (
      'type' => 'string',
    ),
    'profile_banner_color' =>
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
    'profile_billing_name' =>
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
    'profile_achievements' =>
    array (
      'type' => 'string',
    ),
    'profile_experience' =>
    array (
      'type' => 'integer',
    ),
    'profile_package' =>
    array (
      'type' => 'string',
    ),
    'profile_active' =>
    array (
      'type' => 'integer',
    ),
    'profile_type' =>
    array (
      'type' => 'string',
    ),
    'profile_flag' =>
    array (
      'type' => 'integer',
    ),
    'profile_email_flag' =>
    array (
      'type' => 'integer',
    ),
    'profile_created' =>
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'profile_updated' =>
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'profile_verified' =>
    array (
      'type' => 'integer',
    ),
    'profile_tags' =>
    array (
      'type' => 'string',
    ),
    'profile_story' =>
    array (
      'type' => 'string',
    ),
    'profile_interviewer' =>
    array (
      'type' => 'object',
    ),
    'profile_subscribe' =>
    array (
      'type' => 'integer',
    ),
    'profile_bounce' =>
    array (
      'type' => 'integer',
    ),
    'profile_campaigns' =>
    array (
      'type' => 'string',
    ),
    'profile_meta' =>
    array (
      'type' => 'object',
    ),
  ),
);
