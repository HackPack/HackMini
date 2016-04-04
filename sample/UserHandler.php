<?hh // strict

namespace HackPack\HackMini\Sample;

use HackPack\HackMini\Validator\AlphaNumeric;
use HackPack\HackMini\Validator\Raw;
use FactoryContainer;

<<
Command('user:create'),
Arguments('name=default'),
Options('t|title=default'),
UseMiddleware('showcolors')
>>
function createUserFromCli(
  \FactoryContainer $c,
  \HackPack\HackMini\Command\Request $req,
  \HackPack\HackMini\Command\UserInteraction $interact,
): int {
  $userStore = $c->getUserStore();

  $title = $req->getFirst('title');
  $users =
    $req->unnamedArguments()
      ->toVector()
      ->add($req->atFirst('name'))
      ->map(
        $name ==> {
          $user = $userStore->create($name, $title);
          return implode("\t", Shapes::toArray($user));
        },
      );

  array_unshift($users, "id\tname\ttitle");
  $interact->showLine(implode(PHP_EOL, $users));

  return 0;
}

<<Route('post', '/user')>>
function createUserFromWeb(
  FactoryContainer $c,
  \HackPack\HackMini\Message\Request $req,
  \HackPack\HackMini\Message\Response $rsp,
): \HackPack\HackMini\Message\Response {
  $userStore = $c->getUserStore();
  $newUser = $userStore->create(
    $req->at('name', new AlphaNumeric()),
    $req->get('title', new AlphaNumeric()),
  );
  $template = $c->getNewUserPage();

  return $rsp->show($template->render($newUser));
}

<<Route('get', '/user/(\d+)'), UseMiddleware('RequireUser')>>
function showUser(
  FactoryContainer $c,
  \HackPack\HackMini\Message\Request $req,
  \HackPack\HackMini\Message\Response $rsp,
): \HackPack\HackMini\Message\Response {
  $me = $c->getAuth()->meOrThrow();
  $userStore = $c->getUserStore();
  $template = $c->getUserDetailPage();

  $loadedUser = $userStore->fromId((int) $req->pathGroup(1));

  if ($loadedUser === null) {
    return $rsp->show($template->missing());
  }

  if ($loadedUser['id'] === $me['id']) {
    return $rsp->show($template->privateProfile($me));
  }

  return $rsp->show($template->publicProfile($loadedUser));
}

<<Route('get', '/me'), UseMiddleware('RequireUser')>>
function showMyProfile(
  FactoryContainer $c,
  \HackPack\HackMini\Message\Request $req,
  \HackPack\HackMini\Message\Response $rsp,
): \HackPack\HackMini\Message\Response {
  return $rsp->show(
    $c->getUserDetailPage()->privateProfile($c->getAuth()->meOrThrow()),
  );
}
