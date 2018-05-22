<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Create new label');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

//  redirect page
$I->amOnPage('/profile/tracking/post/search');

$I->click('a[href="/profile/tracking/post/detail/12"]');

$I->seeInCurrentUrl('/profile/tracking/post/detail/12');

$I->click('.page-tracking-post-detail .filter .btn-group.btn-filter button');

$I->wait(5);

$I->click('ul.dropdown-menu.filter-menu li a[href="?filter[applicant_status]=Scheduled"]');

$I->seeInCurrentUrl('/profile/tracking/post/detail/12?filter%5Bapplicant_status%5D=Scheduled');

$I->click('.page-tracking-post-detail .filter .btn-group.btn-filter button');

$I->wait(5);

$I->click('ul.dropdown-menu.filter-menu li a[href="?filter[applicant_status]=Interviewed"]');

$I->seeInCurrentUrl('/profile/tracking/post/detail/12?filter%5Bapplicant_status%5D=Interviewed');

$I->click('.page-tracking-post-detail .filter .btn-group.btn-filter button');

$I->wait(5);

$I->click('ul.dropdown-menu.filter-menu li a[href="?filter[applicant_status]=Passed"]');

$I->seeInCurrentUrl('/profile/tracking/post/detail/12?filter%5Bapplicant_status%5D=Passed');

$I->click('.page-tracking-post-detail .filter .btn-group.btn-filter button');

$I->wait(5);

$I->click('ul.dropdown-menu.filter-menu li a[href="?filter[applicant_status]=Offer Letter"]');

$I->seeInCurrentUrl('/profile/tracking/post/detail/12?filter%5Bapplicant_status%5D=Offer%20Letter');

$I->click('.page-tracking-post-detail .filter .btn-group.btn-filter button');

$I->wait(5);

$I->click('ul.dropdown-menu.filter-menu li a[href="?filter[applicant_status]=Started"]');

$I->seeInCurrentUrl('/profile/tracking/post/detail/12?filter%5Bapplicant_status%5D=Started');

$I->click('.page-tracking-post-detail .filter .btn-group.btn-filter button');

$I->wait(5);

$I->click('ul.dropdown-menu.filter-menu li a[href="?filter[applicant_status]=Illegible"]');

$I->seeInCurrentUrl('/profile/tracking/post/detail/12?filter%5Bapplicant_status%5D=Illegible');