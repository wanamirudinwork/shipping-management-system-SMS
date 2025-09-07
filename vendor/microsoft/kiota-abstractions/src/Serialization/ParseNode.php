<?php

namespace Microsoft\Kiota\Abstractions\Serialization;

use DateInterval;
use DateTime;
use Microsoft\Kiota\Abstractions\Enum;
use Microsoft\Kiota\Abstractions\Types\Date;
use Microsoft\Kiota\Abstractions\Types\Time;
use Psr\Http\Message\StreamInterface;

interface ParseNode {
    /**
     * Gets a new parse node for the given identifier.
     * @param string $identifier the identifier of the current node property.
     * @return self|null a new parse node for the given identifier.
     */
    public function getChildNode(string $identifier): ?ParseNode;

    /**
     * Gets the string value of the node.
     * @return string|null the string value of the node.
     */
    public function getStringValue(): ?string;

    /**
     * Gets the boolean value of the node.
     * @return bool|null the boolean value of the node.
     */
    public function getBooleanValue(): ?bool;

    /**
     * Gets the Integer value of the node.
     * @return int|null the Integer value of the node.
     */
    public function getIntegerValue(): ?int;

    /**
     * Gets the Float value of the node.
     * @return float|null the Float value of the node.
     */
    public function getFloatValue(): ?float;

    /**
     * Gets the model object value of the node.
     * @template T of Parsable
     * @param array{class-string<T>,string} $type The type for the Parsable object.
     * @return T|null the model object value of the node.
     */
    public function getObjectValue(array $type): ?Parsable;

    /**
     * @template T of Parsable
     * @param array{class-string<T>,string} $type The underlying type for the Parsable class.
     * @return array<T>|null An array of Parsable values.
     */
    public function getCollectionOfObjectValues(array $type): ?array;

    /**
     * Get a collection of values that are not parsable in Nature.
     * @param string|null $typeName
     * @return array<mixed>|null A collection of primitive values.
     */
    public function getCollectionOfPrimitiveValues(?string $typeName = null): ?array;

    /**
     * Gets the DateTimeValue of the node
     * @return DateTime|null
     */
    public function getDateTimeValue(): ?DateTime;

    /**
     * Gets the DateInterval value of the node
     * @return DateInterval|null
     */
    public function getDateIntervalValue(): ?DateInterval;

    /**
     * Gets the Date only value of the node
     * @return Date|null
     */
    public function getDateValue(): ?Date;

    /**
     * Gets the Time only value of the node
     * @return Time|null
     */
    public function getTimeValue(): ?Time;

    /**
     * Gets the Enum value of the node.
     * @template T of Enum
     * @param class-string<T> $targetEnum
     * @return T|null the Enum value of the node.
     */
    public function getEnumValue(string $targetEnum): ?Enum;

    /**
     * @template T of Enum
     * @param class-string<T> $targetClass
     * @return array<T>|null
     */
    public function getCollectionOfEnumValues(string $targetClass): ?array;

    /**
     * Get a Stream from node.
     * @return StreamInterface|null
     */
    public function getBinaryContent(): ?StreamInterface;
    /**
     * Gets the callback called before the node is deserialized.
     * @return callable the callback called before the node is deserialized.
     */
    public function getOnBeforeAssignFieldValues(): ?callable;

    /**
     * Gets the callback called after the node is deserialized.
     * @return callable the callback called after the node is deserialized.
     */
    public function getOnAfterAssignFieldValues(): ?callable;

    /**
     * Sets the callback called after the node is deserialized.
     * @param callable $value the callback called after the node is deserialized.
     */
    public function setOnAfterAssignFieldValues(callable $value): void;

    /**
     * Sets the callback called before the node is deserialized.
     * @param callable $value the callback called before the node is deserialized.
     */
    public function setOnBeforeAssignFieldValues(callable $value): void;
}
