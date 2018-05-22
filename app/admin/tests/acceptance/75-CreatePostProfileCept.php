<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Create Profile Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Create Profile Post');

$I->click(['xpath' => '//a[@href="/control/post/create?profile_id=6"]']);
$I->seeInCurrentUrl('/control/post/create?profile_id=6');

$I->see('Create Post');

$I->fillField('.page-developer-post-create input[name="post_name"]', 'tech labs');
$I->fillField('.page-developer-post-create input[name="post_email"]', 'sungke@test.com');
$I->fillField('.page-developer-post-create input[name="post_phone"]', '09999999999');
$I->fillField('.page-developer-post-create input[name="post_position"]', 'Programmer');
$I->fillField('.page-developer-post-create input[name="post_location"]', 'Metro Manila');
$I->fillField('.page-developer-post-create input[name="post_experience"]', '2');
// tags
$I->executeJS('$(\'.tag-field\').append(\'<input type="hidden" name="post_tags[]" value="IT" />\')');
$I->executeJS('$(\'.tag-field\').append(\'<input type="hidden" name="post_tags[]" value="Technology" />\')');
// select option
$I->selectOption('.page-developer-post-create input[name="post_type"]', 'seeker');
$I->selectOption('.page-developer-post-create input[name="post_flag"]', '0');

// $I->fillField('.page-developer-post-create textarea[name="post_detail"]', 'Openovate Labs');
$I->executeJS('$(\'iframe.wysihtml5-sandbox\').append(\'<textarea class="form-control wysiwyg-area" data-do="wysiwyg" name="profile_detail" placeholder="Start writing ..." style="display: none;"></textarea>\')');
$I->fillField('.page-developer-post-create input[name="post_link"]', 'http://www.acme.com.ph');

$I->click('.page-developer-post-create button.btn-primary');

$I->seeInCurrentUrl('/control/post/search');

$I->wait(1);
$I->see('Post was Created');