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
$I->fillField('.page-profile-credit-checkout input[name="amount"]', '');
$I->fillField('.page-profile-credit-checkout input[name="name"]', '');
$I->fillField('.page-profile-credit-checkout input[name="number"]', '');
$I->fillField('.page-profile-credit-checkout input[name="exp_month"]', '');
$I->fillField('.page-profile-credit-checkout input[name="exp_year"]', '');
$I->fillField('.page-profile-credit-checkout input[name="cvc"]', '');
$I->click('.page-profile-credit-checkout div.panel-body a.btn.btn-default');
$I->wait(5);
$I->click('.page-profile-credit-checkout .modal-footer button.btn.btn-default','#checkout_modal');

// validation notification
$I->seeInCurrentUrl('profile/credit/checkout');
$I->wait(3);
$I->see('Invalid Parameters');
$I->click('.credit-card-button.payment-button.collapsed');
$I->wait(5);
$I->see('Name is Required');
$I->see('Invalid Credit Card Format');
$I->see('Invalid Date Format');
$I->see('Invalid CVC Format');