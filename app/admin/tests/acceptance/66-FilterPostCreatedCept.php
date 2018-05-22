<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Post Created');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Filter Post Created');

$I->wantTo('Click Ascending');
$I->click('a[href="?filter[post_active]=1&order[post_created]=ASC"]');
// $I->seeInCurrentUrl('/control/post/search?q[]=&order[post_id]=ASC');
$I->seeInCurrentUrl('/control/post/search?filter%5Bpost_active%5D=1&order%5Bpost_created%5D=ASC');

$I->wantTo('Click Descending');
$I->click('a[href="?filter[post_active]=1&order[post_created]=DESC"]');
// $I->seeInCurrentUrl('/control/post/search?q[]=&order[post_id]=ASC');
$I->seeInCurrentUrl('/control/post/search?filter%5Bpost_active%5D=1&order%5Bpost_created%5D=DESC');