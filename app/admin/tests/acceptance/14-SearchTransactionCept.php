<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Search Transaction Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/transaction/search');
$I->amGoingTo('Search Transaction Details');

//
$I->fillField('.page-admin-transaction-search input[name="q[]"]', '123');

// submit form
$I->click('.page-admin-transaction-search button.btn');

$I->seeInCurrentUrl('/control/transaction/search?filter%5Btransaction_status%5D=&date%5Bstart_date%5D=%2F&date%5Bend_date%5D=%2F&q%5B%5D=123');
