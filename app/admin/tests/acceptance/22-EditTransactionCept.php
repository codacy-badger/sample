<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Edit Transaction Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/transaction/search');
$I->amGoingTo('Edit Transaction Details');

// redirect to edit utm
$I->click(['xpath'=> '//tr/td/a[@href="/control/transaction/update/1"]']);

$I->seeInCurrentUrl('/control/transaction/update/1');
$I->see('Updating Transaction');

// edit details
$I->fillField('.page-developer-transaction-update input[name="transaction_credits"]', '5000');

// submit form
$I->click('.page-developer-transaction-update button.btn-primary');

$I->seeInCurrentUrl('/control/transaction/search');
$I->wait(2);
$I->see('Transaction was Updated');
