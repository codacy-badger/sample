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
$I->fillField('.page-profile-widget-form input[name="widget_button_title"]', '');
$I->fillField('.page-profile-widget-form input[name="widget_header_color"]', 'test');
$I->fillField('.page-profile-widget-form input[name="widget_button_color"]', 'test');
$I->selectOption('.page-profile-widget-form input[name="widget_button_position"]', 'bottom-left');
$I->fillField('.page-profile-widget-form input[name="widget_domain"]', 'test');

$I->click('.page-profile-widget-form button.btn.btn-default.text-uppercase');

// notification
$I->see('Invalid Parameters');
$I->see('Invalid widget header color');
$I->see('Invalid widget button color');
$I->see('Invalid domain');


// applicant tracking system
// $I->click('//a[@class="tracking"]');
// $I->wait(3);
// $I->see('STICK WITH US!');
// $I->see('We are currently working on this feature');
// $I->see('Enter your email address to get the latest updates.');

// $I->fillField('.page-profile-widget-form #coming-soon-modal form#widget-subscribe-form input[name="entry.1160409630"]', 'test@gmail.com');
// $I->click('.page-profile-widget-form #coming-soon-modal button.btn.btn-default');
// $I->see('You have successfully subscribed to our newsletter');
// $I->wait(3);
// $I->seeInCurrentUrl('/profile/widget/settings');


// // Interview Scheduler
// $I->click('//a[@class="scheduler"]');
// $I->wait(3);
// $I->see('STICK WITH US!');
// $I->see('We are currently working on this feature');
// $I->see('Enter your email address to get the latest updates.');

// $I->fillField('.page-profile-widget-form #coming-soon-modal form#widget-subscribe-form input[name="entry.1160409630"]', 'test@gmail.com');
// $I->click('.page-profile-widget-form #coming-soon-modal button.btn.btn-default');
// $I->see('You have successfully subscribed to our newsletter');
// $I->wait(3);
// $I->seeInCurrentUrl('/profile/widget/settings');