<?php return array (
  'app' =>
  array (
    'app_id' =>
    array (
      'type' => 'integer',
    ),
    'app_active' =>
    array (
      'type' => 'short',
    ),
    'app_created' =>
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'app_updated' =>
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'app_name' =>
    array (
      'type' => 'string',
    ),
    'app_domain' =>
    array (
      'type' => 'string',
    ),
    'app_website' =>
    array (
      'type' => 'string',
    ),
    'app_token' =>
    array (
      'type' => 'string',
    ),
    'app_secret' =>
    array (
      'type' => 'string',
    ),
    'app_permissions' =>
    array (
      'type' => 'string',
    ),
    'app_type' =>
    array (
      'type' => 'string',
    ),
    'app_flag' =>
    array (
      'type' => 'integer',
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
      'type' => 'keyword',
      'null_value' => '_null_',
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
  ),
  'auth' =>
  array (
    'auth_id' =>
    array (
      'type' => 'integer',
    ),
    'auth_active' =>
    array (
      'type' => 'short',
    ),
    'auth_created' =>
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'auth_updated' =>
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'auth_slug' =>
    array (
      'type' => 'string',
    ),
    'auth_password' =>
    array (
      'type' => 'string',
    ),
    'auth_token' =>
    array (
      'type' => 'string',
    ),
    'auth_secret' =>
    array (
      'type' => 'string',
    ),
    'auth_google_refresh_token' =>
    array (
      'type' => 'string',
    ),
    'auth_permissions' =>
    array (
      'type' => 'string',
    ),
    'auth_type' =>
    array (
      'type' => 'string',
    ),
    'auth_flag' =>
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
    'profile_job' =>
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
  'session' =>
  array (
    'session_id' =>
    array (
      'type' => 'integer',
    ),
    'session_active' =>
    array (
      'type' => 'short',
    ),
    'session_created' =>
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'session_updated' =>
    array (
      'type' => 'date',
      'format' => 'yyyy-MM-dd HH:mm:ss',
    ),
    'session_token' =>
    array (
      'type' => 'string',
    ),
    'session_secret' =>
    array (
      'type' => 'string',
    ),
    'session_permissions' =>
    array (
      'type' => 'string',
    ),
    'session_status' =>
    array (
      'type' => 'string',
    ),
    'session_type' =>
    array (
      'type' => 'string',
    ),
    'session_flag' =>
    array (
      'type' => 'integer',
    ),
    'app_name' =>
    array (
      'type' => 'string',
    ),
    'app_domain' =>
    array (
      'type' => 'string',
    ),
    'app_website' =>
    array (
      'type' => 'string',
    ),
    'app_token' =>
    array (
      'type' => 'string',
    ),
    'app_secret' =>
    array (
      'type' => 'string',
    ),
    'app_permissions' =>
    array (
      'type' => 'string',
    ),
    'app_type' =>
    array (
      'type' => 'string',
    ),
    'app_flag' =>
    array (
      'type' => 'integer',
    ),
    'auth_slug' =>
    array (
      'type' => 'string',
    ),
    'auth_password' =>
    array (
      'type' => 'string',
    ),
    'auth_token' =>
    array (
      'type' => 'string',
    ),
    'auth_secret' =>
    array (
      'type' => 'string',
    ),
    'auth_google_token' =>
    array (
      'type' => 'string',
    ),
    'auth_google_refresh_token' =>
    array (
      'type' => 'string',
    ),
    'auth_permissions' =>
    array (
      'type' => 'string',
    ),
    'auth_type' =>
    array (
      'type' => 'string',
    ),
    'auth_flag' =>
    array (
      'type' => 'integer',
    ),
  ),
);
