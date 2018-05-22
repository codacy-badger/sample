<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Experience Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Filter Experience Post');

$I->wantTo('Click Ascending');
$I->click('a[href="?filter[post_active]=1&order[post_experience]=ASC"]');
// $I->seeInCurrentUrl('/control/post/search?q[]=&order[post_id]=ASC');
$I->seeInCurrentUrl('/control/post/search?filter%5Bpost_active%5D=1&order%5Bpost_experience%5D=ASC');

$I->wantTo('Click Descending');
$I->click('a[href="?filter[post_active]=1&order[post_experience]=DESC"]');
// $I->seeInCurrentUrl('/control/post/search?q[]=&order[post_id]=ASC');
$I->seeInCurrentUrl('/control/post/search?filter%5Bpost_active%5D=1&order%5Bpost_experience%5D=DESC');