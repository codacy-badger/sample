<?php
$I = new AcceptanceTester($scenario);

$I->wantTo('Fail Login Poster Account.');
$I->amOnPage('/');

/**
// poster login blank fields
*
**/
$I->amOnPage('/login');
$I->see('Login');
$I->amGoingTo('Fill the login form');

// fill fields
$I->fillField('auth_slug', '');
$I->fillField('auth_password', '');
$I->click('.page-auth-login div.form-group button.btn.btn-default');

// notification
$I->see('Log in Failed');
$I->see('Cannot be empty');
$I->see('Cannot be empty');

/**
// assert invalid email blank password
*
**/

$I->amOnPage('/login');

$I->see('Login');
$I->amGoingTo('Fill the login form');

// fill fields
$I->fillField('auth_slug', 'asda@test');
$I->fillField('auth_password', '');
$I->click('.page-auth-login div.form-group button.btn.btn-default');

// notification
$I->wait(2);
$I->see('Log in Failed');
$I->see('User does not exist');
$I->see('Cannot be empty');

/**
*
// assert blank email correct password
**/

$I->amOnPage('/login');

$I->see('Login');
$I->amGoingTo('Fill the login form');

// fill fields
$I->fillField('auth_slug', '');
$I->fillField('auth_password', 'password123');
$I->click('.page-auth-login div.form-group button.btn.btn-default');

// notification
$I->wait(1);
$I->see('Log in Failed');
$I->see('Cannot be empty');

/**
*
* assert correct email wrong password
**/

$I->amOnPage('/login');

$I->see('Login');
$I->amGoingTo('Fill the login form');

// fill fields
$I->fillField('auth_slug', 'john@doe.com');
$I->fillField('auth_password', 'password123');
$I->click('.page-auth-login div.form-group button.btn.btn-default');

// notification
$I->wait(1);
$I->see('Log in Failed');
$I->see('Password is incorrect');