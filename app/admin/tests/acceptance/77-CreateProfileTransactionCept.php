<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Create Profile Transaction');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('View Profile Transaction');

$I->click(['xpath' => '//a[@href="/control/transaction/create/5?redirect_uri=%2Fcontrol%2Fprofile%2Fsearch"]']);
$I->seeInCurrentUrl('/control/transaction/create/5?redirect_uri=%2Fcontrol%2Fprofile%2Fsearch');

$I->see('Create Transaction');

$I->selectOption('//form/div/div/select[@name="transaction_status"]', 'verified');
$I->fillField('.page-developer-transaction-create input[name="transaction_payment_method"]', 'paypal');
$I->fillField('.page-developer-transaction-create input[name="transaction_payment_reference"]', '1234567890');
$I->fillField('.page-developer-transaction-create input[name="transaction_statement"]', 'Jobayan Credits');
$I->fillField('.page-developer-transaction-create input[name="transaction_currency"]', 'PHP');
$I->fillField('.page-developer-transaction-create input[name="transaction_total"]', '1000');
$I->fillField('.page-developer-transaction-create input[name="transaction_credits"]', '1000');

$I->click('.page-developer-transaction-create button.btn-primary');

$I->wait(2);
$I->see('Transaction was Created');

