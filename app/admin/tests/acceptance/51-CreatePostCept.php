<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Create new Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Click Create New Post');

// redirect to create utm
$I->click(['xpath'=> '//a[@href="/control/post/create"]']);

$I->seeInCurrentUrl('/control/post/create');
$I->see('Create Post');

$I->fillField('.page-developer-post-create input[name="post_name"]', 'tech labs');
$I->fillField('.page-developer-post-create input[name="post_email"]', 'sungke@test.com');
$I->fillField('.page-developer-post-create input[name="post_phone"]', '099999999');
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




