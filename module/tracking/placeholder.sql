INSERT INTO answer (answer_id, answer_name, answer_created, answer_updated) VALUES (1, 'John Doe', '2018-02-09 09:00:21', '2018-02-09 09:00:21');

INSERT INTO applicant (applicant_id, applicant_status, applicant_created, applicant_updated) VALUES (1, '["PENDING"]', '2018-02-09 09:00:24', '2018-02-09 09:00:24');

INSERT INTO applicant_profile (applicant_id, profile_id) VALUES (1, 1);

INSERT INTO applicant_form (applicant_id, form_id) VALUES (1, 1);

INSERT INTO applicant_post (applicant_id, post_id) VALUES (1, 1);

INSERT INTO form (form_id, form_name, form_created, form_updated) VALUES (1, 'Project Manager Form', '2018-02-09 09:00:29', '2018-02-09 09:00:29');

INSERT INTO label (label_id, label_custom, label_created, label_updated) VALUES (1, '[\"Hired\"]', '2018-02-09 09:00:33', '2018-02-09 09:00:33');

INSERT INTO question (question_id, question_name, question_created, question_updated) VALUES (1, 'What is your name?', '2018-02-09 09:00:33', '2018-02-09 09:00:33');

INSERT INTO question_answer (question_id, answer_id) VALUES (1, 1);

INSERT INTO post_form (post_id, form_id) VALUES (1, 1);

INSERT INTO profile_form (profile_id, form_id) VALUES (1, 1);
