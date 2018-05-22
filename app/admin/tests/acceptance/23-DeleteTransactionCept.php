<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Delete Transaction');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/transaction/search');
$I->amGoingTo('Delete Transaction');

// submit form
$I->click(['xpath' => '//tr/td/a[@href="/control/transaction/remove/1"]']);

$I->seeInCurrentUrl('/control/transaction/search');

$I->wait(2);
$I->see('Transaction was Removed');


