<?php
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Login to the site');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();
