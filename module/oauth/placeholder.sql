--
-- Dumping data for table `app`
--

INSERT INTO `app` (`app_id`, `app_name`, `app_domain`, `app_website`, `app_permissions`, `app_token`, `app_secret`, `app_active`, `app_type`, `app_flag`, `app_created`, `app_updated`) VALUES
(1, 'Cradle App 1', '*.cradlephp.github.io', 'http://cradlephp.github.io', '["public_profile", "personal_profile", "personal_post"]', '87d02468a934cb717cc15fe48a244f43', '21e21453cad34a94b76fb840c1eeba8a', 1, 'admin', 0, '2016-12-21 07:37:43', '2016-12-21 08:06:03');

--
-- Dumping data for table `app_profile`
--

INSERT INTO `app_profile` (`app_id`, `profile_id`) VALUES
(1, 1);

--
-- Dumping data for table `auth`
--

INSERT INTO `auth` (`auth_id`, `auth_slug`, `auth_password`, `auth_token`, `auth_secret`, `auth_permissions`, `auth_facebook_token`, `auth_facebook_secret`, `auth_linkedin_token`, `auth_linkedin_secret`, `auth_twitter_token`, `auth_twitter_secret`, `auth_google_token`, `auth_google_secret`, `auth_active`, `auth_type`, `auth_flag`, `auth_created`, `auth_updated`) VALUES
(1, 'john@doe.com', '202cb962ac59075b964b07152d234b70', '8323fd20795498fb77deb36a85fd3490', '300248246ea1996063a1a40635dbce71', '["public_profile", "personal_profile"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'admin', 0, '2016-12-21 07:36:51', '2016-12-21 08:08:45');

INSERT INTO `auth`  (`auth_id`, `auth_slug`, `auth_password`, `auth_token`, `auth_secret`, `auth_permissions`, `auth_facebook_token`, `auth_facebook_secret`, `auth_linkedin_token`, `auth_linkedin_secret`, `auth_twitter_token`, `auth_twitter_secret`, `auth_google_token`, `auth_google_secret`, `auth_active`, `auth_type`, `auth_flag`, `auth_created`, `auth_updated`) VALUES
(2, 'testseeker@gmail.com', '202cb962ac59075b964b07152d234b70', '8323fd20795498fb77deb36a85fd3490', '300248246ea1996063a1a40635dbce71', '["public_profile", "personal_profile"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'admin', 0, '2016-12-21 07:36:51', '2016-12-21 08:08:45');

--
-- Dumping data for table `auth_profile`
--

INSERT INTO `auth_profile` (`auth_id`, `profile_id`) VALUES
(1, 1);

--
-- Dumping data for table `session`
--

INSERT INTO `session` (`session_id`, `session_token`, `session_secret`, `session_permissions`, `session_status`, `session_active`, `session_type`, `session_flag`, `session_created`, `session_updated`) VALUES
(1, 'eea8e9cb6302e2e83e38737bad5ed194', 'd815589580f5213d3f915eb6d13724e4', '["public_profile", "personal_profile"]', 'PENDING', 1, NULL, 0, '2016-12-21 07:58:18', '2016-12-21 07:58:18');

--
-- Dumping data for table `session_app`
--

INSERT INTO `session_app` (`session_id`, `app_id`) VALUES
(1, 1);

--
-- Dumping data for table `session_auth`
--

INSERT INTO `session_auth` (`session_id`, `auth_id`) VALUES
(1, 1);

--
-- Dumping data for table `role`
--

INSERT INTO role (role_name, role_permissions, role_type, role_created, role_updated) VALUES ('Super Admin', '[\"admin:position:view\",[\"admin:position:listing\",\"admin:position:create\",\"admin:position:update\",\"admin:position:remove\",\"admin:position:restore\",\"admin:utm:view\",\"admin:utm:listing\",\"admin:utm:create\",\"admin:utm:update\",\"admin:utm:remove\",\"admin:utm:restore\",\"admin:transaction:view\",\"admin:transaction:listing\",\"admin:transaction:create\",\"admin:transaction:update\",\"admin:transaction:remove\",\"admin:transaction:export\",\"admin:transaction:restore\",\"admin:service:view\",\"admin:service:listing\",\"admin:service:create\",\"admin:service:update\",\"admin:service:remove\",\"admin:service:listing\",\"admin:term:view\",\"admin:term:listing\",\"admin:term:create\",\"admin:term:update\",\"admin:term:remove\",\"admin:term:restore\",\"admin:post:view\",\"admin:post:listing\",\"admin:post:create\",\"admin:post:update\",\"admin:post:remove\",\"admin:post:copy\",\"admin:post:restore\",\"admin:profile:view\",\"admin:profile:listing\",\"admin:profile:create\",\"admin:profile:update\",\"admin:profile:remove\",\"admin:profile:send-claim-email\",\"admin:profile:export\",\"admin:profile:export-csv-format\",\"admin:profile:upload-csv\",\"admin:profile:restore\",\"admin:auth:view\",\"admin:auth:listing\",\"admin:auth:create\",\"admin:auth:update\",\"admin:auth:remove\",\"admin:auth:restore\",\"admin:article:view\",\"admin:article:listing\",\"admin:article:create\",\"admin:article:update\",\"admin:article:remove\",\"admin:article:restore\",\"admin:research:view\",\"admin:research:listing\",\"admin:research:create\",\"admin:research:update\",\"admin:research:remove\",\"admin:research:restore\",\"admin:feature:view\",\"admin:feature:listing\",\"admin:feature:create\",\"admin:feature:update\",\"admin:feature:remove\",\"admin:feature:restore\"]', 'admin', '2018-04-04 08:39:59', '2018-04-04 08:39:59');
INSERT INTO role (role_name, role_permissions, role_type, role_created, role_updated) VALUES ('Test Admin', '[\"admin:position:view\",\"admin:position:listing\",\"admin:position:create\",\"admin:utm:view\",\"admin:utm:listing\",\"admin:utm:remove\",\"admin:transaction:view\",\"admin:transaction:listing\",\"admin:transaction:update\",\"admin:transaction:remove\",\"admin:profile:view\"\"admin:profile:listing\",\"admin:profile:export\",\"admin:profile:export-csv-format\",\"admin:profile:upload-csv\"]', 'admin', '2018-04-04 08:39:59', '2018-04-04 08:39:59');
--
-- Dumping data for table `role_auth`
--

INSERT INTO `role_auth`  (`role_id`, `auth_id`) VALUES
(1, 1);

INSERT INTO `role_auth`  (`role_id`, `auth_id`) VALUES
(2, 2);
