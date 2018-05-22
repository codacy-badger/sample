<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Edit Auth');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/auth/search');
$I->amGoingTo('Click Edit Auth');

// redirect to edit auth
$I->click('//tr/td/a[@href="/control/auth/update/4"]');

$I->seeInCurrentUrl('/control/auth/update/4');
$I->see('Updating Auth');

// edit details
$I->fillField('.page-developer-auth-update input[name="auth_password"]', 'password123');
$I->fillField('.page-developer-auth-update input[name="confirm"]', 'password123');

// submit form
$I->click('.page-developer-auth-update button.btn-primary');

$I->seeInCurrentUrl('/control/auth/update/4');

$I->wait(2);
$I->see('Auth was Updated');


