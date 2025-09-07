<?php

declare (strict_types=1);
namespace PHPStan\Rules;

final class ClassNameCheck
{
    /**
     * @var ClassCaseSensitivityCheck
     */
    private $classCaseSensitivityCheck;
    /**
     * @var ClassForbiddenNameCheck
     */
    private $classForbiddenNameCheck;
    public function __construct(\PHPStan\Rules\ClassCaseSensitivityCheck $classCaseSensitivityCheck, \PHPStan\Rules\ClassForbiddenNameCheck $classForbiddenNameCheck)
    {
        $this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
        $this->classForbiddenNameCheck = $classForbiddenNameCheck;
    }
    /**
     * @param ClassNameNodePair[] $pairs
     * @return list<IdentifierRuleError>
     */
    public function checkClassNames(array $pairs, bool $checkClassCaseSensitivity = \true) : array
    {
        $errors = [];
        if ($checkClassCaseSensitivity) {
            foreach ($this->classCaseSensitivityCheck->checkClassNames($pairs) as $error) {
                $errors[] = $error;
            }
        }
        foreach ($this->classForbiddenNameCheck->checkClassNames($pairs) as $error) {
            $errors[] = $error;
        }
        return $errors;
    }
}
