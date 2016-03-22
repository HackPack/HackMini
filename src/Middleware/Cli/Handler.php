<?hh // strict

namespace HackPack\HackMini\Middleware\Cli;

use FactoryContainer;
use HackPack\HackMini\Command\Request;
use HackPack\HackMini\Command\UserInteraction;

type Handler = (function(FactoryContainer, Request, UserInteraction) : int);
