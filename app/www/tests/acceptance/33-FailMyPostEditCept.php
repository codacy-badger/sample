<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Fail Edit a post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
$I->expect('Updating Post');

$I->amGoingTo('Edit Post');

// fill edit fields
$I->fillField('.page-post-update form#post-form input[name="post_name"]', '');
$I->fillField('.page-post-update form#post-form input[name="post_position"]', '');
$I->fillField('.page-post-update form#post-form input[name="post_location"]', '');
$I->fillField('.page-post-update form#post-form input[name="post_email"]', 'test');
$I->fillField('.page-post-update form#post-form input[name="post_phone"]', 'test');

$I->click('.page-post-update button.submit');
$I->seeInCurrentUrl('/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');

// notification & validation
$I->wait(3);
$I->see('Invalid Parameters');
$I->see('Name is required');
$I->see('Title is required');
$I->see('Location is required');
$I->see('Should be a valid email');
// $I->see('Phone number should be numeric');

/**
* Asser 2 required name
**/

$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
$I->expect('Updating Post');

$I->amGoingTo('Edit Post');

// fill edit fields
$I->fillField('.page-post-update form#post-form input[name="post_name"]', '');
$I->fillField('.page-post-update form#post-form input[name="post_position"]', 'programmer');
$I->fillField('.page-post-update form#post-form input[name="post_location"]', 'quezon city');
$I->fillField('.page-post-update form#post-form input[name="post_email"]', 'test@gmail.com');
$I->fillField('.page-post-update form#post-form input[name="post_phone"]', '12345678');

$I->click('.page-post-update button.submit');
$I->seeInCurrentUrl('/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');

// notification & validation
$I->wait(3);
$I->see('Invalid Parameters');
$I->see('Name is required');

/**
* Asser 2 required position
**/

$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
$I->expect('Updating Post');

$I->amGoingTo('Edit Post');

// fill edit fields
$I->fillField('.page-post-update form#post-form input[name="post_name"]', 'test');
$I->fillField('.page-post-update form#post-form input[name="post_position"]', '');
$I->fillField('.page-post-update form#post-form input[name="post_location"]', 'quezon city');
$I->fillField('.page-post-update form#post-form input[name="post_email"]', 'test@gmail.com');
$I->fillField('.page-post-update form#post-form input[name="post_phone"]', '12345678');

$I->click('.page-post-update button.submit');
$I->seeInCurrentUrl('/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');

// notification & validation
$I->wait(3);
$I->see('Invalid Parameters');
$I->see('Title is required');


/**
* Asser 2 required location
**/

$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
$I->expect('Updating Post');

$I->amGoingTo('Edit Post');

// fill edit fields
$I->fillField('.page-post-update form#post-form input[name="post_name"]', 'test');
$I->fillField('.page-post-update form#post-form input[name="post_position"]', 'programmer');
$I->fillField('.page-post-update form#post-form input[name="post_location"]', '');
$I->fillField('.page-post-update form#post-form input[name="post_email"]', 'test@gmail.com');
$I->fillField('.page-post-update form#post-form input[name="post_phone"]', '12345678');

$I->click('.page-post-update button.submit');
$I->seeInCurrentUrl('/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');

// notification & validation
$I->wait(3);
$I->see('Invalid Parameters');
$I->see('Location is required');

/**
* Asser 2 valid email
**/

$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
$I->expect('Updating Post');

$I->amGoingTo('Edit Post');

// fill edit fields
$I->fillField('.page-post-update form#post-form input[name="post_name"]', 'test');
$I->fillField('.page-post-update form#post-form input[name="post_position"]', 'programmer');
$I->fillField('.page-post-update form#post-form input[name="post_location"]', 'quezon city');
$I->fillField('.page-post-update form#post-form input[name="post_email"]', 'test');
$I->fillField('.page-post-update form#post-form input[name="post_phone"]', '12345678');

$I->click('.page-post-update button.submit');
$I->seeInCurrentUrl('/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');

// notification & validation
$I->wait(3);
$I->see('Invalid Parameters');
$I->see('Should be a valid email');

/**
* Asser 2 valid phone
**/

$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
$I->expect('Updating Post');

$I->amGoingTo('Edit Post');

// fill edit fields
$I->fillField('.page-post-update form#post-form input[name="post_name"]', 'test');
$I->fillField('.page-post-update form#post-form input[name="post_position"]', 'programmer');
$I->fillField('.page-post-update form#post-form input[name="post_location"]', 'quezon city');
$I->fillField('.page-post-update form#post-form input[name="post_email"]', 'test@gmail.com');
$I->fillField('.page-post-update form#post-form input[name="post_phone"]', '123123');

$I->click('.page-post-update button.submit');
$I->seeInCurrentUrl('/post/update/poster/1?redirect_uri=%2Fprofile%2Fpost%2Fsearch');

// notification & validation
$I->wait(3);
$I->see('Invalid Parameters');
$I->see('Phone number should be at least 7 digits (no alphabets!) or leave blank');