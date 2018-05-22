<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Go to Inactive Transaction');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/transaction/search');
$I->amGoingTo('Click Inactive Transaction');

$I->click('.page-admin-transaction-search a.btn-danger');

$I->seeInCurrentUrl('/control/transaction/search?filter%5Btransaction_active%5D=0');

$I->click(['xpath' => '//tr/td/a[@href="/control/transaction/restore/1"]']);

$I->seeInCurrentUrl('/control/transaction/search');