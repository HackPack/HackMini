<?hh // strict

namespace HackPack\HackMini\Test\Command;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackMini\Command\Builder;

class BuilderTest
{

    <<Test>>
    public function headAndFootAreRendered(Assert $assert) : void
    {
        $builder = new Builder(Vector{});
        $result = $builder->render();

        $assert->string($result)->contains(Builder::HEAD);
        $result = str_replace(Builder::HEAD, '', $result);
        $assert->string($result)->contains(Builder::FOOT);
    }

    <<Test>>
    public function exceptionThrownWithNoFunctionName(Assert $assert) : void
    {
        $builder = new Builder(Vector{
            shape(
                'name' => '',
                'arguments' => Vector{},
                'options' => Vector{},
            ),
        });

        $assert->whenCalled(() ==> {
            $builder->render();
        })->willThrowClass(\UnexpectedValueException::class);
    }

    <<Test>>
    public function renderFunctionCommand(Assert $assert) : void
    {
        $builder = new Builder(Vector{
            shape(
                'name' => 'functionCommand',
                'function' => 'functionName',
                'arguments' => Vector{},
                'options' => Vector{},
            )
        });

        $definition = <<<'Hack'
        'functionCommand' => shape(
            'arguments' => Vector{},
            'options' => Vector{},
            'handler' => fun('functionName'),
        ),

Hack;
        $assert->string($builder->render())->contains($definition);
    }

    <<Test>>
    public function renderMethodCommand(Assert $assert) : void
    {
        $builder = new Builder(Vector{
            shape(
                'name' => 'methodCommand',
                'class' => 'ClassName',
                'method' => 'methodName',
                'arguments' => Vector{},
                'options' => Vector{},
            )
        });

        $definition = <<<'Hack'
        'methodCommand' => shape(
            'arguments' => Vector{},
            'options' => Vector{},
            'handler' => class_meth(ClassName::class, 'methodName'),
        ),

Hack;
        $assert->string($builder->render())->contains($definition);
    }

    <<Test>>
    public function renderArguments(Assert $assert) : void
    {
        $builder = new Builder(Vector{
            shape(
                'name' => 'commandWithArguments',
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
        });

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
        $builder = new Builder(Vector{
             shape(
                 'name' => 'commandWithArguments',
                 'function' => 'f',
                 'arguments' => Vector{},
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
        });

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
}
