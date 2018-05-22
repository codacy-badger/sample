<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Buy Credits');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page
$I->amOnPage('/profile/credit/checkout');
$I->see('Buy Credits');

//  fill fields
$I->amGoingTo('Fill Billing Information');
$I->fillField('.page-profile-credit-checkout input[name="amount"]', '50000');
$I->click('.page-profile-credit-checkout .bank-deposit-button-title');
$I->wait(5);
$I->see('Bank Details');
$I->see('Sterling Openovate Corporation');
$I->see('Metropolitan Bank & Trust Company');
$I->see('#233-7-233551429(PESO ACCOUNT)');
$I->see('Kayamanan-C');
// modal
$I->click('.credit-card-button.payment-button.collapsed');
$I->wait(5);
$I->fillField('.page-profile-credit-checkout input[name="name"]', 'John Doe');
$I->fillField('.page-profile-credit-checkout input[name="number"]', '4242424242424242');
$I->fillField('.page-profile-credit-checkout input[name="exp_month"]', '12');
$I->fillField('.page-profile-credit-checkout input[name="exp_year"]', '2019');
$I->fillField('.page-profile-credit-checkout input[name="cvc"]', '123');
$I->click('.page-profile-credit-checkout div.panel a.btn.btn-default');
$I->wait(5);
$I->click('.page-profile-credit-checkout .checkout-modal .modal-footer button.btn.btn-default','#checkout_modal');

// $I->seeInCurrentUrl('/profile/credit/checkout');
// $I->see('Transaction Successful');
// $I->see('Invalid Parameters');
// $I->see('Invalid Credit Card Format');
$I->see('The payment could not be processed at this time. Please try again later.');
// $I->seeInCurrentUrl('/profile/transaction/search#history');
// $I->see('PHP 50,000');
// $I->see('Complete');
