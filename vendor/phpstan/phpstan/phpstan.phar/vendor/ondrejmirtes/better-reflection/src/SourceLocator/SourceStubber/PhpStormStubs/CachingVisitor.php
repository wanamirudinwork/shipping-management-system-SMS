<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubs;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PHPStan\BetterReflection\Reflection\Exception\InvalidConstantNode;
use PHPStan\BetterReflection\Util\ConstantNodeChecker;
use function array_key_exists;
use function assert;
use function class_exists;
use function constant;
use function count;
use function defined;
use function explode;
use function in_array;
use function is_resource;
use function sprintf;
use function strtolower;
use function strtoupper;
/** @internal */
class CachingVisitor extends NodeVisitorAbstract
{
    private const TRUE_FALSE_NULL = ['true', 'false', 'null'];
    /**
     * @var \PhpParser\Node\Stmt\Namespace_|null
     */
    private $currentNamespace = null;
    /** @var array<string, array{0: Node\Stmt\ClassLike, 1: Node\Stmt\Namespace_|null}> */
    private $classNodes = [];
    /** @var array<string, list<array{0: Node\Stmt\Function_, 1: Node\Stmt\Namespace_|null}>> */
    private $functionNodes = [];
    /** @var array<string, array{0: Node\Stmt\Const_|Node\Expr\FuncCall, 1: Node\Stmt\Namespace_|null}> */
    private $constantNodes = [];
    /**
     * @var \PhpParser\BuilderFactory
     */
    private $builderFactory;
    public function __construct(BuilderFactory $builderFactory)
    {
        $this->builderFactory = $builderFactory;
    }
    public function enterNode(Node $node) : ?int
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->currentNamespace = $node;
            return null;
        }
        if ($node instanceof Node\Stmt\ClassLike) {
            $classNamespacedName = $node->namespacedName;
            assert($classNamespacedName instanceof Node\Name);
            $className = $classNamespacedName->toString();
            $this->classNodes[$className] = [$node, $this->currentNamespace];
            foreach ($node->getConstants() as $constantsNode) {
                foreach ($constantsNode->consts as $constNode) {
                    $constClassName = sprintf('%s::%s', $className, $constNode->name->toString());
                    $this->updateConstantValue($constNode, $constClassName);
                }
            }
            // We need to traverse children to resolve attributes names for methods, properties etc.
            return null;
        }
        if ($node instanceof Node\Stmt\ClassMethod) {
            return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        }
        if ($node instanceof Node\Stmt\Function_) {
            $functionNamespacedName = $node->namespacedName;
            assert($functionNamespacedName instanceof Node\Name);
            $functionName = $functionNamespacedName->toString();
            $this->functionNodes[$functionName][] = [$node, $this->currentNamespace];
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
        if ($node instanceof Node\Stmt\Const_) {
            foreach ($node->consts as $constNode) {
                $constNamespacedName = $constNode->namespacedName;
                assert($constNamespacedName instanceof Node\Name);
                $constNodeName = $constNamespacedName->toString();
                $this->updateConstantValue($constNode, $constNodeName);
                $this->constantNodes[$constNodeName] = [$node, $this->currentNamespace];
            }
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
        if ($node instanceof Node\Expr\FuncCall) {
            $argumentNameNode = $node->args[0];
            assert($argumentNameNode instanceof Node\Arg);
            $nameNode = $argumentNameNode->value;
            assert($nameNode instanceof Node\Scalar\String_);
            $constantName = $nameNode->value;
            // The definition is stubs looks like `define('STDIN', fopen('php://stdin', 'r'))`
            // We will modify it to `define('STDIN', constant('STDIN'));
            // The later definition can pass validation in `ConstantNodeChecker` and has support in `CompileNodeToValue`
            if (in_array($constantName, ['STDIN', 'STDOUT', 'STDERR'], \true) && array_key_exists(1, $node->args) && $node->args[1] instanceof Node\Arg) {
                $node->args[1]->value = $this->builderFactory->funcCall('constant', [$constantName]);
            }
            // @codeCoverageIgnoreStart
            // @infection-ignore-all
            // No invalid definition in PhpStorm stubs
            try {
                ConstantNodeChecker::assertValidDefineFunctionCall($node);
            } catch (InvalidConstantNode $exception) {
                return null;
            }
            // @codeCoverageIgnoreEnd
            if (in_array($constantName, self::TRUE_FALSE_NULL, \true)) {
                $constantName = strtoupper($constantName);
                $nameNode->value = $constantName;
            }
            $this->updateConstantValue($node, $constantName);
            $this->constantNodes[$constantName] = [$node, $this->currentNamespace];
            if (array_key_exists(2, $node->args) && $node->args[2] instanceof Node\Arg && $node->args[2]->value instanceof Node\Expr\ConstFetch && $node->args[2]->value->name->toLowerString() === 'true') {
                $this->constantNodes[strtolower($constantName)] = [$node, $this->currentNamespace];
            }
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
        return null;
    }
    /**
     * {@inheritDoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->currentNamespace = null;
        }
        return null;
    }
    /** @return array<string, array{0: Node\Stmt\ClassLike, 1: Node\Stmt\Namespace_|null}> */
    public function getClassNodes() : array
    {
        return $this->classNodes;
    }
    /** @return array<string, list<array{0: Node\Stmt\Function_, 1: Node\Stmt\Namespace_|null}>> */
    public function getFunctionNodes() : array
    {
        return $this->functionNodes;
    }
    /** @return array<string, array{0: Node\Stmt\Const_|Node\Expr\FuncCall, 1: Node\Stmt\Namespace_|null}> */
    public function getConstantNodes() : array
    {
        return $this->constantNodes;
    }
    public function clearNodes() : void
    {
        $this->classNodes = [];
        $this->functionNodes = [];
        $this->constantNodes = [];
    }
    /**
     * Some constants have different values on different systems, some are not actual in stubs.
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Const_ $node
     */
    private function updateConstantValue($node, string $constantName) : void
    {
        // prevent autoloading while discovering class constants
        $parts = explode('::', $constantName, 2);
        if (count($parts) === 2) {
            [$className, $classConstName] = $parts;
            if (!class_exists($className, \false)) {
                return;
            }
        }
        if (!defined($constantName)) {
            return;
        }
        // @ because access to deprecated constant throws deprecated warning
        /** @var scalar|resource|list<scalar>|null $constantValue */
        $constantValue = @constant($constantName);
        $normalizedConstantValue = is_resource($constantValue) ? $this->builderFactory->funcCall('constant', [$constantName]) : $this->builderFactory->val($constantValue);
        if ($node instanceof Node\Expr\FuncCall) {
            $argumentValueNode = $node->args[1];
            assert($argumentValueNode instanceof Node\Arg);
            $argumentValueNode->value = $normalizedConstantValue;
        } else {
            $node->value = $normalizedConstantValue;
        }
    }
}
