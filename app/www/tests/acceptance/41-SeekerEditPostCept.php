<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Edit a post');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/post/search');
$I->see('Post');
$I->click(['xpath' => '//div/a[@href="/post/update/seeker/10?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);

// update post page
$I->seeInCurrentUrl('/post/update/seeker/10?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
$I->expect('Updating Post');

$I->amGoingTo('Edit Post');

// fill edit fields
$I->fillField('.page-post-update form#post-form input[name="post_name"]', 'Test Labster');
$I->fillField('.page-post-update form#post-form input[name="post_position"]', 'Foo Bar Test');
$I->fillField('.page-post-update form#post-form input[name="post_phone"]', '12344567');

$I->click('.page-post-update button.submit');
$I->seeInCurrentUrl('/post/search');
$I->see('All job opportunities and job seekers');
$I->see('Post was Updated');
// $I->see('Foo Bar Test');
// $I->see('Test Labster');
// $I->see('Openovate Labs 2 is also looking for these positions');
// $I->see('Foo Bar Test in Metro Manila');

