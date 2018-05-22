<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Location Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Filter Location Post');

$I->wantTo('Click Ascending');
$I->click('a[href="?filter[post_active]=1&q[]=&order[post_location]=ASC"]');
// $I->seeInCurrentUrl('/control/post/search?q[]=&order[post_id]=ASC');
$I->seeInCurrentUrl('/control/post/search?filter%5Bpost_active%5D=1&q%5B%5D=&order%5Bpost_location%5D=ASC');

$I->wantTo('Click Descending');
$I->click('a[href="?filter[post_active]=1&q[]=&order[post_location]=DESC"]');
// $I->seeInCurrentUrl('/control/post/search?q[]=&order[post_id]=ASC');
$I->seeInCurrentUrl('/control/post/search?filter%5Bpost_active%5D=1&q%5B%5D=&order%5Bpost_location%5D=DESC');