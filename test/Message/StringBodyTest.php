<?hh // strict

namespace HackPack\HackMini\Test\Message;

use HackPack\HackMini\Message\StringBody;
use HackPack\HackUnit\Contract\Assert;
use HackPack\HackMini\Message\Whence;

class StringBodyTest {
  <<Test>>
  public function append(Assert $assert): void {
    $body = new StringBody('some text');
    $body->seek(0, Whence::END);
    $body->write(' and more');

    $assert->string((string) $body)->is('some text and more');
  }

  <<Test>>
  public function insert(Assert $assert): void {
    $body = new StringBody('012345');
    $body->seek(2);
    $body->write('a');
    $assert->string((string) $body)->is('01a345');
  }

  <<Test>>
  public function prepend(Assert $assert): void {
    $body = new StringBody('some text');
    $body->write('one ');
    $assert->string((string) $body)->is('one  text');
  }

  <<Test>>
  public function seek(Assert $assert): void {
    $body = new StringBody('some text');
    $body->seek(-1, SEEK_END);
    $assert->string($body->read(1))->is('t');
    $assert->string($body->getContents())->is('');
  }
}
