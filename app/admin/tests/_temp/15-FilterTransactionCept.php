<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Transaction Status');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/transaction/search');
$I->amGoingTo('Filter Transaction Status');


// $I->selectOption('//form/select[@data-do="show-select"]', 'filter by method');
// $I->selectOption('//form/select[@name="filter[transaction_payment_method]"]', 'paypal');
//$I->click('.page-admin-transaction-search input.btn-success');

// $I->seeInCurrentUrl('/control/transaction/search?filter%5Btransaction_payment_method%5D=paypal');

$I->selectOption('//form/div/select[@data-do="redirect-filter"]', 'All');
$I->wait(2);
// $I->seeInCurrentUrl('/control/transaction/search');
$I->see('Transactions');

// $I->selectOption('//form/div/select[@data-do="redirect-filter"]','Complete');
// $I->wait(2);
// // $I->seeInCurrentUrl('/control/transaction/search?filter[transaction_status]=complete');
// $I->see('0 Transactions');
// $I->see('No Results Found');

// $I->selectOption('//form/div/select[@data-do="redirect-filter"]', 'Verified');
// $I->wait(2);
// // $I->seeInCurrentUrl('/control/transaction/search?filter[transaction_status]=verified');
// $I->see('0 Transactions');

