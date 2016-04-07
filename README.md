# HackMini
Mini framework written in Hack

## Features

Being somewhere between a "micro" framework and a "bloated" framework, HackMini provides a balanced set of features
that eases the burden on developers desiring a flexible, maintainable, clean codebase.

### Type Safety

HackMini uses Hack's strict mode as much as possible, thus all of the framework components must be fully typed.  What this
means for you is that all of your code can be type safe, even when depending on HackMini's functions or classes.

Type safety is eminently important for code maintainability and flexibility.  When your code is type safe and you alter
the interface for any function or class, the type checker will tell you all the other places that must be updated to accommodate the new feature. It's like an entire class of integration tests already written for you!

### Router and Middleware Stack

HackMini makes heavy use of user defined attributes to allow for easy routing.  Attributes are also used
to define route specific middleware stacks.

#### Defining a Route Handler

Route handlers must either be plain functions or static class methods.  The route handler must be annotated
with the `<<Route('/path')>>` attribute.  The path attribute parameter must be unique application-wide.

```php
<?hh // strict

<<Route('/users')>>
function userListHandler(
  \FactoryContainer $c,
  \HackPack\HackMini\Message\Request $req,
  \HackPack\HackMini\Message\Response $rsp,
) : \HackPack\HackMini\Message\Response {
  return $rsp->show('Some HTML');
}
```

#### HTTP Message Abstraction (PSR-7... ish)

### Factory Container


### Command Line

## Non-Features

There are some features of other frameworks that are purposefully not implemented in HackMini. In the age of
Composer, we developers only need enough scaffolding to get the request to our code, and to get the response back
to the user.  For the stuff between, we have a plethora of tools available, and HackMini makes it easy to use
the tools most appropriate for your awesome idea.

### Magic

The driving goal of HackMini is clarity of code.  This is accomplished by using strict mode as much as possible
such that the framework code gives the Hack Typechecker as much information as possible.

If anything in the HackMini implementation is unclear or the code is poorly documented, please open an issue on GitHub.
Better yet, submit a pull request!

### Database Abstraction

There are many database abstraction solutions, and there is no reason for the scaffolding surrounding your application
to force any one particular way of interacting with databases.  HackMini deliberately eschews any database interactions,
giving you the freedom to use the solution you like!

### Templates

Just as there are many database solutions, there are many template engines, including XHP!  HackMini provides
the PSR-7 compliant request and response abstractions, making it easy to send whatever data you like to your users.

### Configuration

HackMini favors developer experience by minimizing the amount of configuration required.  The minimum is zero,
and that's what HackMini needs.

There are no configuration files specific to HackMini.  The web server may need some configuration, system dependencies
may need to be installed, but HackMini just does what it does best... makes writing clean code in your style easy.
