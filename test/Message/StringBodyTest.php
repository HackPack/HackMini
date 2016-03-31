<?hh // strict

namespace HackPack\HackMini\Test\Message;

use HackPack\HackMini\Message\StringBody;
use HackPack\HackUnit\Contract\Assert;

class StringBodyTest
{
  <<Test>>
  public function append(Assert $assert) : void
  {
    $body = new StringBody('some text');
    $body->seek(0, SEEK_END);
    $body->write(' and more');

    $assert->string((string)$body)->is('some text and more');
  }

  <<Test>>
  public function prepend(Assert $assert) : void
  {
    $body = new StringBody('some text');
    $body->write('one ');
    $assert->string((string)$body)->is('one  text');
  }
}
