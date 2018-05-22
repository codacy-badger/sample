<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Profile Credits');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Filter Profile Credits');

$I->wantTo('Click Ascending');
$I->click('a[href="?filter[profile_active]=1&order[profile_credits]=ASC"]');
// $I->seeInCurrentUrl('/control/post/search?q[]=&order[profile_id]=ASC');
$I->seeInCurrentUrl('/control/profile/search?filter%5Bprofile_active%5D=1&order%5Bprofile_credits%5D=ASC');

$I->wantTo('Click Descending');
$I->click('a[href="?filter[profile_active]=1&order[profile_credits]=DESC"]');
// $I->seeInCurrentUrl('/control/post/search?q[]=&order[profile_id]=ASC');
$I->seeInCurrentUrl('/control/profile/search?filter%5Bprofile_active%5D=1&order%5Bprofile_credits%5D=DESC');