<?php //-->
// use Page\Login as LoginPage;
// $I = new AcceptanceTester($scenario);

// $I->wantTo('Create new Role');

// //Login
// $loginPage = new LoginPage($I);
// $loginPage->login();


// $I->expect('Homepage');
// $I->seeInCurrentUrl('/');

// // redirect page

// $I->amOnPage('/control/role/search');
// $I->amGoingTo('Click Create New Role');

// // redirect to create utm
// $I->click(['xpath'=> '//a[@href="/control/role/create"]']);

// $I->seeInCurrentUrl('/control/role/create');
// $I->see('Create Roles');

// $I->fillField('.page-admin-role-create input[name="role_name"]', 'Test Admin');
// $I->checkOption('input[name="role_permissions[admin:position:view]"]');
// $I->checkOption('input[name="role_permissions[admin:position:create]"]');
// $I->checkOption('input[name="role_permissions[admin:utm:view]"]');
// $I->checkOption('input[name="role_permissions[admin:utm:remove]"]');
// $I->checkOption('input[name="role_permissions[admin:transaction:view]"]');
// $I->checkOption('input[name="role_permissions[admin:transaction:update]"]');
// $I->checkOption('input[name="role_permissions[admin:transaction:remove]"]');
// $I->checkOption('input[name="role_permissions[admin:profile:view]"]');
// $I->checkOption('input[name="role_permissions[admin:profile:export]"]');
// $I->checkOption('input[name="role_permissions[admin:profile:upload-csv]"]');

// $I->click('.page-admin-role-create button.btn-primary');

// $I->seeInCurrentUrl('/control/role/search');

// $I->wait(2);
// $I->see('Role Successfully Created');