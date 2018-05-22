<?php
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Click Job Position Link');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->seeInCurrentUrl('/');
$I->amOnPage('/profile/seeker/search');
$I->see('Job Seeker Search');

// $I->amGoingTo('Click Job Position Link');
// $I->click(['xpath'=>'//div/a[@href="/Customer-Support-p2/post-detail"]']);

// $I->seeInCurrentUrl('/Customer-Support-p2/post-detail');
// $I->see('Jane Doe');
// $I->see('Hello, my name is Jane Doe and I am a Customer Support with 2 years of experience');
// $I->see('Interested');
// $I->click('//a[@data-id="2"]');
// $I->seeInCurrentUrl('/Customer-Support-p2/post-detail');
// $I->wait(5);
// $I->see("Yay, Achievement Unlocked!");
// $I->click('.modal.fade.achievement-modal.in .modal-footer button.btn.btn-default');
// $I->see('You earned 100 experience points');
// $I->see('User is being notified of your interest');

