<?hh // strict

namespace HackPack\HackMini\Test\Filter;

use HackPack\HackMini\Filter\Email;
use HackPack\HackUnit\Contract\Assert;

class EmailTest {
  <<DataProvider('valid emails')>>
  public static function valid(): Traversable<string> {
    return
      [
        'email@example.com',
        'firstname.lastname@example.com',
        'email@subdomain.example.com',
        'firstname+lastname@example.com',
        'email@[123.123.123.123]',
        '"email"@example.com',
        '1234567890@example.com',
        'email@example-one.com',
        '_______@example.com',
        'email@example.name',
        'email@example.museum',
        'email@example.co.jp',
        'firstname-lastname@example.com',
        'much."more\ unusual"@example.com',
        'very.unusual."@".unusual.com@example.com',
        'very."(),:;<>[]".VERY."very@\\\\\\ \"very".unusual@strange.example.com',
      ];
  }

  <<Test, Data('valid emails')>>
  public function validTest(Assert $assert, string $email): void {
    $filter = new Email();
    $assert->bool($filter->validate($email))->is(true);
    $assert->string($filter->transform($email))->is($email);
  }

  <<DataProvider('invalid emails')>>
  public static function invalid(): Traversable<string> {
    return [
      'plainaddress',
      '#@%^%#$@#$@#.com',
      '@example.com',
      'Joe Smith <email@example.com>',
      'email.example.com',
      'email@example@example.com',
      '.email@example.com',
      'email.@example.com',
      'email..email@example.com',
      'あいうえお@example.com',
      'email@example.com (Joe Smith)',
      'email@example',
      'email@-example.com',
      // 'email@example.web', // Domain names are not validated
      'email@123.123.123.123',
      'email@111.222.333.44444',
      'email@example..com',
      'Abc..123@example.com',
      '"(),:;<>[\]@example.com',
      'just"not"right@example.com',
      'this\ is\"really\"not\\\\allowed@example.com',
    ];
  }

  <<Test, Data('invalid emails')>>
  public function invalidTest(Assert $assert, string $email): void {
    $filter = new Email();
    if ($filter->validate($email) !== false) {
      var_dump($email);
    }
    $assert->bool($filter->validate($email))->is(false);
    $assert->string($filter->transform($email))->is('');
  }

  <<DataProvider('non string')>>
  public static function nonString(): Traversable<mixed> {
    return [null, 1, 1.0, [], new self()];
  }

  <<Test, Data('non string')>>
  public function nonStringTest(Assert $assert, mixed $email): void {
    $filter = new Email();
    $assert->bool($filter->validate($email))->is(false);
    $assert->string($filter->transform($email))->is('');
  }
}
