<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Create ATS Form');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

//  redirect page
$I->amOnPage('/profile/tracking/post/search');

// check active post
$I->see('Applicant Tracking System');
$I->click('Create Form');
$I->wait(5);

// checking modal
$I->see('Create an Application Form');
$I->see('Please enter your Application Form title');
$I->fillField('.page-tracking-post-search .confirmation-form.in input[name="form_name"]', 'Test ATS Form');


// $I->click('.page-tracking-post-search .confirmation-form.in .modal-footer button.btn.btn-default','#confirmation-form-current');

$I->click('.page-tracking-post-search .confirmation-form.in .modal-footer button.btn.btn-default');

$I->wait(1);
$I->see('Form has been created!');

$I->wait(2);
$I->seeInCurrentUrl('/profile/tracking/application/poster/update/6');

// $I->click('.page-tracking-application-poster-update .detail-wrapper .detail-form .form-add');
$I->click('Add Field');
$I->wait(10);
$I->see('Custom Question');
$I->see('Answer');

$I->fillField('.page-tracking-application-poster-update .form-custom-question.in input[name="question_name"]', 'What is your preferred working hours?');
// $I->fillField('.page-tracking-application-poster-update #form-custom-question-4 input[name="question_name"]', 'What is your preferred working hours?');

$I->executeJS('$(\'.form-answers\').append(\'<div class="form-answer"><input class="form-control" name="question_choices[]" placeholder="Choice" type="text" value="7am-4pm"><button class="close" data-do="form-remove-answer" data-on="click" type="button"><i class="fa fa-times"></i></button></div>\')');
$I->executeJS('$(\'.form-answers\').append(\'<div class="form-answer"><input class="form-control" name="question_choices[]" placeholder="Choice" type="text" value="8am-5pm"><button class="close" data-do="form-remove-answer" data-on="click" type="button"><i class="fa fa-times"></i></button></div>\')');
$I->executeJS('$(\'.form-answers\').append(\'<div class="form-answer"><input class="form-control" name="question_choices[]" placeholder="Choice" type="text" value="9am-6pm"><button class="close" data-do="form-remove-answer" data-on="click" type="button"><i class="fa fa-times"></i></button></div>\')');
$I->executeJS('$(\'.form-answers\').append(\'<div class="form-answer"><input class="form-control" name="question_choices[]" placeholder="Choice" type="text" value="10am-7pm"><button class="close" data-do="form-remove-answer" data-on="click" type="button"><i class="fa fa-times"></i></button></div>\')');

$I->executeJS('$(\'#form-custom-6\').click();');
$I->click('.page-tracking-application-poster-update .form-custom-question.in button.btn.btn-default');
$I->wait(1);
$I->see('Question was successfully added');
$I->wait(5);
$I->click('.page-tracking-application-poster-update .publish a.btn.btn-default.form-publish');

$I->wait(5);
$I->see('Publish Form');
$I->see('Are you sure you want to publish this form?');
$I->click('.page-tracking-application-poster-update .confirmation-form.in button.btn.btn-default');
$I->wait(1);
$I->see('Successfully published form');
$I->wait(5);
$I->seeInCurrentUrl('/profile/tracking/application/poster/search');


//  on page  application form

//  redirect page
$I->amOnPage('/profile/tracking/application/poster/search');

// check active post
$I->see('Applicant Tracking System');
$I->click('Create Form');
$I->wait(5);

// checking modal
$I->see('Create an Application Form');
$I->see('Please enter your Application Form title');
$I->fillField('.page-tracking-application-poster-search .confirmation-form.in input[name="form_name"]', 'Test 2 ATS Form');


// $I->click('.page-tracking-post-search .confirmation-form.in .modal-footer button.btn.btn-default','#confirmation-form-current');
$I->click('.page-tracking-application-poster-search .confirmation-form.in .modal-footer button.btn.btn-default');
$I->wait(1);
$I->see('Form has been created!');

$I->wait(2);
$I->seeInCurrentUrl('/profile/tracking/application/poster/update/7');

// $I->click('.page-tracking-application-poster-update .detail-wrapper .detail-form .form-add');
$I->click('Add Field');
$I->wait(10);
$I->see('Custom Question');
$I->see('Answer');

$I->fillField('.page-tracking-application-poster-update .form-custom-question.in input[name="question_name"]', 'What is your preferred working hours?');
// $I->fillField('.page-tracking-application-poster-update #form-custom-question-4 input[name="question_name"]', 'What is your preferred working hours?');

$I->executeJS('$(\'.form-answers\').append(\'<div class="form-answer"><input class="form-control" name="question_choices[]" placeholder="Choice" type="text" value="7am-4pm"><button class="close" data-do="form-remove-answer" data-on="click" type="button"><i class="fa fa-times"></i></button></div>\')');
$I->executeJS('$(\'.form-answers\').append(\'<div class="form-answer"><input class="form-control" name="question_choices[]" placeholder="Choice" type="text" value="8am-5pm"><button class="close" data-do="form-remove-answer" data-on="click" type="button"><i class="fa fa-times"></i></button></div>\')');
$I->executeJS('$(\'.form-answers\').append(\'<div class="form-answer"><input class="form-control" name="question_choices[]" placeholder="Choice" type="text" value="9am-6pm"><button class="close" data-do="form-remove-answer" data-on="click" type="button"><i class="fa fa-times"></i></button></div>\')');
$I->executeJS('$(\'.form-answers\').append(\'<div class="form-answer"><input class="form-control" name="question_choices[]" placeholder="Choice" type="text" value="10am-7pm"><button class="close" data-do="form-remove-answer" data-on="click" type="button"><i class="fa fa-times"></i></button></div>\')');

$I->executeJS('$(\'#form-custom-7\').click();');
$I->click('.page-tracking-application-poster-update .form-custom-question.in button.btn.btn-default');
$I->wait(1);
$I->see('Question was successfully added');
$I->wait(5);
$I->click('.page-tracking-application-poster-update .publish a.btn.btn-default.form-publish');

$I->wait(5);
$I->see('Publish Form');
$I->see('Are you sure you want to publish this form?');
$I->click('.page-tracking-application-poster-update .confirmation-form.in button.btn.btn-default');
$I->wait(1);
$I->see('Successfully published form');
$I->wait(5);
$I->seeInCurrentUrl('/profile/tracking/application/poster/search');
