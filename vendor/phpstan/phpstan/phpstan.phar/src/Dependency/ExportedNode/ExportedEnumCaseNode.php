<?php

declare (strict_types=1);
namespace PHPStan\Dependency\ExportedNode;

use JsonSerializable;
use PHPStan\Dependency\ExportedNode;
use ReturnTypeWillChange;
final class ExportedEnumCaseNode implements ExportedNode, JsonSerializable
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var ?string
     */
    private $value;
    /**
     * @var ?ExportedPhpDocNode
     */
    private $phpDoc;
    public function __construct(string $name, ?string $value, ?\PHPStan\Dependency\ExportedNode\ExportedPhpDocNode $phpDoc)
    {
        $this->name = $name;
        $this->value = $value;
        $this->phpDoc = $phpDoc;
    }
    public function equals(ExportedNode $node) : bool
    {
        if (!$node instanceof self) {
            return \false;
        }
        if ($this->phpDoc === null) {
            if ($node->phpDoc !== null) {
                return \false;
            }
        } elseif ($node->phpDoc !== null) {
            if (!$this->phpDoc->equals($node->phpDoc)) {
                return \false;
            }
        } else {
            return \false;
        }
        return $this->name === $node->name && $this->value === $node->value;
    }
    /**
     * @param mixed[] $properties
     * @return self
     */
    public static function __set_state(array $properties) : ExportedNode
    {
        return new self($properties['name'], $properties['value'], $properties['phpDoc']);
    }
    /**
     * @param mixed[] $data
     * @return self
     */
    public static function decode(array $data) : ExportedNode
    {
        return new self($data['name'], $data['value'], $data['phpDoc'] !== null ? \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::decode($data['phpDoc']['data']) : null);
    }
    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return ['type' => self::class, 'data' => ['name' => $this->name, 'value' => $this->value, 'phpDoc' => $this->phpDoc]];
    }
}
