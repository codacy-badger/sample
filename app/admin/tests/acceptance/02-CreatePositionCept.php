<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Create new Position');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/position/search');
$I->amGoingTo('Click Create New Position');

// redirect to create utm
$I->click(['xpath'=> '//a[@href="/control/position/create"]']);

$I->seeInCurrentUrl('/control/position/create');
$I->see('Create Position');

$I->fillField('.page-developer-position-create input[name="position_name"]', 'Openovate Labs');
$I->selectOption('//select[@data-do="hide-fields"]', 'Parent');
$I->fillField('.page-developer-position-create textarea[name="position_description"]', 'Openovate Labs');
$I->executeJS('$(\'.tag-field\').append(\'<div class="tag"><input type="text" class="tag-input text-field" name="position_skills[]" value="PHP"><a class="remove" href="javascript:void(0) style="width: 36.4531px;"><i class="fa fa-times"></i></a></div>\')');


// submit form
$I->click('.page-developer-position-create button.btn-primary');

$I->seeInCurrentUrl('/control/position/search');
$I->wait(2);
$I->see('Position was Created');


