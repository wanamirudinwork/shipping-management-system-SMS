<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\SourceStubber;

use CompileError;
use DatePeriod;
use Error;
use Generator;
use Iterator;
use IteratorAggregate;
use _PHPStan_14faee166\JetBrains\PHPStormStub\PhpStormStubsMap;
use JsonSerializable;
use ParseError;
use PDOStatement;
use PhpParser\BuilderFactory;
use PhpParser\BuilderHelpers;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use RecursiveIterator;
use PHPStan\BetterReflection\Reflection\Annotation\AnnotationHelper;
use PHPStan\BetterReflection\Reflection\Exception\InvalidConstantNode;
use PHPStan\BetterReflection\SourceLocator\FileChecker;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\Exception\CouldNotFindPhpStormStubs;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubs\CachingVisitor;
use PHPStan\BetterReflection\Util\ConstantNodeChecker;
use SeekableIterator;
use SimpleXMLElement;
use SplFixedArray;
use SplObjectStorage;
use Traversable;
use function array_change_key_case;
use function array_key_exists;
use function array_map;
use function assert;
use function explode;
use function file_get_contents;
use function in_array;
use function is_dir;
use function preg_match;
use function preg_replace;
use function sprintf;
use function str_contains;
use function str_replace;
use function strtolower;
use function usort;
use const PHP_VERSION_ID;
/** @internal */
final class PhpStormStubsSourceStubber implements \PHPStan\BetterReflection\SourceLocator\SourceStubber\SourceStubber
{
    private const SEARCH_DIRECTORIES = [__DIR__ . '/../../../../../jetbrains/phpstorm-stubs', __DIR__ . '/../../../vendor/jetbrains/phpstorm-stubs'];
    private const CORE_EXTENSIONS = ['apache', 'bcmath', 'bz2', 'calendar', 'Core', 'ctype', 'curl', 'date', 'dba', 'dom', 'enchant', 'exif', 'FFI', 'fileinfo', 'filter', 'fpm', 'ftp', 'gd', 'gettext', 'gmp', 'hash', 'iconv', 'imap', 'interbase', 'intl', 'json', 'ldap', 'libxml', 'mbstring', 'mcrypt', 'mssql', 'mysql', 'mysqli', 'oci8', 'odbc', 'openssl', 'pcntl', 'pcre', 'PDO', 'pdo_ibm', 'pdo_mysql', 'pdo_pgsql', 'pdo_sqlite', 'pgsql', 'Phar', 'posix', 'pspell', 'random', 'readline', 'recode', 'Reflection', 'regex', 'session', 'shmop', 'SimpleXML', 'snmp', 'soap', 'sockets', 'sodium', 'SPL', 'sqlite3', 'standard', 'sybase', 'sysvmsg', 'sysvsem', 'sysvshm', 'tidy', 'tokenizer', 'wddx', 'xml', 'xmlreader', 'xmlrpc', 'xmlwriter', 'xsl', 'Zend OPcache', 'zip', 'zlib'];
    /**
     * @var \PhpParser\BuilderFactory
     */
    private $builderFactory;
    /**
     * @var \PhpParser\PrettyPrinter\Standard
     */
    private $prettyPrinter;
    /**
     * @var \PhpParser\NodeTraverser
     */
    private $nodeTraverser;
    /**
     * @var string|null
     */
    private $stubsDirectory = null;
    /**
     * @var \PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubs\CachingVisitor
     */
    private $cachingVisitor;
    /**
     * `null` means "class is not supported in the required PHP version"
     *
     * @var array<string, array{0: Node\Stmt\ClassLike, 1: Node\Stmt\Namespace_|null}|null>
     */
    private $classNodes = [];
    /**
     * `null` means "function is not supported in the required PHP version"
     *
     * @var array<string, array{0: Node\Stmt\Function_, 1: Node\Stmt\Namespace_|null}|null>
     */
    private $functionNodes = [];
    /**
     * `null` means "failed lookup" for constant that is not case insensitive or "constant is not supported in the required PHP version"
     *
     * @var array<string, array{0: Node\Stmt\Const_|Node\Expr\FuncCall, 1: Node\Stmt\Namespace_|null}|null>
     */
    private $constantNodes = [];
    /**
     * @var bool
     */
    private static $mapsInitialized = \false;
    /** @var array<lowercase-string, string> */
    private static $classMap;
    /** @var array<lowercase-string, string> */
    private static $functionMap;
    /** @var array<lowercase-string, string> */
    private static $constantMap;
    /**
     * @var \PhpParser\Parser
     */
    private $phpParser;
    /**
     * @var int
     */
    private $phpVersion = PHP_VERSION_ID;
    public function __construct(Parser $phpParser, Standard $prettyPrinter, int $phpVersion = PHP_VERSION_ID)
    {
        $this->phpParser = $phpParser;
        $this->phpVersion = $phpVersion;
        $this->builderFactory = new BuilderFactory();
        $this->prettyPrinter = $prettyPrinter;
        $this->cachingVisitor = new CachingVisitor($this->builderFactory);
        $this->nodeTraverser = new NodeTraverser();
        $this->nodeTraverser->addVisitor(new NameResolver());
        $this->nodeTraverser->addVisitor($this->cachingVisitor);
        if (self::$mapsInitialized) {
            return;
        }
        /** @psalm-suppress PropertyTypeCoercion */
        self::$classMap = array_change_key_case(PhpStormStubsMap::CLASSES);
        /** @psalm-suppress PropertyTypeCoercion */
        self::$functionMap = array_change_key_case(PhpStormStubsMap::FUNCTIONS);
        /** @psalm-suppress PropertyTypeCoercion */
        self::$constantMap = array_change_key_case(PhpStormStubsMap::CONSTANTS);
        self::$mapsInitialized = \true;
    }
    public function hasClass(string $className) : bool
    {
        $lowercaseClassName = strtolower($className);
        return array_key_exists($lowercaseClassName, self::$classMap);
    }
    public function isPresentClass(string $className) : ?bool
    {
        $lowercaseClassName = strtolower($className);
        if (!array_key_exists($lowercaseClassName, self::$classMap)) {
            return null;
        }
        $classNode = $this->getClassNodeData($lowercaseClassName);
        return $classNode !== null;
    }
    public function isPresentFunction(string $functionName) : ?bool
    {
        $lowercaseFunctionName = strtolower($functionName);
        if (!array_key_exists($lowercaseFunctionName, self::$functionMap)) {
            return null;
        }
        $functionNode = $this->getFunctionNodeData($lowercaseFunctionName);
        return $functionNode !== null;
    }
    /** @param class-string|trait-string $className */
    public function generateClassStub(string $className) : ?\PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData
    {
        if (strtolower($className) === 'iterable') {
            return null;
        }
        $classNodeData = $this->getClassNodeData($className);
        if ($classNodeData === null) {
            return null;
        }
        $classNode = $classNodeData[0];
        if ($classNode instanceof Node\Stmt\Class_) {
            if ($classNode->extends !== null) {
                $modifiedExtends = $this->replaceExtendsOrImplementsByPhpVersion($className, [$classNode->extends]);
                $classNode->extends = $modifiedExtends !== [] ? $modifiedExtends[0] : null;
            }
            $classNode->implements = $this->replaceExtendsOrImplementsByPhpVersion($className, $classNode->implements);
        } elseif ($classNode instanceof Node\Stmt\Interface_) {
            $classNode->extends = $this->replaceExtendsOrImplementsByPhpVersion($className, $classNode->extends);
        }
        $filePath = self::$classMap[strtolower($className)];
        $extension = $this->getExtensionFromFilePath($filePath);
        $stub = $this->createStub($classNode, $classNodeData[1]);
        if ($className === Traversable::class) {
            // See https://github.com/JetBrains/phpstorm-stubs/commit/0778a26992c47d7dbee4d0b0bfb7fad4344371b1#diff-575bacb45377d474336c71cbf53c1729
            $stub = str_replace(' extends \\iterable', '', $stub);
        } elseif ($className === Generator::class) {
            $stub = str_replace('PS_UNRESERVE_PREFIX_throw', 'throw', $stub);
        }
        return new \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData($stub, $extension, $this->getAbsoluteFilePath($filePath));
    }
    /** @return array{0: Node\Stmt\ClassLike, 1: Node\Stmt\Namespace_|null}|null */
    private function getClassNodeData(string $className)
    {
        $lowercaseClassName = strtolower($className);
        if (!array_key_exists($lowercaseClassName, self::$classMap)) {
            return null;
        }
        $filePath = self::$classMap[$lowercaseClassName];
        if (!array_key_exists($lowercaseClassName, $this->classNodes)) {
            $this->parseFile($filePath);
            /** @psalm-suppress RedundantCondition */
            if (!array_key_exists($lowercaseClassName, $this->classNodes)) {
                // Save `null` so we don't parse the file again for the same $lowercaseClassName
                $this->classNodes[$lowercaseClassName] = null;
            }
        }
        return $this->classNodes[$lowercaseClassName];
    }
    /**
     * @return array{0: Node\Stmt\Function_, 1: Node\Stmt\Namespace_|null}|null
     */
    private function getFunctionNodeData(string $functionName) : ?array
    {
        $lowercaseFunctionName = strtolower($functionName);
        if (!array_key_exists($lowercaseFunctionName, self::$functionMap)) {
            return null;
        }
        $filePath = self::$functionMap[$lowercaseFunctionName];
        if (!array_key_exists($lowercaseFunctionName, $this->functionNodes)) {
            $this->parseFile($filePath);
            /** @psalm-suppress RedundantCondition */
            if (!array_key_exists($lowercaseFunctionName, $this->functionNodes)) {
                // Save `null` so we don't parse the file again for the same $lowercaseFunctionName
                $this->functionNodes[$lowercaseFunctionName] = null;
            }
        }
        return $this->functionNodes[$lowercaseFunctionName];
    }
    public function generateFunctionStub(string $functionName) : ?\PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData
    {
        $functionNodeData = $this->getFunctionNodeData($functionName);
        if ($functionNodeData === null) {
            return null;
        }
        $filePath = self::$functionMap[strtolower($functionName)];
        $extension = $this->getExtensionFromFilePath($filePath);
        return new \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData($this->createStub($functionNodeData[0], $functionNodeData[1]), $extension, $this->getAbsoluteFilePath($filePath));
    }
    public function generateConstantStub(string $constantName) : ?\PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData
    {
        $lowercaseConstantName = strtolower($constantName);
        if (!array_key_exists($lowercaseConstantName, self::$constantMap)) {
            return null;
        }
        if (array_key_exists($lowercaseConstantName, $this->constantNodes) && $this->constantNodes[$lowercaseConstantName] === null) {
            return null;
        }
        $filePath = self::$constantMap[$lowercaseConstantName];
        $constantNodeData = $this->constantNodes[$constantName] ?? $this->constantNodes[$lowercaseConstantName] ?? null;
        if ($constantNodeData === null) {
            $this->parseFile($filePath);
            $constantNodeData = $this->constantNodes[$constantName] ?? $this->constantNodes[$lowercaseConstantName] ?? null;
            if ($constantNodeData === null) {
                // Still `null` - the constant is not case-insensitive. Save `null` so we don't parse the file again for the same $constantName
                $this->constantNodes[$lowercaseConstantName] = null;
                return null;
            }
        }
        $extension = $this->getExtensionFromFilePath($filePath);
        return new \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData($this->createStub($constantNodeData[0], $constantNodeData[1]), $extension, $this->getAbsoluteFilePath($filePath));
    }
    private function parseFile(string $filePath) : void
    {
        $absoluteFilePath = $this->getAbsoluteFilePath($filePath);
        FileChecker::assertReadableFile($absoluteFilePath);
        /** @var list<Node\Stmt> $ast */
        $ast = $this->phpParser->parse(file_get_contents($absoluteFilePath));
        // "@since" and "@removed" annotations in some cases do not contain a PHP version, but an extension version - e.g. "@since 1.3.0"
        // So we check PHP version only for stubs of core extensions
        $isCoreExtension = $this->isCoreExtension($this->getExtensionFromFilePath($filePath));
        $this->cachingVisitor->clearNodes();
        $this->nodeTraverser->traverse($ast);
        foreach ($this->cachingVisitor->getClassNodes() as $className => $classNodeData) {
            [$classNode] = $classNodeData;
            if ($isCoreExtension) {
                if ($className !== 'Attribute' && $className !== 'ReturnTypeWillChange' && $className !== 'AllowDynamicProperties' && $className !== 'SensitiveParameter' && $className !== 'Override' && !$this->isSupportedInPhpVersion($classNode)) {
                    continue;
                }
                $classNode->stmts = $this->modifyStmtsByPhpVersion($classNode->stmts);
            }
            $this->classNodes[strtolower($className)] = $classNodeData;
        }
        foreach ($this->cachingVisitor->getFunctionNodes() as $functionName => $functionNodesData) {
            foreach ($functionNodesData as $functionNodeData) {
                [$functionNode] = $functionNodeData;
                if ($isCoreExtension) {
                    if (!$this->isSupportedInPhpVersion($functionNode)) {
                        continue;
                    }
                    $this->modifyFunctionReturnTypeByPhpVersion($functionNode);
                    $this->modifyFunctionParametersByPhpVersion($functionNode);
                }
                $lowercaseFunctionName = strtolower($functionName);
                if (array_key_exists($lowercaseFunctionName, $this->functionNodes)) {
                    continue;
                }
                $this->functionNodes[$lowercaseFunctionName] = $functionNodeData;
            }
        }
        foreach ($this->cachingVisitor->getConstantNodes() as $constantName => $constantNodeData) {
            [$constantNode] = $constantNodeData;
            if ($isCoreExtension && !$this->isSupportedInPhpVersion($constantNode)) {
                continue;
            }
            $this->constantNodes[$constantName] = $constantNodeData;
        }
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\Const_|\PhpParser\Node\Expr\FuncCall $node
     */
    private function createStub($node, ?\PhpParser\Node\Stmt\Namespace_ $namespaceNode) : string
    {
        if ($node instanceof Node\Expr\FuncCall) {
            try {
                ConstantNodeChecker::assertValidDefineFunctionCall($node);
                $this->addDeprecatedDocComment($node);
            } catch (InvalidConstantNode $exception) {
                // just keep going
            }
        }
        if (!$node instanceof Node\Expr\FuncCall) {
            $this->addDeprecatedDocComment($node);
            $nodeWithNamespaceName = $node instanceof Node\Stmt\Const_ ? $node->consts[0] : $node;
            $namespacedName = $nodeWithNamespaceName->namespacedName;
            assert($namespacedName instanceof Node\Name);
            $namespaceBuilder = $this->builderFactory->namespace($namespacedName->slice(0, -1));
            if ($namespaceNode !== null) {
                foreach ($namespaceNode->stmts as $stmt) {
                    if (!$stmt instanceof Node\Stmt\Use_ && !$stmt instanceof Node\Stmt\GroupUse) {
                        continue;
                    }
                    $namespaceBuilder->addStmt($stmt);
                }
            }
            $namespaceBuilder->addStmt($node);
            $node = $namespaceBuilder->getNode();
        }
        return sprintf("<?php\n\n%s%s\n", $this->prettyPrinter->prettyPrint([$node]), $node instanceof Node\Expr\FuncCall ? ';' : '');
    }
    /** @return non-empty-string */
    private function getExtensionFromFilePath(string $filePath) : string
    {
        $extensionName = explode('/', $filePath)[0];
        assert($extensionName !== '');
        return $extensionName;
    }
    /** @return non-empty-string */
    private function getAbsoluteFilePath(string $filePath) : string
    {
        return sprintf('%s/%s', $this->getStubsDirectory(), $filePath);
    }
    /**
     * Some stubs extend/implement classes from newer PHP versions. We need to filter those names in regard to set PHP version so that those stubs remain valid.
     *
     * @param array<Node\Name> $nameNodes
     *
     * @return list<Node\Name>
     */
    private function replaceExtendsOrImplementsByPhpVersion(string $className, array $nameNodes) : array
    {
        $modifiedNames = [];
        foreach ($nameNodes as $nameNode) {
            $name = $nameNode->toString();
            if ($className === ParseError::class) {
                if ($name === CompileError::class && $this->phpVersion < 70300) {
                    $modifiedNames[] = new Node\Name\FullyQualified(Error::class);
                    continue;
                }
            } elseif ($className === SplFixedArray::class) {
                if ($name === JsonSerializable::class && $this->phpVersion < 80100) {
                    continue;
                }
                if ($name === IteratorAggregate::class && $this->phpVersion < 80000) {
                    continue;
                }
                if ($name === Iterator::class && $this->phpVersion >= 80000) {
                    continue;
                }
            } elseif ($className === SimpleXMLElement::class) {
                if ($name === RecursiveIterator::class && $this->phpVersion < 80000) {
                    continue;
                }
            } elseif ($className === DatePeriod::class || $className === PDOStatement::class) {
                if ($name === IteratorAggregate::class && $this->phpVersion < 80000) {
                    $modifiedNames[] = new Node\Name\FullyQualified(Traversable::class);
                    continue;
                }
            } elseif ($className === SplObjectStorage::class) {
                if ($name === SeekableIterator::class && $this->phpVersion < 80400) {
                    $modifiedNames[] = new Node\Name\FullyQualified(Iterator::class);
                    continue;
                }
            }
            if ($this->getClassNodeData($name) === null) {
                continue;
            }
            $modifiedNames[] = $nameNode;
        }
        return $modifiedNames;
    }
    /**
     * @param array<Node\Stmt> $stmts
     *
     * @return list<Node\Stmt>
     */
    private function modifyStmtsByPhpVersion(array $stmts) : array
    {
        $newStmts = [];
        foreach ($stmts as $stmt) {
            assert($stmt instanceof Node\Stmt\ClassConst || $stmt instanceof Node\Stmt\Property || $stmt instanceof Node\Stmt\ClassMethod || $stmt instanceof Node\Stmt\EnumCase);
            if (!$this->isSupportedInPhpVersion($stmt)) {
                continue;
            }
            if ($stmt instanceof Node\Stmt\Property) {
                $this->modifyStmtTypeByPhpVersion($stmt);
            }
            if ($stmt instanceof Node\Stmt\ClassMethod) {
                $this->modifyFunctionReturnTypeByPhpVersion($stmt);
                $this->modifyFunctionParametersByPhpVersion($stmt);
            }
            $this->addDeprecatedDocComment($stmt);
            $newStmts[] = $stmt;
        }
        return $newStmts;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $stmt
     */
    private function modifyStmtTypeByPhpVersion($stmt) : void
    {
        $type = $this->getStmtType($stmt);
        if ($type === null) {
            return;
        }
        $stmt->type = $type;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $function
     */
    private function modifyFunctionReturnTypeByPhpVersion($function) : void
    {
        $isTentativeReturnType = $this->getNodeAttribute($function, 'JetBrains\\PhpStorm\\Internal\\TentativeType') !== null;
        if ($isTentativeReturnType) {
            // Tentative types are the most correct in stubs
            // If the type is tentative in stubs, we should remove the type for PHP < 8.1
            if ($this->phpVersion >= 80100) {
                $this->addAnnotationToDocComment($function, AnnotationHelper::TENTATIVE_RETURN_TYPE_ANNOTATION);
            } else {
                $function->returnType = null;
            }
            return;
        }
        $type = $this->getStmtType($function);
        if ($type === null) {
            return;
        }
        $function->returnType = $type;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $function
     */
    private function modifyFunctionParametersByPhpVersion($function) : void
    {
        $parameters = [];
        foreach ($function->getParams() as $parameterNode) {
            if (!$this->isSupportedInPhpVersion($parameterNode)) {
                continue;
            }
            $this->modifyStmtTypeByPhpVersion($parameterNode);
            $parameters[] = $parameterNode;
        }
        $function->params = $parameters;
    }
    /**
     * @param \PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $node
     * @return \PhpParser\Node\Name|\PhpParser\Node\Identifier|\PhpParser\Node\ComplexType|null
     */
    private function getStmtType($node)
    {
        $languageLevelTypeAwareAttribute = $this->getNodeAttribute($node, 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware');
        if ($languageLevelTypeAwareAttribute === null) {
            return null;
        }
        assert($languageLevelTypeAwareAttribute->args[0]->value instanceof Node\Expr\Array_);
        /** @var list<Node\Expr\ArrayItem> $types */
        $types = $languageLevelTypeAwareAttribute->args[0]->value->items;
        usort($types, static function (Node\Expr\ArrayItem $a, Node\Expr\ArrayItem $b) : int {
            return $b->key <=> $a->key;
        });
        foreach ($types as $type) {
            assert($type->key instanceof Node\Scalar\String_);
            assert($type->value instanceof Node\Scalar\String_);
            if ($this->parsePhpVersion($type->key->value) > $this->phpVersion) {
                continue;
            }
            return $this->normalizeType($type->value->value);
        }
        assert($languageLevelTypeAwareAttribute->args[1]->value instanceof Node\Scalar\String_);
        return $languageLevelTypeAwareAttribute->args[1]->value->value !== '' ? $this->normalizeType($languageLevelTypeAwareAttribute->args[1]->value->value) : null;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\ClassConst|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Stmt\Const_|\PhpParser\Node\Stmt\EnumCase $node
     */
    private function addDeprecatedDocComment($node) : void
    {
        if ($node instanceof Node\Expr\FuncCall) {
            if (!$this->isDeprecatedByPhpDocInPhpVersion($node)) {
                $this->removeAnnotationFromDocComment($node, 'deprecated');
            }
            return;
        }
        if ($node instanceof Node\Stmt\Const_) {
            return;
        }
        if (!$this->isDeprecatedInPhpVersion($node)) {
            $this->removeAnnotationFromDocComment($node, 'deprecated');
            return;
        }
        $this->addAnnotationToDocComment($node, 'deprecated');
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\ClassConst|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\Const_|\PhpParser\Node\Stmt\EnumCase $node
     */
    private function addAnnotationToDocComment($node, string $annotationName) : void
    {
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            $docCommentText = sprintf('/** @%s */', $annotationName);
        } else {
            $docCommentText = preg_replace('~(\\r?\\n\\s*)\\*/~', sprintf('\\1* @%s\\1*/', $annotationName), $docComment->getText());
        }
        $node->setDocComment(new Doc($docCommentText));
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\ClassConst|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Stmt\Const_|\PhpParser\Node\Stmt\EnumCase $node
     */
    private function removeAnnotationFromDocComment($node, string $annotationName) : void
    {
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return;
        }
        $docCommentText = preg_replace('~@' . $annotationName . '.*$~m', '', $docComment->getText());
        $node->setDocComment(new Doc($docCommentText));
    }
    private function isCoreExtension(string $extension) : bool
    {
        return in_array($extension, self::CORE_EXTENSIONS, \true);
    }
    private function isDeprecatedByPhpDocInPhpVersion(Node\Expr\FuncCall $node) : bool
    {
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return \false;
        }
        if (preg_match('#@deprecated\\s+(\\d+)\\.(\\d+)(?:\\.(\\d+))?$#m', $docComment->getText(), $matches) === 1) {
            $major = $matches[1];
            $minor = $matches[2];
            $patch = $matches[3] ?? 0;
            $versionId = sprintf('%d%02d%02d', $major, $minor, $patch);
            return $this->phpVersion >= $versionId;
        }
        return \true;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\ClassConst|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\EnumCase $node
     */
    private function isDeprecatedInPhpVersion($node) : bool
    {
        $deprecatedAttribute = $this->getNodeAttribute($node, 'JetBrains\\PhpStorm\\Deprecated');
        if ($deprecatedAttribute === null) {
            return \false;
        }
        foreach ($deprecatedAttribute->args as $attributeArg) {
            if ($attributeArg->name !== null && $attributeArg->name->toString() === 'since') {
                assert($attributeArg->value instanceof Node\Scalar\String_);
                return $this->parsePhpVersion($attributeArg->value->value) <= $this->phpVersion;
            }
        }
        return \true;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\Const_|\PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Stmt\ClassConst|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Param|\PhpParser\Node\Stmt\EnumCase $node
     */
    private function isSupportedInPhpVersion($node) : bool
    {
        [$fromVersion, $toVersion] = $this->getSupportedPhpVersions($node);
        if ($fromVersion !== null && $fromVersion > $this->phpVersion) {
            return \false;
        }
        return $toVersion === null || $toVersion >= $this->phpVersion;
    }
    /** @return array{0: int|null, 1: int|null}
     * @param \PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\Const_|\PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Stmt\ClassConst|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Param|\PhpParser\Node\Stmt\EnumCase $node */
    private function getSupportedPhpVersions($node) : array
    {
        $fromVersion = null;
        $toVersion = null;
        $docComment = $node->getDocComment();
        if ($docComment !== null) {
            if (preg_match('~@since\\s+(?P<version>\\d+\\.\\d+(?:\\.\\d+)?)\\s+~', $docComment->getText(), $sinceMatches) === 1) {
                $fromVersion = $this->parsePhpVersion($sinceMatches['version']);
            }
            if (preg_match('~@removed\\s+(?P<version>\\d+\\.\\d+(?:\\.\\d+)?)\\s+~', $docComment->getText(), $removedMatches) === 1) {
                $toVersion = $this->parsePhpVersion($removedMatches['version']) - 1;
            }
        }
        $elementsAvailable = $this->getNodeAttribute($node, 'JetBrains\\PhpStorm\\Internal\\PhpStormStubsElementAvailable');
        if ($elementsAvailable !== null) {
            foreach ($elementsAvailable->args as $i => $attributeArg) {
                $isFrom = \false;
                if ($attributeArg->name !== null && $attributeArg->name->toString() === 'from') {
                    $isFrom = \true;
                }
                if ($attributeArg->name === null && $i === 0) {
                    $isFrom = \true;
                }
                if ($isFrom) {
                    assert($attributeArg->value instanceof Node\Scalar\String_);
                    $fromVersion = $this->parsePhpVersion($attributeArg->value->value);
                }
                $isTo = \false;
                if ($attributeArg->name !== null && $attributeArg->name->toString() === 'to') {
                    $isTo = \true;
                }
                if ($attributeArg->name === null && $i === 1) {
                    $isTo = \true;
                }
                if (!$isTo) {
                    continue;
                }
                assert($attributeArg->value instanceof Node\Scalar\String_);
                $toVersion = $this->parsePhpVersion($attributeArg->value->value, 99);
            }
        }
        return [$fromVersion, $toVersion];
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\Const_|\PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Stmt\ClassConst|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Param|\PhpParser\Node\Stmt\EnumCase $node
     */
    private function getNodeAttribute($node, string $attributeName) : ?\PhpParser\Node\Attribute
    {
        if ($node instanceof Node\Expr\FuncCall || $node instanceof Node\Stmt\Const_) {
            return null;
        }
        foreach ($node->attrGroups as $attributesGroupNode) {
            foreach ($attributesGroupNode->attrs as $attributeNode) {
                if ($attributeNode->name->toString() === $attributeName) {
                    return $attributeNode;
                }
            }
        }
        return null;
    }
    private function parsePhpVersion(string $version, int $defaultPatch = 0) : int
    {
        $parts = array_map('intval', explode('.', $version));
        return $parts[0] * 10000 + $parts[1] * 100 + ($parts[2] ?? $defaultPatch);
    }
    /**
     * @return \PhpParser\Node\Name|\PhpParser\Node\Identifier|\PhpParser\Node\ComplexType|null
     */
    private function normalizeType(string $type)
    {
        // There are some invalid types in stubs, eg. `string[]|string|null`
        if (\strpos($type, '[') !== \false) {
            return null;
        }
        /** @psalm-suppress InternalClass, InternalMethod */
        return BuilderHelpers::normalizeType($type);
    }
    private function getStubsDirectory() : string
    {
        if ($this->stubsDirectory !== null) {
            return $this->stubsDirectory;
        }
        foreach (self::SEARCH_DIRECTORIES as $directory) {
            if (is_dir($directory)) {
                return $this->stubsDirectory = $directory;
            }
        }
        // @codeCoverageIgnoreStart
        // @infection-ignore-all
        // Untestable code
        throw CouldNotFindPhpStormStubs::create();
        // @codeCoverageIgnoreEnd
    }
}
