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

        return $this;
    }

    public function loginSeeker()
    {
        $I = $this->tester;

        $I->amOnPage(self::$URL);

        // Login
        $I->amGoingTo('Fillup the login form.');
        $I->fillField(self::$usernameField, 'test@gmail.com');
        $I->fillField(self::$passwordField, 'password123');
        $I->click(self::$loginButton);

        $I->expect('Homepage');
        $I->seeInCurrentUrl('/');

        return $this;
    }
}
