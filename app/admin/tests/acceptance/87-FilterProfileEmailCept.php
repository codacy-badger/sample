<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Profile Email');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Filter Profile Email');

$I->wantTo('Click Ascending');
$I->click('a[href="?filter[profile_active]=1}&order[profile_email]=ASC"]');
// $I->seeInCurrentUrl('/control/post/search?q[]=&order[profile_id]=ASC');
$I->seeInCurrentUrl('/control/profile/search?filter%5Bprofile_active%5D=1%7D&order%5Bprofile_email%5D=ASC');

$I->wantTo('Click Descending');
$I->click('a[href="?filter[profile_active]=1}}&order[profile_email]=DESC"]');
// $I->seeInCurrentUrl('/control/post/search?q[]=&order[profile_id]=ASC');
$I->seeInCurrentUrl('/control/profile/search?filter%5Bprofile_active%5D=1%7D%7D&order%5Bprofile_email%5D=DESC');