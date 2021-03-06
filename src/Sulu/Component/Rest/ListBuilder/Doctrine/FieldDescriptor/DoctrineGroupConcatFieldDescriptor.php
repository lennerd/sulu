<?php

/*
 * This file is part of the Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor;

use JMS\Serializer\Annotation\ExclusionPolicy;

/**
 * This field descriptor can be used to group-concatenate a joined (1:n) field descriptor.
 *
 * @ExclusionPolicy("all")
 */
class DoctrineGroupConcatFieldDescriptor extends AbstractDoctrineFieldDescriptor
{
    /**
     * The field descriptor which will be group concatenated.
     *
     * @var AbstractDoctrineFieldDescriptor
     */
    private $fieldDescriptor;

    /**
     * @var string
     */
    private $glue;

    public function __construct(
        AbstractDoctrineFieldDescriptor $fieldDescriptor,
        $name,
        $translation = null,
        $glue = ',',
        $disabled = false,
        $default = false,
        $type = '',
        $width = '',
        $minWidth = '',
        $sortable = true,
        $editable = false,
        $cssClass = ''
    ) {
        parent::__construct($name, $translation, $disabled, $default, $type, $width, $minWidth, $sortable, $editable, $cssClass);
        $this->fieldDescriptor = $fieldDescriptor;
        $this->glue = $glue;
    }

    /**
     * Returns the select statement for this field without the alias.
     *
     * @return string
     */
    public function getSelect()
    {
        return 'GROUP_CONCAT(' . $this->fieldDescriptor->getSelect() . ' SEPARATOR \'' . $this->glue . '\')';
    }

    /**
     * Returns all the joins required for this field.
     *
     * @return DoctrineJoinDescriptor[]
     */
    public function getJoins()
    {
        return $this->fieldDescriptor->getJoins();
    }
}
