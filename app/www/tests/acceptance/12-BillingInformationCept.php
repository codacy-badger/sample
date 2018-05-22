<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Update Billing Information');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->seeInCurrentUrl('/');
$I->amOnPage('/profile/transaction/search');
$I->see('Billing Information');

// redirect to edit
$I->click(['xpath' => '//div/a[@href="/profile/transaction/search/update"]']);
$I->seeInCurrentUrl('/profile/transaction/search/update');

$I->fillField('.page-profile-transaction-search input[name="profile_address_street"]', '123 Sesame Street');
// $I->fillField('.page-profile-transaction-search input[name="profile_address_state"]', 'Metro Manila');
$I->fillField('.page-profile-transaction-search input[name="profile_address_postal"]', '12345');
$I->executeJS('$(\'select[id="state"]\').show();');
$I->selectOption('.page-profile-transaction-search select[id="state"]', 'Metro Manila');
$I->wait(5);
$I->executeJS('$(\'select[id="city"]\').show();');
$I->selectOption('.page-profile-transaction-search select[id="city"]', 'Caloocan City');

$I->click('.page-profile-transaction-search div.contact-wrapper button.btn.btn-default');

$I->seeInCurrentUrl('/profile/transaction/search');
$I->wait(2);
$I->see('Update Successful');

// search on page
// $I->fillField('.page-profile-transaction-search form.form-inline input.form-control', 'pending');
// $I->click('.page-profile-transaction-search form.form-inline span.input-group-btn button.btn.btn-default');
// $I->see('No Results Found');

// Filter History by month and year

// $I->amOnPage('/profile/transaction/search');
// $I->selectOption('form.view-all.pull-right.form-inline select[name="month"]', 'January');
// $I->selectOption('form.view-all.pull-right.form-inline select[name="year"]', '2018');
// $I->click('form.view-all.pull-right.form-inline button.btn.btn-default');
// // $I->see('No Results Found');

// // click reference number
// $I->amOnPage('/profile/transaction/search');
// $I->click(['xpath' => '//tr/td/a[@href="/profile/transaction/detail/1"]']);
// $I->seeInCurrentUrl('/profile/transaction/detail/1');

// // credit history
// $I->click('//div/a[@href="/profile/credit/search"]');
// $I->seeInCurrentUrl('/profile/credit/search');

// $I->selectOption('form.view-all.pull-right.form-inline select[name="month"]', 'January');
// $I->selectOption('form.view-all.pull-right.form-inline select[name="year"]', '2018');

// $I->click('form.view-all.pull-right.form-inline button.btn.btn-default');