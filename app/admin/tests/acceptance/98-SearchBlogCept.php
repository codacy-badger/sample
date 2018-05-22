<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Search Blog Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/blog/search');
$I->amGoingTo('Search Blog Details');

// 
$I->fillField('.page-admin-blog-search form.search input[name="q[]"]', 'blog search');

// submit form
$I->click('.page-admin-blog-search form.search button.btn');

$I->seeInCurrentUrl('/control/blog/search?q%5B%5D=blog+search');