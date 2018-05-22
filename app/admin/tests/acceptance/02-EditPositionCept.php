<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Update Position');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/position/search');
$I->amGoingTo('Click Create New Position');

// redirect to update position
$I->click('//a[@href="/control/position/update/2"]');

$I->seeInCurrentUrl('/control/position/update/2');
$I->see('Updating Position');

$I->fillField('.page-developer-position-update input[name="position_name"]', 'Openovate Labs Test');
$I->selectOption('//select[@data-do="hide-fields"]', 'Child');

// submit form
$I->click('.page-developer-position-update button.btn-primary');

$I->seeInCurrentUrl('/control/position/search');
$I->wait(2);
$I->see('Position was Updated');


