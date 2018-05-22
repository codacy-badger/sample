<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);


/**
* Assert  blank fields
**/

$I->wantTo('Fail Create a post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/create/poster?clear');
$I->seeInCurrentUrl('/post/create/poster?clear');
$I->expect('Create a New Post');

$I->amGoingTo('Create a New Post');
$I->fillField('.page-post-create form#post-form input[name="post_name"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', '');


$I->fillField('.page-post-create form#post-form input[name="post_email"]', 'test');


$I->click('.page-post-create button.submit');

// notification & validation
$I->seeInCurrentUrl('/post/create/poster?clear');
$I->wait(3);
$I->see('Invalid Parameters');
$I->see('Name is required');
$I->see('Title is required');
$I->see('Location is required');
// $I->see('Experience is required');
// $I->see('Must be a valid number');
// $I->see('Must be a valid number');
$I->see('Should be a valid email');
// $I->see('Phone number should be numeric');


/**
* Assert letter on post experience
**/

$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/create/poster?clear');
$I->seeInCurrentUrl('/post/create/poster?clear');
$I->expect('Create a New Post');

$I->amGoingTo('Create a New Post');
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'test name');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'test position');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'test location');
// $I->fillField('.page-post-create form#post-form input[name="post_experience"]', 'test experience');
// $I->fillField('.page-post-create form#post-form input[name="post_salary_min"]', '5000');
// $I->fillField('.page-post-create form#post-form input[name="post_salary_max"]', '10000');
$I->fillField('.page-post-create form#post-form input[name="post_email"]', 'test@gmail.com');
$I->fillField('.page-post-create form#post-form input[name="post_phone"]', '123456');

$I->click('.page-post-create button.submit');

// notification & validation
$I->seeInCurrentUrl('/post/create/poster?clear');
$I->wait(3);
$I->see('Invalid Parameters');
$I->see('Phone number should be at least 7 digits (no alphabets!) or leave blank');