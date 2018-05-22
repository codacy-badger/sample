<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Create a post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/post/search');
// $I->see('Post');
// $I->click(['xpath' => '//div/a[@href="/post/create/poster?clear"]']);
$I->amOnPage('/post/create/poster?clear');
$I->seeInCurrentUrl('/post/create/poster?clear');
$I->expect('Create a New Post');

$I->amGoingTo('Create a New Post');
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'Openovate Labs');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'IT and Software');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Metro Manila');
$I->fillField('.page-post-create form#post-form input[name="post_experience"]', '2');
$I->fillField('.page-post-create form#post-form input[name="post_salary_min"]', '10000');
$I->fillField('.page-post-create form#post-form input[name="post_salary_max"]', '20000');
// $I->attachFile('//div[@data-do="file-field"]', 'images.png');
$I->fillField('.page-post-create input[name="post_email"]', 'testcompany@gmail.com');
$I->fillField('.page-post-create input[name="post_phone"]', '12345678');
$I->executeJS('$(\'iframe.wysihtml5-sandbox\').append(\'<textarea data-do="wysiwyg" name="post_detail" class="form-control" placeholder="Particularly ..." style="display: none;">post job detail</textarea>\')');

// tags
$I->executeJS('$(\'.tag-field\').append(\'<input type="hidden" name="post_tags[]" value="IT" />\')');
$I->executeJS('$(\'.tag-field\').append(\'<input type="hidden" name="post_tags[]" value="Technology" />\')');

// check option
// $I->checkOption('.page-post-create #notify_match');
// $I->checkOption('.page-post-create #notify_company');
$I->click('.page-post-create button.submit');

$I->seeInCurrentUrl('/post/search');
$I->wait(2);
$I->see('All job opportunities and job seekers');
$I->see('Post was Created.');
// $I->wait(5);
// $I->see("Yay, Achievement Unlocked!");
// $I->click('.modal.fade.achievement-modal.in .modal-footer button.btn.btn-default');
$I->seeInCurrentUrl('/post/search');
$I->see('All job opportunities and job seekers');
// $I->see('Openovate Labs');
// $I->see('Hiring Programmer');
// $I->see('₱ 10,000 - 20,000');
// $I->see('Metro Manila');
// $I->see('Hello! We are Openovate Labs from Metro Manila and we are looking for a Programmer with 2 years of experience.');

$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/create/poster?clear');
// $I->see('Post');
// $I->click(['xpath' => '//div/a[@href="/post/create/poster?clear"]']);
$I->seeInCurrentUrl('/post/create/poster?clear');
$I->expect('Create a New Post');

$I->amGoingTo('Create a New Post');
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'Openovate Labs2');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'IT and Software');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Metro Manila');
$I->fillField('.page-post-create form#post-form input[name="post_experience"]', '2');
$I->fillField('.page-post-create form#post-form input[name="post_salary_min"]', '10000');
$I->fillField('.page-post-create form#post-form input[name="post_salary_max"]', '20000');
// $I->attachFile('//div[@data-do="file-field"]', 'images.png');
$I->fillField('.page-post-create input[name="post_email"]', 'testcompany@gmail.com');
$I->fillField('.page-post-create input[name="post_phone"]', '12345678');
$I->executeJS('$(\'iframe.wysihtml5-sandbox\').append(\'<textarea data-do="wysiwyg" name="post_detail" class="form-control" placeholder="Particularly ..." style="display: none;">post job detail</textarea>\')');

// tags
$I->executeJS('$(\'.tag-field\').append(\'<input type="hidden" name="post_tags[]" value="IT" />\')');
$I->executeJS('$(\'.tag-field\').append(\'<input type="hidden" name="post_tags[]" value="Technology" />\')');

// check option
// $I->checkOption('.page-post-create #notify_match');
// $I->checkOption('.page-post-create #notify_company');
$I->click('.page-post-create button.submit');

$I->wait(3);
$I->seeInCurrentUrl('/post/search');
$I->see('Post was Created.');
$I->see('All job opportunities and job seekers');
