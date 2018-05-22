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

$I->click('//a[@href="/control/blog/restore/1?redirect_uri=%2Fcontrol%2Fblog%2Fsearch%3Ffilter%5Bblog_active%5D%3D0"]');

$I->seeInCurrentUrl('/control/blog/search');
$I->wait(1);
$I->see('Article was Restored');