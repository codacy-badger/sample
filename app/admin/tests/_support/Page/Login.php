<?php
namespace Page;

class Login
{
    public static $URL = '/login';

    public static $usernameField = 'auth_slug';
    public static $passwordField = 'auth_password';
    public static $loginButton = 'form button';


    public static $name = 'john@doe.com';
    public static $password = '123';

    public static $nameRole = 'testseeker123@gmail.com';
    public static $passwordRole = 'password123';


    /**
     * @var AcceptanceTester
     */
    protected $tester;

    public function __construct(\AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    public function login()
    {
        $I = $this->tester;

        $I->amOnPage(self::$URL);

        // Login
        $I->amGoingTo('Fillup the login form.');
        $I->fillField(self::$usernameField, self::$name);
        $I->fillField(self::$passwordField, self::$password);
        $I->click(self::$loginButton);

        $I->expect('Homepage');
        $I->seeInCurrentUrl('/');
        // $I->see('Account Settings');

        return $this;
    }

    public function loginRole()
    {
        $I = $this->tester;

        $I->amOnPage(self::$URL);
        // Login
        $I->amGoingTo('Fillup the login form.');
        $I->fillField(self::$usernameField, self::$nameRole);
        $I->fillField(self::$passwordField, self::$passwordRole);
        $I->click(self::$loginButton);

        $I->expect('Homepage');
        $I->seeInCurrentUrl('/');
        // $I->see('Account Settings');

        return $this;
    }
}