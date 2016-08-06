<?php

namespace InfyOm\Generator\Common;

class GeneratorField
{
    /** @var  string */
    public $name, $dbInput, $htmlType;

    /** @var  string */
    public $migrationText, $validations;

    /** @var  boolean */
    public $isSearchable = true, $isFillable = true, $isPrimary = false, $inForm = true, $inIndex = true;

    /** @var  GeneratorFieldRelation[] */
    public $relations;

    public function parseDBType($dbInput)
    {
        $this->dbInput = $dbInput;
        $this->prepareMigrationText();
    }

    public function parseOptions($options)
    {
        $options = strtolower($options);
        $optionsArr = explode(",", $options);
        if (in_array("s", $optionsArr)) {
            $this->isSearchable = false;
        }
        if (in_array("p", $optionsArr)) {
            $this->isPrimary = true;
        }
        if (in_array("f", $optionsArr)) {
            $this->isFillable = false;
        }
        if (in_array("if", $optionsArr)) {
            $this->inForm = false;
        }
        if (in_array("ii", $optionsArr)) {
            $this->inIndex = false;
        }
    }

    private function prepareMigrationText()
    {
        $inputsArr = explode(":", $this->dbInput);
        $this->migrationText = '$table->';

        $fieldTypeParams = explode(",", array_shift($inputsArr));
        $fieldType = array_shift($fieldTypeParams);
        $this->migrationText .= $fieldType . "('" . $this->name . "'";

        foreach ($fieldTypeParams as $param) {
            $this->migrationText .= ", " . $param;
        }

        $this->migrationText .= ")";

        foreach ($inputsArr as $input) {
            $inputParams = explode(",", $input);
            $functionName = array_shift($inputParams);
            $this->migrationText .= "->" . $functionName;
            if ($functionName == "foreign") {
                $foreignTable = array_shift($inputParams);
                $foreignField = array_shift($inputParams);
                $this->migrationText .= "('" . $this->name . "')->references('" . $foreignField . "')->on('" . $foreignTable . "')";
            } else {
                $this->migrationText .= "(";
                foreach ($inputParams as $param) {
                    $this->migrationText .= ", " . $param;
                }
                $this->migrationText .= ")";
            }
        }

        $this->migrationText .= ";";
    }
}