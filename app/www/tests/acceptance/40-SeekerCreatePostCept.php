<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Create a post');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/create/seeker?clear');
// $I->see('Post');
// $I->click(['xpath' => '//div/a[@href="/post/create/seeker?clear"]']);
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->expect('Create a New Post');

$I->amGoingTo('Create a New Post');
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'Openovate Labs');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'Programmer');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Metro Manila');
$I->fillField('.page-post-create form#post-form input[name="post_experience"]', '2');
// $I->attachFile('//div[@data-do="file-field"]', 'CV-Templates-Curriculum-Vitae.pdf');
$I->fillField('.page-post-create input[name="post_email"]', 'testseeker@gmail.com');
$I->fillField('.page-post-create input[name="post_phone"]', '1234567');
$I->executeJS('$(\'iframe.wysihtml5-sandbox\').append(\'<textarea data-do="wysiwyg" name="post_detail" class="form-control" placeholder="Particularly ..." style="display: none;">post job detail</textarea>\')');

// check option
// $I->checkOption('.page-post-create #notify_match');
// $I->checkOption('.page-post-create #notify_company');
$I->click('.page-post-create button.submit');

$I->seeInCurrentUrl('/post/search');
$I->see('Post was Created');
$I->see('All job opportunities and job seekers');
$I->see('Openovate Labs');
$I->see('Programmer');
$I->see('Metro Manila');


// post 2


$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/create/seeker?clear');
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->expect('Create a New Post');

$I->amGoingTo('Create a New Post');
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'Openovate Labs 2');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'Programmer 2');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Metro Manila 2');
$I->fillField('.page-post-create form#post-form input[name="post_experience"]', '2');
// $I->attachFile('//div[@data-do="file-field"]', 'CV-Templates-Curriculum-Vitae.pdf');
$I->fillField('.page-post-create input[name="post_email"]', 'testseeker@gmail.com');
$I->fillField('.page-post-create input[name="post_phone"]', '1234567');
$I->executeJS('$(\'iframe.wysihtml5-sandbox\').append(\'<textarea data-do="wysiwyg" name="post_detail" class="form-control" placeholder="Particularly ..." style="display: none;">post job detail</textarea>\')');

// check option
// $I->checkOption('.page-post-create #notify_match');
// $I->checkOption('.page-post-create #notify_company');
$I->click('.page-post-create button.submit');

$I->seeInCurrentUrl('/post/search');
$I->see('Post was Created');
$I->see('All job opportunities and job seekers');
$I->see('Openovate Labs');
$I->see('Programmer');
$I->see('Metro Manila');
