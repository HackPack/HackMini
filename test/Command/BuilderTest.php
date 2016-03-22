<?hh // strict

namespace HackPack\HackMini\Test\Command;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackMini\Command\Builder;

class BuilderTest
{

    <<Test>>
    public function headAndFootAreRendered(Assert $assert) : void
    {
        $builder = new Builder(Vector{}, Map{});
        $result = $builder->render();

        $assert->string($result)->contains(Builder::HEAD);
        $result = str_replace(Builder::HEAD, '', $result);
        $assert->string($result)->contains(Builder::FOOT);
    }

    <<Test>>
    public function renderFunctionCommand(Assert $assert) : void
    {
        $builder = new Builder(
            Vector{
                shape(
                    'name' => 'functionCommand',
                    'middleware' => Vector{},
                    'function' => 'functionName',
                    'arguments' => Vector{},
                    'options' => Vector{},
                )
            },
            Map{},
        );

        $definition = <<<'Hack'
        'functionCommand' => shape(
            'arguments' => Vector{},
            'options' => Vector{},
            'handler' => fun('functionName'),
            'middleware' => Vector{},
        ),

Hack;
        $assert->string($builder->render())->contains($definition);
    }

    <<Test>>
    public function renderMethodCommand(Assert $assert) : void
    {
        $builder = new Builder(
            Vector{
                shape(
                    'name' => 'methodCommand',
                    'middleware' => Vector{},
                    'class' => 'ClassName',
                    'method' => 'methodName',
                    'arguments' => Vector{},
                    'options' => Vector{},
                )
            },
            Map{},
        );

        $definition = <<<'Hack'
        'methodCommand' => shape(
            'arguments' => Vector{},
            'options' => Vector{},
            'handler' => class_meth(ClassName::class, 'methodName'),
            'middleware' => Vector{},
        ),

Hack;
        $assert->string($builder->render())->contains($definition);
    }

    <<Test>>
    public function renderMiddleware(Assert $assert) : void
    {
        $builder = new Builder(
            Vector{
                shape(
                    'name' => 'stuff',
                    'middleware' => Vector{
                        'one',
                        'two',
                    },
                    'function' => 'stuff',
                    'arguments' => Vector{},
                    'options' => Vector{},
                ),
            },
            Map{
                'one' => 'fun(\'middlewareBuilder\')',
                'two' => 'class_meth(\'MiddlewareClass\', \'factory\')',
            }
        );

        $definition = <<<'Hack'
            'middleware' => Vector{
                fun('middlewareBuilder'),
                class_meth('MiddlewareClass', 'factory'),
            },

Hack;
        $assert->string($builder->render())->contains($definition);
    }

    <<Test>>
    public function renderArguments(Assert $assert) : void
    {
        $builder = new Builder(
            Vector{
                shape(
                    'name' => 'commandWithArguments',
                    'middleware' => Vector{},
                    'function' => 'f',
                    'arguments' => Vector{
                        shape(
                            'name' => 'one',
                        ),
                        shape(
                            'name' => 'two',
                            'default' => 'stuff',
                        ),
                    },
                    'options' => Vector{},
                ),
            },
            Map{},
        );

        $definition = <<<'Hack'
            'arguments' => Vector{
                shape(
                    'name' => 'one',
                ),
                shape(
                    'name' => 'two',
                    'default' => 'stuff',
                ),
            },

Hack;

        $assert->string($builder->render())->contains($definition);
    }

    <<Test>>
    public function renderOptions(Assert $assert) : void
    {
        $builder = new Builder(
            Vector{
                shape(
                    'name' => 'commandWithArguments',
                    'function' => 'f',
                    'arguments' => Vector{},
                    'middleware' => Vector{},
                    'options' => Vector{
                        shape(
                            'name' => 'one',
                            'alias' => 'o',
                            'value required' => true,
                        ),
                        shape(
                            'name' => 'two',
                            'value required' => false,
                            'default' => 'stuff',
                        ),
                    },
                ),
            },
            Map{},
        );

        $definition = <<<'Hack'
            'options' => Vector{
                shape(
                    'name' => 'one',
                    'alias' => 'o',
                    'value required' => true,
                ),
                shape(
                    'name' => 'two',
                    'value required' => false,
                    'default' => 'stuff',
                ),
            },

Hack;

        $assert->string($builder->render())->contains($definition);
    }

    <<Test>>
    public function noFunctionName(Assert $assert) : void
    {
        $builder = new Builder(
            Vector{
                shape(
                    'name' => '',
                    'middleware' => Vector{},
                    'arguments' => Vector{},
                    'options' => Vector{},
                ),
            },
            Map{},
        );

        $assert->whenCalled(() ==> {
            $builder->render();
        })->willThrowClass(\UnexpectedValueException::class);
    }

    <<Test>>
    public function missingMiddleware(Assert $assert) : void
    {
        $builder = new Builder(
            Vector{
                shape(
                    'name' => 'stuff',
                    'function' => 'stuff',
                    'middleware' => Vector{'a'},
                    'arguments' => Vector{},
                    'options' => Vector{},
                ),
            },
            Map{},
        );

        $assert->whenCalled(() ==> {
            $builder->render();
        })->willThrowClass(\UnexpectedValueException::class);
    }
}
