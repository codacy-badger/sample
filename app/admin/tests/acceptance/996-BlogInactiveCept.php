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



// click active blog
$I->click('//a[@href="?filter[blog_active]=0"]');

$I->seeInCurrentUrl('/control/blog/search');