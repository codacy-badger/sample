<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Search Profile Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Search Profile Details');

// 
$I->fillField('.page-admin-profile-search form.search input[name="q[]"]', 'john doe');

// submit form
$I->click('.page-admin-profile-search form.search button.btn');

$I->seeInCurrentUrl('/control/profile/search?select_filter=profile_id&q%5B%5D=john+doe');

