<?php //-->
// use Page\Login as LoginPage;
// $I = new AcceptanceTester($scenario);

// $I->wantTo('Create new Position');

// //Login
// $loginPage = new LoginPage($I);
// $loginPage->loginRole();


// $I->expect('Homepage');
// $I->seeInCurrentUrl('/');

// // redirect page

// $I->amOnPage('/control/position/search');
// $I->amGoingTo('Click Create New Position');

// // redirect to create utm
// $I->click(['xpath'=> '//a[@href="/control/position/create"]']);

// $I->seeInCurrentUrl('/control/position/create');
// $I->see('Create Position');

// $I->fillField('.page-developer-position-create input[name="position_name"]', 'Test Position');
// $I->fillField('.page-developer-position-create textarea[name="position_description"]', 'Test Position Desc');
// $I->executeJS('$(\'.tag-field\').append(\'<input type="hidden" name="position_skills[]" value="PHP" />\')');

// $I->click('.page-developer-position-create button.btn-primary');

// $I->seeInCurrentUrl('/control/position/search');

// $I->wait(2);
// $I->see('Position was Created');