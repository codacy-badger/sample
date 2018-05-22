<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Go to Active Transaction');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/transaction/search');
$I->amGoingTo('Click Active Transaction');

$I->click('.page-admin-transaction-search a.btn-primary');

$I->seeInCurrentUrl('/control/transaction/search?filter%5Btransaction_active%5D=1');