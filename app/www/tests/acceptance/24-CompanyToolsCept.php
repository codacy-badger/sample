<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Check Link of the post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page
$I->amOnPage('/profile/post/search');
$I->see('My Jobs');
$I->see('Job Seekers');
$I->click('.menu-content a[href="/profile/widget/settings"]');
$I->seeInCurrentUrl('/profile/widget/settings');
// $I->wait(3);
// $I->see('Career Widget');
// $I->see('Applicant Tracking System');
// $I->see('Interview Scheduler');


// click career page
// $I->click('//a[@href="/profile/widget/settings"]');
$I->see('Widget Settings');

// fill fields
$I->fillField('.page-profile-widget-form input[name="widget_button_title"]', 'Openovate Hiring!');
$I->fillField('.page-profile-widget-form input[name="widget_header_color"]', '#c9c9c9');
$I->fillField('.page-profile-widget-form input[name="widget_button_color"]', '#c9c9c9');
$I->selectOption('.page-profile-widget-form input[name="widget_button_position"]', 'bottom-left');
$I->fillField('.page-profile-widget-form input[name="widget_domain"]', 'http://www.mysite.com');

$I->click('.page-profile-widget-form button.btn.btn-default.text-uppercase');
$I->wait(2);
$I->see('Career widget has been successully updated');