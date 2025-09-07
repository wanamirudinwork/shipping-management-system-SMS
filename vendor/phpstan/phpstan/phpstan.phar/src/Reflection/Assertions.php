<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\PhpDoc\Tag\AssertTag;
use PHPStan\Type\Type;
use function array_filter;
use function array_map;
use function array_merge;
use function count;
/**
 * @api
 * @final
 */
class Assertions
{
    /**
     * @var AssertTag[]
     */
    private $asserts;
    /**
     * @var ?self
     */
    private static $empty = null;
    /**
     * @param AssertTag[] $asserts
     */
    private function __construct(array $asserts)
    {
        $this->asserts = $asserts;
    }
    /**
     * @return AssertTag[]
     */
    public function getAll() : array
    {
        return $this->asserts;
    }
    /**
     * @return AssertTag[]
     */
    public function getAsserts() : array
    {
        return array_filter($this->asserts, static function (AssertTag $assert) {
            return $assert->getIf() === AssertTag::NULL;
        });
    }
    /**
     * @return AssertTag[]
     */
    public function getAssertsIfTrue() : array
    {
        return array_merge(array_filter($this->asserts, static function (AssertTag $assert) {
            return $assert->getIf() === AssertTag::IF_TRUE;
        }), array_map(static function (AssertTag $assert) {
            return $assert->negate();
        }, array_filter($this->asserts, static function (AssertTag $assert) {
            return $assert->getIf() === AssertTag::IF_FALSE && !$assert->isEquality();
        })));
    }
    /**
     * @return AssertTag[]
     */
    public function getAssertsIfFalse() : array
    {
        return array_merge(array_filter($this->asserts, static function (AssertTag $assert) {
            return $assert->getIf() === AssertTag::IF_FALSE;
        }), array_map(static function (AssertTag $assert) {
            return $assert->negate();
        }, array_filter($this->asserts, static function (AssertTag $assert) {
            return $assert->getIf() === AssertTag::IF_TRUE && !$assert->isEquality();
        })));
    }
    /**
     * @param callable(Type): Type $callable
     */
    public function mapTypes(callable $callable) : self
    {
        $assertTagsCallback = static function (AssertTag $tag) use($callable) : AssertTag {
            return $tag->withType($callable($tag->getType()));
        };
        return new self(array_map($assertTagsCallback, $this->asserts));
    }
    public function intersectWith(\PHPStan\Reflection\Assertions $other) : self
    {
        return new self(array_merge($this->getAll(), $other->getAll()));
    }
    public static function createEmpty() : self
    {
        $empty = self::$empty;
        if ($empty !== null) {
            return $empty;
        }
        $empty = new self([]);
        self::$empty = $empty;
        return $empty;
    }
    public static function createFromResolvedPhpDocBlock(ResolvedPhpDocBlock $phpDocBlock) : self
    {
        $tags = $phpDocBlock->getAssertTags();
        if (count($tags) === 0) {
            return self::createEmpty();
        }
        return new self($tags);
    }
}
