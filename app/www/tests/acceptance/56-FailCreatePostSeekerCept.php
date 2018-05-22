<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);


/**
* Assert  blank fields
**/

$I->wantTo('Fail Create a post');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/create/seeker?clear');
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->expect('Create a New Post');

$I->amGoingTo('Create a New Post');
$I->fillField('.page-post-create form#post-form input[name="post_name"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', '');
// $I->fillField('.page-post-create form#post-form input[name="post_email"]', '');
// $I->fillField('.page-post-create form#post-form input[name="post_phone"]', '');

$I->click('.page-post-create button.submit');

// notification & validation
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->wait(2);
$I->see('Invalid Parameters');
$I->see('Name is required');
$I->see('Title is required');
$I->see('Location is required');

/**
* Assert Blank name
**/

$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/create/seeker?clear');
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->expect('Create a New Post');

$I->amGoingTo('Create a New Post');
$I->fillField('.page-post-create form#post-form input[name="post_name"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'Programmer');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Quezon City');

$I->click('.page-post-create button.submit');

// notification & validation
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->wait(2);
$I->see('Invalid Parameters');
$I->see('Name is required');


/**
* Assert blank position
**/

$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/create/seeker?clear');
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->expect('Create a New Post');

$I->amGoingTo('Create a New Post');
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'Foo Barss');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Quezon City');

$I->click('.page-post-create button.submit');

// notification & validation
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->wait(2);
$I->see('Invalid Parameters');
$I->see('Title is required');


/**
* Assert blank location
**/

$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/create/seeker?clear');
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->expect('Create a New Post');

$I->amGoingTo('Create a New Post');
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'Foo Barss');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'Programmer');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', '');

$I->click('.page-post-create button.submit');

// notification & validation
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->wait(2);
$I->see('Invalid Parameters');
$I->see('Location is required');



/**
* Assert wrong email
**/

$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/create/seeker?clear');
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->expect('Create a New Post');

$I->amGoingTo('Create a New Post');
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'Foo Barss');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'Programmer');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Quezon City');
$I->fillField('.page-post-create form#post-form input[name="post_email"]', 'test@test');

$I->click('.page-post-create button.submit');

// notification & validation
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->wait(2);
$I->see('Invalid Parameters');
$I->see('Should be a valid email');


/**
* Assert phone number
**/

$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/create/seeker?clear');
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->expect('Create a New Post');

$I->amGoingTo('Create a New Post');
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'Foo Barss');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'Programmer');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Quezon City');
$I->fillField('.page-post-create form#post-form input[name="post_email"]', 'test@test.com');
$I->fillField('.page-post-create form#post-form input[name="post_phone"]', 'test');

$I->click('.page-post-create button.submit');

// notification & validation
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->wait(2);
$I->see('Invalid Parameters');
$I->see('Phone number should be numeric');



/**
* Assert phone less than 7
**/

$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/create/seeker?clear');
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->expect('Create a New Post');

$I->amGoingTo('Create a New Post');
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'Foo Barss');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'Programmer');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Quezon City');
$I->fillField('.page-post-create form#post-form input[name="post_email"]', 'test@test.com');
$I->fillField('.page-post-create form#post-form input[name="post_phone"]', '4445');

$I->click('.page-post-create button.submit');

// notification & validation
$I->seeInCurrentUrl('/post/create/seeker?clear');
$I->wait(2);
$I->see('Invalid Parameters');
$I->see('Phone number should be at least 7 digits (no alphabets!) or leave blank');