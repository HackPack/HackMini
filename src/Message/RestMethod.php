<?hh // strict

namespace HackPack\HackMini\Message;

enum RestMethod : string
{
    Delete = 'DELETE';
    Get = 'GET';
    Head = 'HEAD';
    Options = 'OPTIONS';
    Patch = 'PATCH';
    Post = 'POST';
    Put = 'PUT';

    // These two are used as catch-alls for route definition/consumption
    Any = 'ANY';
    Unknown = 'UNKNOWN';
}
