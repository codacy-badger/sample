<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Fail Copy a post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');

$I->amGoingTo('Fail Copy a Post');

$I->amGoingTo('Create a New Post');
// fill edit fields
$I->fillField('.page-post-create form#post-form input[name="post_name"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_experience"]', '');
// $I->fillField('.page-post-create form#post-form input[name="post_salary_min"]', 'test');
// $I->fillField('.page-post-create form#post-form input[name="post_salary_max"]', 'test');
$I->fillField('.page-post-create form#post-form input[name="post_email"]', 'test');
// $I->attachFile('//input[@type="file"]', 'images.png');

$I->click('.page-post-create button.submit');
$I->wait(3);

// notification & validation
$I->see('Invalid Parameters');
$I->see('Name is required');
$I->see('Title is required');
$I->see('Location is required');
$I->see('Should be a valid email');
$I->seeInCurrentUrl('/post/create/poster');
// $I->see('Phone number should be numeric');

/**
* Asser 2 required name
**/

$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');

$I->amGoingTo('Fail Copy a Post');

$I->amGoingTo('Create a New Post');
// fill edit fields
$I->fillField('.page-post-create form#post-form input[name="post_name"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'test');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'quezon city');
$I->fillField('.page-post-create form#post-form input[name="post_email"]', 'test@gmail.com');
$I->fillField('.page-post-create form#post-form input[name="post_phone"]', '1234567');
// $I->attachFile('//input[@type="file"]', 'images.png');

$I->click('.page-post-create button.submit');


// notification & validation
$I->wait(3);
$I->see('Invalid Parameters');
$I->see('Name is required');
$I->seeInCurrentUrl('/post/create/poster');

/**
* Asser 2 required position
**/

$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');

$I->amGoingTo('Fail Copy a Post');

$I->amGoingTo('Create a New Post');
// fill edit fields
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'test');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'quezon city');
$I->fillField('.page-post-create form#post-form input[name="post_email"]', 'test@gmail.com');
$I->fillField('.page-post-create form#post-form input[name="post_phone"]', '1234567');
// $I->attachFile('//input[@type="file"]', 'images.png');

$I->click('.page-post-create button.submit');


// notification & validation
$I->wait(3);
$I->see('Invalid Parameters');
$I->see('Title is required');
$I->seeInCurrentUrl('/post/create/poster');

/**
* Asser 2 required location
**/

$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');

$I->amGoingTo('Fail Copy a Post');

$I->amGoingTo('Create a New Post');
// fill edit fields
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'test');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'programmer');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_email"]', 'test@gmail.com');
// $I->fillField('.page-post-create form#post-form input[name="post_phone"]', '1234567');
// $I->attachFile('//input[@type="file"]', 'images.png');

$I->click('.page-post-create button.submit');


// notification & validation
$I->wait(3);
$I->see('Invalid Parameters');
$I->see('Location is required');
$I->seeInCurrentUrl('/post/create/poster');

/**
* Asser 2 required email
**/

$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');

$I->amGoingTo('Fail Copy a Post');

$I->amGoingTo('Create a New Post');
// fill edit fields
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'test');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'programmer');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'quezon city');
$I->fillField('.page-post-create form#post-form input[name="post_email"]', 'test');
$I->fillField('.page-post-create form#post-form input[name="post_phone"]', '1234567');
// $I->attachFile('//input[@type="file"]', 'images.png');

$I->click('.page-post-create button.submit');


// notification & validation
$I->wait(3);
$I->see('Invalid Parameters');
$I->see('Should be a valid email');
$I->seeInCurrentUrl('/post/create/poster');

/**
* Asser 2 valid email
**/

$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');

$I->amGoingTo('Fail Copy a Post');

$I->amGoingTo('Create a New Post');
// fill edit fields
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'test');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'programmer');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'quezon city');
$I->fillField('.page-post-create form#post-form input[name="post_email"]', 'test');
$I->fillField('.page-post-create form#post-form input[name="post_phone"]', '1234567');
// $I->attachFile('//input[@type="file"]', 'images.png');

$I->click('.page-post-create button.submit');


// notification & validation
$I->wait(3);
$I->see('Invalid Parameters');
$I->see('Should be a valid email');
$I->seeInCurrentUrl('/post/create/poster');

/**
* Asser 2 valid number
**/

$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/copy/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');

$I->amGoingTo('Fail Copy a Post');

$I->amGoingTo('Create a New Post');
// fill edit fields
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'test');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'programmer');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'quezon city');
$I->fillField('.page-post-create form#post-form input[name="post_email"]', 'test@gmail.com');
$I->fillField('.page-post-create form#post-form input[name="post_phone"]', '123');
// $I->attachFile('//input[@type="file"]', 'images.png');

$I->click('.page-post-create button.submit');


// notification & validation
$I->wait(3);
$I->see('Invalid Parameters');
$I->see('Phone number should be at least 7 digits (no alphabets!) or leave blank');
$I->seeInCurrentUrl('/post/create/poster');