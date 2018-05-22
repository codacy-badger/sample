<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Signup a new account.');
$I->amOnPage('/signup');
$I->amGoingTo('go to the signup page');
$I->see('CREATE ACCOUNT');
$I->amGoingTo('signup form');
$I->click('Employer');
$I->fillField('profile_name', 'New User');
$I->fillField('profile_email', 'josealfonso0607@gmail.com');
$I->fillField('profile_company', 'Test Comp');
$I->fillField('auth_password', 'password123');
$I->fillField('confirm', 'password123');
$I->click('Create Account');

$I->wait(2);
$I->see('Sign Up Successful. Please check your email for verification process.');
