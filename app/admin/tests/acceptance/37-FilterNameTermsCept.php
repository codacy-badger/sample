<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Terms By Type');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/term/search');
$I->amGoingTo('Filter Terms By Name');

$I->wantTo('Click Ascending');
$I->click('a[href="?filter[term_active]=1&q[]=&order[term_name]=ASC"]');
$I->seeInCurrentUrl('/control/term/search?filter%5Bterm_active%5D=1&q%5B%5D=&order%5Bterm_name%5D=ASC');

$I->wantTo('Click Descending');
$I->click('a[href="?filter[term_active]=1&q[]=&order[term_name]=DESC"]');
$I->seeInCurrentUrl('/control/term/search?filter%5Bterm_active%5D=1&q%5B%5D=&order%5Bterm_name%5D=DESC');
