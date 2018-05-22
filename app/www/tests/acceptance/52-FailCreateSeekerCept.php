<?php

/**
//  assert 1
*
**/

$I = new AcceptanceTester($scenario);

$I->wantTo('Fail signup a new poster account.');	
/**
* for seeker sign up
**/

// seeker sign up 
$I->amOnPage('/');
$I->wantTo('Signup a new seeker account.');
$I->amGoingTo('go to the signup page');

$I->amOnPage('/signup');
$I->see('create your account');
$I->amGoingTo('signup the form');

// select seeker or poster
$I->click('Applicant');

// fill fields
$I->fillField('profile_name', '');
$I->fillField('profile_email', '');
$I->fillField('auth_password', '');
$I->fillField('confirm', '');
$I->click('Create Account');

// notification & validation
$I->wait(2);
$I->see('Registration Failed');
$I->see('Name is required');
$I->see('Cannot be empty');
$I->see('Cannot be empty');
$I->see('Must be at least 7 characters long');

//  assert 2 
$I->amOnPage('/signup');

// select seeker
$I->click('Applicant');

// fill fields
$I->fillField('profile_name', '');
$I->fillField('profile_email', 'asda@test.com');
$I->fillField('auth_password', 'asdasd123');
$I->fillField('confirm', 'asdasd123');
$I->click('Create Account');

// notification & validation
$I->wait(2);
$I->see('Registration Failed');
$I->see('Name is required');

//  assert 3 
$I->amOnPage('/signup');

// select seeker
$I->click('Applicant');

// fill fields
$I->fillField('profile_name', 'test');
$I->fillField('profile_email', '');
$I->fillField('auth_password', 'asdasd123');
$I->fillField('confirm', 'asdasd123');
$I->click('Create Account');

// notification & validation
$I->wait(2);
$I->see('Registration Failed');
$I->see('Cannot be empty');

//  assert 4 
$I->amOnPage('/signup');

// select seeker
$I->click('Applicant');

// fill fields
$I->fillField('profile_name', 'test');
$I->fillField('profile_email', 'axczxczx@asd');
$I->fillField('auth_password', 'asdasd123');
$I->fillField('confirm', 'asdasd123');
$I->click('Create Account');

// notification & validation
$I->wait(2);
$I->see('Registration Failed');
$I->see('Must be a valid email');

//  assert 5 
$I->amOnPage('/signup');

// select seeker
$I->click('Applicant');

// fill fields
$I->fillField('profile_name', 'test');
$I->fillField('profile_email', 'axczxczx@asd.com');
$I->fillField('auth_password', '123');
$I->fillField('confirm', 'asdasd123');
$I->click('Create Account');

// notification & validation
$I->wait(2);
$I->see('Registration Failed');
$I->see('Passwords do not match');


//  assert 6 
$I->amOnPage('/signup');

// select seeker
$I->click('Applicant');

// fill fields
$I->fillField('profile_name', '');
$I->fillField('profile_email', 'asda@test');
$I->fillField('auth_password', '123');
$I->fillField('confirm', 'asdasd123');
$I->click('Create Account');

// notification & validation
$I->wait(2);
$I->see('Registration Failed');
$I->see('Name is required');
$I->see('Must be a valid email');
$I->see('Passwords do not match');
