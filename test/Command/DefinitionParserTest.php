<?hh // strict

namespace HackPack\HackMini\Test\Command;

use FredEmmott\DefinitionFinder\FileParser;
use FredEmmott\DefinitionFinder\ScannedFunctionAbstract;
use HackPack\HackUnit\Contract\Assert;
use HackPack\HackMini\Command\DefinitionParser;
use HackPack\HackMini\Command\OptionDefinition;
use HackPack\HackMini\Command\ArgumentDefinition;

class DefinitionParserTest
{
    <<Test>>
    public function repeatedArgumentOptionNames(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('simple'), Arguments('name'), Options('name')>>
function simplehandler(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;

        $parser = $this->parse($code);

        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function repeatedArgumentNames(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('repeated-arguments'), Arguments('name', 'name')>>
function simplehandler(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);

        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function repeatedOptionAliases(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('simple'), Options('n', 'n|name')>>
function simplehandler(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);

        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function repeatedOptionNames(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('simple'), Options('name', 'name')>>
function simplehandler(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);

        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function ignoreNonCommands(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
function notacommand(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }

function notacommandtoo() : string { }

class Stuff {

    public static function nocommands(
        FactoryContainer $c,
        HackPack\HackMini\Command\Request $r,
        HackPack\HackMini\Command\UserInteraction $i,
    ) : int { }

    public static function nocommandshere() : int { }
}

Hack;
        $parser = $this->parse($code);

        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(0);
    }

    <<Test>>
    public function parseSimpleFunction(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('simple')>>
function simplehandler(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);

        $assert->int($parser->failures()->count())->eq(0);

        $commands = $parser->commands();
        $assert->int($commands->count())->eq(1);

        $command = $commands->at(0);
        $assert->string($command['name'])->is('simple');
        $assert->string(Shapes::idx($command, 'function', ''))->is('simplehandler');
        $assert->mixed(Shapes::idx($command, 'class'))->isNull();
        $assert->mixed(Shapes::idx($command, 'method'))->isNull();
        $assert->int($command['arguments']->count())->eq(0);
        $assert->int($command['options']->count())->eq(0);
    }

    <<Test>>
    public function parseSimpleMethod(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
class CommandClass {
    <<Command('simple')>>
    public static function simplehandler(
        FactoryContainer $c,
        HackPack\HackMini\Command\Request $r,
        HackPack\HackMini\Command\UserInteraction $i,
    ) : int { }
}
Hack;

        $parser = $this->parse($code);

        $assert->int($parser->failures()->count())->eq(0);

        $commands = $parser->commands();
        $assert->int($commands->count())->eq(1);

        $command = $commands->at(0);
        $assert->string($command['name'])->is('simple');
        $assert->mixed(Shapes::idx($command, 'function'))->isNull();
        $assert->string(Shapes::idx($command, 'class', ''))->is('CommandClass');
        $assert->string(Shapes::idx($command, 'method', ''))->is('simplehandler');
        $assert->int($command['arguments']->count())->eq(0);
        $assert->int($command['options']->count())->eq(0);
    }

    <<Test>>
    public function parseSimpleOption(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
class CommandClass {
    <<Command('complex'), Options('simple')>>
    public static function complexHandler(
        FactoryContainer $c,
        HackPack\HackMini\Command\Request $r,
        HackPack\HackMini\Command\UserInteraction $i,
    ) : int { }
}
Hack;
        $parser = $this->parse($code);

        $assert->int($parser->failures()->count())->eq(0);

        $options = $parser->commands()->at(0)['options'];
        $assert->int($options->count())->eq(1);

        $this->checkOption(
            $assert,
            $options->at(0),
            shape(
                'name' => 'simple',
                'value required' => false,
            ),
        );
    }

    <<Test>>
    public function parseOptionWithAlias(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
class CommandClass {
    <<Command('complex'), Options('s|simple')>>
    public static function complexHandler(
        FactoryContainer $c,
        HackPack\HackMini\Command\Request $r,
        HackPack\HackMini\Command\UserInteraction $i,
    ) : int { }
}
Hack;
        $parser = $this->parse($code);

        $assert->int($parser->failures()->count())->eq(0);

        $options = $parser->commands()->at(0)['options'];
        $assert->int($options->count())->eq(1);

        $this->checkOption(
            $assert,
            $options->at(0),
            shape(
                'name' => 'simple',
                'alias' => 's',
                'value required' => false,
            ),
        );
    }

    <<Test>>
    public function parseOptionWithRequiredValue(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
class CommandClass {
    <<Command('complex'), Options('simple=')>>
    public static function complexHandler(
        FactoryContainer $c,
        HackPack\HackMini\Command\Request $r,
        HackPack\HackMini\Command\UserInteraction $i,
    ) : int { }
}
Hack;
        $parser = $this->parse($code);

        $assert->int($parser->failures()->count())->eq(0);

        $options = $parser->commands()->at(0)['options'];
        $assert->int($options->count())->eq(1);

        $this->checkOption(
            $assert,
            $options->at(0),
            shape(
                'name' => 'simple',
                'value required' => true,
            ),
        );
    }

    <<Test>>
    public function parseOptionWithDefaultValue(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
class CommandClass {
    <<Command('complex'), Options('simple=va l|u=e')>>
    public static function complexHandler(
        FactoryContainer $c,
        HackPack\HackMini\Command\Request $r,
        HackPack\HackMini\Command\UserInteraction $i,
    ) : int { }
}
Hack;
        $parser = $this->parse($code);

        $assert->int($parser->failures()->count())->eq(0);

        $options = $parser->commands()->at(0)['options'];
        $assert->int($options->count())->eq(1);

        $this->checkOption(
            $assert,
            $options->at(0),
            shape(
                'name' => 'simple',
                'value required' => true,
                'default' => 'va l|u=e',
            ),
        );
    }

    <<Test>>
    public function commandMustHaveName(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command>>
function nonamecommand(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function parameterCountCheck(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('toomany')>>
function toomany(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
    int $more,
) : int { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function paramsMustBePresent(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('noparams')>>
function noparams() : int {}
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function firstParamCheck(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('wrongfirst')>>
function wrong(
    int $wrong,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function secondParamCheck(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('wrongsecond')>>
function wrong(
    FactoryContainer $c,
    int $wrong,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function thirdParamCheck(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('wrongthird')>>
function wrong(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    int $wrong,
) : int { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function returnTypeCheck(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('simple')>>
function simplehandler(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : void { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function extraOptionalParamsAllowed(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('toomany')>>
function toomany(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
    int $more = 0,
) : int { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(1);
        $assert->int($parser->failures()->count())->eq(0);
    }

    <<Test>>
    public function optionWithPipe(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('complex'), Options('b|aa|d=')>>
function complexhandler(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function optionWithSpace(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('complex'), Options('b|b ad =')>>
function complexhandler(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function optionWithLongAlias(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('complex'), Options('ba|ad')>>
function complexhandler(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function nonStaticMethod(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
class CommandClass {
    <<Command('simple')>>
    public function simplehandler(
        FactoryContainer $c,
        HackPack\HackMini\Command\Request $r,
        HackPack\HackMini\Command\UserInteraction $i,
    ) : int { }
}
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function commandNameWithPipe(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('s|imple')>>
function simplehandler(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function commandNameWithSpace(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('simpl e')>>
function simplehandler(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function commandNameWithEqualSign(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('simpl=e')>>
function simplehandler(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function argumentsWithDefaults(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('complex'), Arguments('one', 'two=default', 'three=three')>>
function complexhandler(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(1);
        $assert->int($parser->failures()->count())->eq(0);

        $arguments = $parser->commands()->at(0)['arguments'];
        $assert->int($arguments->count())->eq(3);

        $this->checkArgument(
            $assert,
            $arguments->at(0),
            shape(
                'name' => 'one',
            ),
        );
        $this->checkArgument(
            $assert,
            $arguments->at(1),
            shape(
                'name' => 'two',
                'default' => 'default',
            ),
        );
        $this->checkArgument(
            $assert,
            $arguments->at(2),
            shape(
                'name' => 'three',
                'default' => 'three',
            ),
        );
    }

    <<Test>>
    public function argumentsWithInvalidDefaults(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('complex'), Arguments('one=default', 'two', 'three=three')>>
function complexhandler(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    <<Test>>
    public function wrongArgumentNameType(Assert $assert) : void
    {
        $code = <<<'Hack'
<?hh
<<Command('complex'), Arguments(1, 'two')>>
function complexhandler(
    FactoryContainer $c,
    HackPack\HackMini\Command\Request $r,
    HackPack\HackMini\Command\UserInteraction $i,
) : int { }
Hack;
        $parser = $this->parse($code);
        $assert->int($parser->commands()->count())->eq(0);
        $assert->int($parser->failures()->count())->eq(1);
    }

    private function parse(string $code) : DefinitionParser
    {
        $fileParser = FileParser::FromData($code);
        $functions = $fileParser->getFunctions();
        $classes = $fileParser->getClasses();
        return new DefinitionParser($functions, $classes);
    }

    private function checkOption(
        Assert $assert,
        OptionDefinition $actual,
        OptionDefinition $expected,
    ) : void
    {
        $assert->string($actual['name'])->is($expected['name']);
        $assert
            ->mixed(Shapes::idx($actual, 'default'))
            ->identicalTo(Shapes::idx($expected, 'default'))
            ;
        $assert
            ->mixed(Shapes::idx($actual, 'alias'))
            ->identicalTo(Shapes::idx($expected, 'alias'))
            ;
        $assert->bool($actual['value required'])->is($expected['value required']);
    }

    private function checkArgument(
        Assert $assert,
        ArgumentDefinition $actual,
        ArgumentDefinition $expected,
    ) : void
    {
        $assert->string($actual['name'])->is($expected['name']);
        $assert
            ->mixed(Shapes::idx($actual, 'default'))
            ->identicalTo(Shapes::idx($expected, 'default'))
            ;
    }
}
