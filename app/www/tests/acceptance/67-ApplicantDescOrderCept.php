<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('ASC DESC order');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

//  redirect page
$I->amOnPage('/profile/tracking/post/search');

$I->click('a[href="/profile/tracking/post/detail/12"]');

$I->seeInCurrentUrl('/profile/tracking/post/detail/12');

$I->click('.page-tracking-post-detail .sort a[href="?order[profile_name]=DESC&q="]');

$I->seeInCurrentUrl('/profile/tracking/post/detail/12?order%5Bprofile_name%5D=DESC&q=');

$I->click('.page-tracking-post-detail .sort a[href="?order[profile_name]=ASC&q="]');

$I->seeInCurrentUrl('/profile/tracking/post/detail/12?order%5Bprofile_name%5D=ASC&q=');
