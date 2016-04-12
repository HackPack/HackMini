<?hh // strict

namespace HackPack\HackMini\Middleware;

type Next<Trequest, Tresponse, Tresult> = (function(Trequest, Tresponse): Tresult);
