<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Actions ATS');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

//  redirect page
$I->amOnPage('/profile/tracking/post/detail/12');

$I->amGoingTo('Set Label');

$I->click('.list-action .btn-group.btn-tag button');

$I->wait(5);

$I->click('.btn-group.btn-tag.open .dropdown-menu.label-menu li a[data-label="Started"]');

$I->wait(1);

$I->see('Label was successfully attached');


$I->amOnPage('/profile/tracking/post/detail/12');

$I->amGoingTo('Remove Label');

$I->click('a[data-label-name="Started"]');

$I->wait(5);

$I->see('Remove Label');

$I->see('Are you sure want to remove this label?');

$I->click('#confirmation-form-current .modal-footer button.btn.btn-default');

$I->wait(1);

$I->see('Label was successfully removed');


$I->amOnPage('/profile/tracking/post/detail/12');

$I->amGoingTo('Mark as illegible');

$I->click('.list-action a[data-type="remove-applicant"]');

$I->wait(5);

$I->see('You are removing this applicant. Once deleted');

$I->click('.confirmation-form.in .modal-footer button.btn.btn-default');

$I->wait(1);

$I->see('Label was successfully attached');