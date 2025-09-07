<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\Type;
use function array_key_exists;
use function array_merge;
use function array_shift;
use function count;
use function explode;
use function implode;
use function ltrim;
use function sprintf;
use function str_starts_with;
use function strtolower;
/**
 * @api
 * @final
 */
class NameScope
{
    /**
     * @var non-empty-string|null
     */
    private $namespace;
    /**
     * @var array<string, string>
     */
    private $uses;
    /**
     * @var ?string
     */
    private $className;
    /**
     * @var ?string
     */
    private $functionName;
    /**
     * @var array<string, true>
     */
    private $typeAliasesMap;
    /**
     * @var bool
     */
    private $bypassTypeAliases;
    /**
     * @var array<string, string>
     */
    private $constUses;
    /**
     * @var ?string
     */
    private $typeAliasClassName;
    /**
     * @var TemplateTypeMap
     */
    private $templateTypeMap;
    /**
     * @api
     * @param non-empty-string|null $namespace
     * @param array<string, string> $uses alias(string) => fullName(string)
     * @param array<string, string> $constUses alias(string) => fullName(string)
     * @param array<string, true> $typeAliasesMap
     */
    public function __construct(?string $namespace, array $uses, ?string $className = null, ?string $functionName = null, ?TemplateTypeMap $templateTypeMap = null, array $typeAliasesMap = [], bool $bypassTypeAliases = \false, array $constUses = [], ?string $typeAliasClassName = null)
    {
        $this->namespace = $namespace;
        $this->uses = $uses;
        $this->className = $className;
        $this->functionName = $functionName;
        $this->typeAliasesMap = $typeAliasesMap;
        $this->bypassTypeAliases = $bypassTypeAliases;
        $this->constUses = $constUses;
        $this->typeAliasClassName = $typeAliasClassName;
        $this->templateTypeMap = $templateTypeMap ?? TemplateTypeMap::createEmpty();
    }
    public function getNamespace() : ?string
    {
        return $this->namespace;
    }
    /**
     * @return array<string, string>
     */
    public function getUses() : array
    {
        return $this->uses;
    }
    public function hasUseAlias(string $name) : bool
    {
        return isset($this->uses[strtolower($name)]);
    }
    /**
     * @return array<string, string>
     */
    public function getConstUses() : array
    {
        return $this->constUses;
    }
    public function getClassName() : ?string
    {
        return $this->className;
    }
    public function getClassNameForTypeAlias() : ?string
    {
        return $this->typeAliasClassName ?? $this->className;
    }
    public function resolveStringName(string $name) : string
    {
        if (str_starts_with($name, '\\')) {
            return ltrim($name, '\\');
        }
        $nameParts = explode('\\', $name);
        $firstNamePart = strtolower($nameParts[0]);
        if (isset($this->uses[$firstNamePart])) {
            if (count($nameParts) === 1) {
                return $this->uses[$firstNamePart];
            }
            array_shift($nameParts);
            return sprintf('%s\\%s', $this->uses[$firstNamePart], implode('\\', $nameParts));
        }
        if ($this->namespace !== null) {
            return sprintf('%s\\%s', $this->namespace, $name);
        }
        return $name;
    }
    /**
     * @return non-empty-list<string>
     */
    public function resolveConstantNames(string $name) : array
    {
        if (str_starts_with($name, '\\')) {
            return [ltrim($name, '\\')];
        }
        $nameParts = explode('\\', $name);
        $firstNamePart = strtolower($nameParts[0]);
        if (count($nameParts) > 1) {
            if (isset($this->uses[$firstNamePart])) {
                array_shift($nameParts);
                return [sprintf('%s\\%s', $this->uses[$firstNamePart], implode('\\', $nameParts))];
            }
        } elseif (isset($this->constUses[$firstNamePart])) {
            return [$this->constUses[$firstNamePart]];
        }
        if ($this->namespace !== null) {
            return [sprintf('%s\\%s', $this->namespace, $name), $name];
        }
        return [$name];
    }
    public function getTemplateTypeScope() : ?TemplateTypeScope
    {
        if ($this->className !== null) {
            if ($this->functionName !== null) {
                return TemplateTypeScope::createWithMethod($this->className, $this->functionName);
            }
            return TemplateTypeScope::createWithClass($this->className);
        }
        if ($this->functionName !== null) {
            return TemplateTypeScope::createWithFunction($this->functionName);
        }
        return null;
    }
    public function getTemplateTypeMap() : TemplateTypeMap
    {
        return $this->templateTypeMap;
    }
    public function resolveTemplateTypeName(string $name) : ?Type
    {
        return $this->templateTypeMap->getType($name);
    }
    public function withTemplateTypeMap(TemplateTypeMap $map) : self
    {
        if ($map->isEmpty() && $this->templateTypeMap->isEmpty()) {
            return $this;
        }
        return new self($this->namespace, $this->uses, $this->className, $this->functionName, new TemplateTypeMap(array_merge($this->templateTypeMap->getTypes(), $map->getTypes())), $this->typeAliasesMap, $this->bypassTypeAliases, $this->constUses);
    }
    public function withClassName(string $className) : self
    {
        return new self($this->namespace, $this->uses, $className, $this->functionName, $this->templateTypeMap, $this->typeAliasesMap, $this->bypassTypeAliases, $this->constUses);
    }
    public function unsetTemplateType(string $name) : self
    {
        $map = $this->templateTypeMap;
        if (!$map->hasType($name)) {
            return $this;
        }
        return new self($this->namespace, $this->uses, $this->className, $this->functionName, $this->templateTypeMap->unsetType($name), $this->typeAliasesMap, $this->bypassTypeAliases, $this->constUses);
    }
    public function bypassTypeAliases() : self
    {
        return new self($this->namespace, $this->uses, $this->className, $this->functionName, $this->templateTypeMap, $this->typeAliasesMap, \true, $this->constUses);
    }
    public function shouldBypassTypeAliases() : bool
    {
        return $this->bypassTypeAliases;
    }
    public function hasTypeAlias(string $alias) : bool
    {
        return array_key_exists($alias, $this->typeAliasesMap);
    }
    /**
     * @param mixed[] $properties
     */
    public static function __set_state(array $properties) : self
    {
        return new self($properties['namespace'], $properties['uses'], $properties['className'], $properties['functionName'], $properties['templateTypeMap'], $properties['typeAliasesMap'], $properties['bypassTypeAliases'], $properties['constUses']);
    }
}
