<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Edit UTM');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/utm/search');
$I->amGoingTo('Click Edit Utm');

// redirect to edit utm
$I->click(['xpath'=> '//tr/td/a[@href="/control/utm/update/2"]']);

$I->seeInCurrentUrl('/control/utm/update/2');
$I->see('Updating UTM');

// edit details
$I->fillField('.page-developer-utm-update input[name="utm_title"]', 'Foo Bar Test');

// submit form
$I->click('.page-developer-utm-update button.btn-primary');

$I->seeInCurrentUrl('/control/utm/search');
$I->wait(2);
$I->see('UTM was Updated');