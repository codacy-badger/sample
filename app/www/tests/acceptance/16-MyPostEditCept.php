<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Edit a post');

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
$I->fillField('.page-post-update form#post-form input[name="post_name"]', 'Test Labster');
$I->fillField('.page-post-update form#post-form input[name="post_position"]', 'IT and Software');

$I->click('.page-post-update button.submit');
$I->seeInCurrentUrl('/post/search');
$I->wait(3);
$I->see('Post was Updated');
