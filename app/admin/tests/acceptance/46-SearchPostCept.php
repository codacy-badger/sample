<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Search Post Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Search Post Details');

// 
$I->fillField('.page-admin-post-search .search.form-inline input[name="q[]"]', 'metro manila');

// submit form
$I->click('.page-admin-post-search .search.form-inline button.btn');

$I->seeInCurrentUrl('/control/post/search?filter%5Bpost_type%5D=&date%5Bstart_date%5D=&date%5Bend_date%5D=&q%5B%5D=metro+manila');

$I->see('Posts matching Metro Manila');

