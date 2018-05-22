<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Edit Services');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/service/search');
$I->amGoingTo('Click Edit Services');

// redirect to edit utm
$I->click(['xpath'=> '//tr/td/a[@href="/control/service/update/1"]']);

$I->seeInCurrentUrl('/control/service/update/1');
$I->see('Updating Service');

// edit details
$I->fillField('.page-developer-service-update input[name="service_credits"]', '1000');

// submit form
$I->click('.page-developer-service-update button.btn-primary');

$I->seeInCurrentUrl('/control/service/search');

$I->wait(2);
$I->see('Service was Updated');

