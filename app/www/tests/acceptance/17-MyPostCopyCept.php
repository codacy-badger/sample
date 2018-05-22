<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Copy a post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/post/search');
$I->click(['xpath' => '//div/a[@href="/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);
$I->see('Create a New Post');

$I->seeInCurrentUrl('/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
//$I->fillField('post_phone', '12312123');
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'Openovate test copy');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'IT and Software');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Metro Manila');
$I->fillField('.page-post-create form#post-form input[name="post_salary_min"]', '25000');
$I->fillField('.page-post-create form#post-form input[name="post_salary_max"]', '35000');

// 3

$I->fillField('.page-post-create input[name="post_phone"]', '55554421');
// tags
$I->executeJS('$(\'.tag-field\').append(\'<input type="hidden" name="post_tags[]" value="IT" />\')');
$I->executeJS('$(\'.tag-field\').append(\'<input type="hidden" name="post_tags[]" value="Technology" />\')');

$I->click('.page-post-create button.submit');
// $I->seeInCurrentUrl('/post/search');
// $I->wait(2);
// $I->see('Post was Created');
// $I->see('All job opportunities and job seekers');
