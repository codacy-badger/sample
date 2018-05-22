<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Terms By Type');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/term/search');
$I->amGoingTo('Filter Terms By Type');

$I->wantTo('Click Position');
// $I->click(['xpath' => '//tr/td/a[@href="?filter[term_type]=position"]']);
$I->selectOption('.filter-form select[name="filter[term_type]"]', 'Position');
$I->click('.filter-form input[value="Filter"]');
$I->seeInCurrentUrl('/control/term/search?filter%5Bterm_type%5D=position');