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
$I->click(['xpath'=>'//a[@href="/It-And-Software-p1/post-detail"]']);
$I->wait(2);

$I->seeInCurrentUrl('/It-And-Software-p1/post-detail');

//	Jobayan tips location
// $I->click('.page-post-detail div.post-tips-wrapper div.tips-body div.location a.btn.btn-primary');
// $I->wait(3);
// $I->selectOption('.page-post-search div.form-group select[name="post_location"]', 'Quezon City');
// $I->click('.page-post-search #industry .modal-footer button.btn.btn-primary','#industry');
// $I->wait(3);
// $I->see('Post was Updated');

//	Jobayan tips arrangement
// $I->click('.page-post-detail div.post-tips-wrapper div.tips-body div.arrangement a[data-do="arrangement-full"]');
// $I->wait(1);
// $I->see('Post was Updated');
// $I->wait(3);


//  promote post
// $I->click('.page-post-detail div.post-tips-wrapper div.tips-body div.promote-post a[data-do="promote-post"]');
// $I->wait(2);
// $I->see('You earned 500 experience points');
// $I->see('Promote Post Success');

// sms notif
// $I->click('.page-post-detail div.post-tips-wrapper div.tips-body div.sms-match a[data-do="sms-notification"]');
// $I->wait(1);
// $I->see('SMS Notification Success');

// edit post
$I->click('//a[@href="/post/update/poster/1?redirect_uri=/It-And-Software-p1/post-detail"]');
$I->wait(3);
$I->seeInCurrentUrl('/post/update/poster/1?redirect_uri=/It-And-Software-p1/post-detail');
$I->fillField('.page-post-update div.form-group input[name="post_name"]','Openovate Tester2');
$I->click('//button[@data-do="submit-post"]');
$I->wait(2);
$I->seeInCurrentUrl('/post/search');
$I->wait(2);
$I->see('Post was Updated');
$I->see('All job opportunities and job seekers');

// remove post
// $I->click('//a[@href="/post/remove/10?redirect_uri=/post/search"]');
// $I->seeInCurrentUrl('/post/search');
// $I->wait(3);
// $I->see('Post was Removed');
// $I->see('All job opportunities and job seekers');

// $I->see('All job opportunities');
// $I->seeInCurrentUrl('/post/search');
