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

// $I->click('.detail-head input[name="post_notify[]"]');
$I->executeJS('$(\'#ats_applicant_bulk\').click()');

$I->click('.remove .btn-group.btn-tag button.btn.btn-default.dropdown-toggle');

$I->wait(5);

$I->click('.btn-group.btn-tag.open ul.dropdown-menu.label-menu a[data-label="Passed"]');

$I->wait(1);

$I->see('Label was successfully attached');

$I->amGoingTo('Remove Eligible');

$I->amOnPage('/profile/tracking/post/detail/12');

$I->click('.list-label .label.label-default a[data-label-name="Illegible"]');

$I->wait(5);

$I->see('Remove Label');

$I->see('Are you sure want to remove this label?');

$I->click('.modal.fade.confirmation-form.in .modal-footer button.btn.btn-default');

$I->wait(1);

$I->see('Label was successfully removed');

//  redirect page
$I->amOnPage('/profile/tracking/post/detail/12');

$I->executeJS('$(\'#ats_applicant_bulk\').click()');

$I->click('.remove a[data-label-name="Illegible"]');

$I->wait(1);

$I->see('Label was successfully attached');