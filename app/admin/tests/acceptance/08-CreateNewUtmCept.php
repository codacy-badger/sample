<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Create new UTM');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/utm/search');
$I->amGoingTo('Click Create New Utm');

// redirect to create utm
$I->click(['xpath'=> '//a[@href="/control/utm/create"]']);

$I->seeInCurrentUrl('/control/utm/create');
$I->see('Create UTM');

$I->fillField('.page-developer-utm-create input[name="utm_title"]', 'Openovate Labs');
$I->fillField('.page-developer-utm-create input[name="utm_source"]', 'Openovate Labs');
$I->fillField('.page-developer-utm-create input[name="utm_medium"]', 'Openovate Labs');
$I->fillField('.page-developer-utm-create input[name="utm_campaign"]', 'Openovate Labs');
$I->fillField('.page-developer-utm-create textarea[name=utm_detail]', 'Openovate Labs');

// submit form
$I->click('.page-developer-utm-create button.btn-primary');

$I->seeInCurrentUrl('/control/utm/search');
$I->wait(2);
$I->see('UTM was Created');

