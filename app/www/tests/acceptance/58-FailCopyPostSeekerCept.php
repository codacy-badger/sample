<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Fail Copy a post');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/copy/11?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
// update post page
$I->seeInCurrentUrl('/post/copy/11?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
$I->expect('Updating Post');

$I->amGoingTo('Edit Post');

// fill edit fields
$I->fillField('.page-post-create form#post-form input[name="post_name"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', '');

$I->click('.page-post-create button.submit');
$I->seeInCurrentUrl('/post/create/seeker');

// notification & validation
$I->wait(2);
$I->see('Invalid Parameters');
$I->see('Name is required');
$I->see('Title is required');
$I->see('Location is required');


//  blank name
$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/copy/11?redirect_uri=%2Fprofile%2Fpost%2Fsearch');

// update post page
$I->seeInCurrentUrl('/post/copy/11?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
$I->expect('Updating Post');

$I->amGoingTo('Edit Post');

// fill edit fields
$I->fillField('.page-post-create form#post-form input[name="post_name"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'Programmer');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Quezon City');

$I->click('.page-post-create button.submit');
$I->seeInCurrentUrl('/post/create/seeker');

// notification & validation
$I->wait(2);
$I->see('Invalid Parameters');
$I->see('Name is required');


//  blank position
$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/copy/11?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
// update post page
$I->seeInCurrentUrl('/post/copy/11?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
$I->expect('Updating Post');

$I->amGoingTo('Edit Post');

// fill edit fields
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'Foo barss');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', '');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Quezon City');

$I->click('.page-post-create button.submit');
$I->seeInCurrentUrl('/post/create/seeker');

// notification & validation
$I->wait(2);
$I->see('Invalid Parameters');
$I->see('Title is required');




//  blank location
$I->amOnPage('/profile/post/search');
$I->amOnPage('/post/copy/11?redirect_uri=%2Fprofile%2Fpost%2Fsearch');

// update post page
$I->seeInCurrentUrl('/post/copy/11?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
$I->expect('Updating Post');

$I->amGoingTo('Edit Post');

// fill edit fields
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'Foo barss');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'Programmer');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', '');

$I->click('.page-post-create button.submit');
$I->seeInCurrentUrl('/post/create/seeker');

// notification & validation
$I->wait(2);
$I->see('Invalid Parameters');
$I->see('Location is required');




//  wrong email
$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/copy/11?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/copy/11?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
$I->expect('Updating Post');

$I->amGoingTo('Edit Post');

// fill edit fields
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'Foo barss');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'Programmer');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Quezon City');
$I->fillField('.page-post-create form#post-form input[name="post_email"]', 'test@test');

$I->click('.page-post-create button.submit');
$I->seeInCurrentUrl('/post/create/seeker');

// notification & validation
$I->wait(2);
$I->see('Invalid Parameters');
$I->see('Should be a valid email');


//  wrong type of phone
// $I->amOnPage('/profile/post/search');
// $I->see('Post');
// $I->click(['xpath' => '//div/a[@href="/post/copy/11?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// // update post page
// $I->seeInCurrentUrl('/post/copy/11?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
// $I->expect('Updating Post');

// $I->amGoingTo('Edit Post');

// // fill edit fields
// $I->fillField('.page-post-create form#post-form input[name="post_name"]', 'Foo barss');
// $I->fillField('.page-post-create form#post-form input[name="post_position"]', 'Programmer');
// $I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Quezon City');
// $I->fillField('.page-post-create form#post-form input[name="post_email"]', 'test@test.com');
// $I->fillField('.page-post-create form#post-form input[name="post_phone"]', 'test');


// $I->click('.page-post-create button.submit');
// $I->seeInCurrentUrl('/post/create/seeker');

// // notification & validation
// $I->wait(2);
// $I->see('Invalid Parameters');
// $I->see('Phone number should be numeric');




//  wrong less than 7 characters
$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/copy/11?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/copy/11?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
$I->expect('Updating Post');

$I->amGoingTo('Edit Post');

// fill edit fields
$I->fillField('.page-post-create form#post-form input[name="post_name"]', 'Foo barss');
$I->fillField('.page-post-create form#post-form input[name="post_position"]', 'Programmer');
$I->fillField('.page-post-create form#post-form input[name="post_location"]', 'Quezon City');
$I->fillField('.page-post-create form#post-form input[name="post_email"]', 'test@test.com');
$I->fillField('.page-post-create form#post-form input[name="post_phone"]', '4445');


$I->click('.page-post-create button.submit');
$I->seeInCurrentUrl('/post/create/seeker');

// notification & validation
$I->wait(3);
$I->see('Invalid Parameters');
$I->see('Phone number should be at least 7 digits (no alphabets!) or leave blank');


