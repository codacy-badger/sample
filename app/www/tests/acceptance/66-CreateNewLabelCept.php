<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('ASC DESC order');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

//  redirect page
$I->amOnPage('/profile/tracking/post/search');

$I->click('a[href="/profile/tracking/post/detail/12"]');

$I->seeInCurrentUrl('/profile/tracking/post/detail/12');

$I->click('.detail-wrapper .action .remove a i.fa.fa-plus');

$I->wait(5);

$I->see('Create a New Label');

$I->fillField('#confirmation-form-current .form-group.label-name input[name="label_name"]','Testing ATS');

$I->click('#confirmation-form-current form.form-horizontal.modal-form .modal-footer button.btn.btn-default');

$I->wait(1);

$I->see('Label successfully created');