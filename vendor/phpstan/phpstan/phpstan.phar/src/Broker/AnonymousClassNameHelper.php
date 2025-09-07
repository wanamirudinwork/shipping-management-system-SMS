<?php

declare (strict_types=1);
namespace PHPStan\Broker;

use PhpParser\Node;
use PHPStan\File\FileHelper;
use PHPStan\File\RelativePathHelper;
use PHPStan\Parser\AnonymousClassVisitor;
use PHPStan\ShouldNotHappenException;
use function md5;
use function sprintf;
final class AnonymousClassNameHelper
{
    /**
     * @var FileHelper
     */
    private $fileHelper;
    /**
     * @var RelativePathHelper
     */
    private $relativePathHelper;
    public function __construct(FileHelper $fileHelper, RelativePathHelper $relativePathHelper)
    {
        $this->fileHelper = $fileHelper;
        $this->relativePathHelper = $relativePathHelper;
    }
    public function getAnonymousClassName(Node\Stmt\Class_ $classNode, string $filename) : string
    {
        if (isset($classNode->namespacedName)) {
            throw new ShouldNotHappenException();
        }
        $filename = $this->relativePathHelper->getRelativePath($this->fileHelper->normalizePath($filename, '/'));
        /** @var int|null $lineIndex */
        $lineIndex = $classNode->getAttribute(AnonymousClassVisitor::ATTRIBUTE_LINE_INDEX);
        if ($lineIndex === null) {
            $hash = md5(sprintf('%s:%s', $filename, $classNode->getStartLine()));
        } else {
            $hash = md5(sprintf('%s:%s:%d', $filename, $classNode->getStartLine(), $lineIndex));
        }
        return sprintf('AnonymousClass%s', $hash);
    }
}
