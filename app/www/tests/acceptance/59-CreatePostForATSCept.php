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
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'ATS Test');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'It And Software Is Ats');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Metro Manila');
$I->fillField('.page-post-create form#post-form input[name="post_experience"]', '2');
$I->fillField('.page-post-create form#post-form input[name="post_salary_min"]', '10000');
$I->fillField('.page-post-create form#post-form input[name="post_salary_max"]', '20000');
$I->attachFile('//div[@data-do="file-field"]', 'images.png');
$I->fillField('.page-post-create input[name="post_email"]', 'john@doe.com');
$I->fillField('.page-post-create input[name="post_phone"]', '1234567');
$I->executeJS('$(\'iframe.wysihtml5-sandbox\').append(\'<textarea data-do="wysiwyg" name="post_detail" class="form-control" placeholder="Particularly ..." style="display: none;">post job detail</textarea>\')');

// tags
$I->executeJS('$(\'.tag-field\').append(\'<input type="hidden" name="post_tags[]" value="IT" />\')');
$I->executeJS('$(\'.tag-field\').append(\'<input type="hidden" name="post_tags[]" value="Technology" />\')');

// check option
$I->executeJS('$(\'#notify_match\').click();');
$I->executeJS('$(\'#notify_company\').click();');

$I->click('.page-post-create button.submit');

$I->seeInCurrentUrl('/post/search');
$I->wait(2);
$I->see('All job opportunities and job seekers');
// $I->see('Post was Created.');

$I->seeInCurrentUrl('/post/search');
$I->see('All job opportunities and job seekers');