<?php
/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Content\Block;

use Sulu\Component\Content\Compat\Block\BlockProperty;
use Sulu\Component\Content\Compat\Block\BlockPropertyType;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\Document\Structure\PropertyValue;

class BlockPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testSetValue()
    {
        $data = [['type' => 'test', 'title' => 'my title', 'description' => 'my description']];

        $blockProperty = new BlockProperty('block', [], 'test');
        $blockPropertyType = $this->prophesize(BlockPropertyType::class);
        $blockPropertyType->getName()->willReturn('test');
        $blockProperty->addType($blockPropertyType->reveal());
        $blockPropertyValue = $this->prophesize(PropertyValue::class);
        $blockPropertyValue->setValue($data)->shouldBeCalled();
        $blockPropertyValue->getValue()->willReturn(null);
        $blockProperty->setPropertyValue($blockPropertyValue->reveal());
        $childProperty1 = $this->prophesize(PropertyInterface::class);
        $childProperty1->getName()->willReturn('title');
        $childProperty1->setValue($data[0]['title'])->shouldBeCalled();
        $childProperty2 = $this->prophesize(PropertyInterface::class);
        $childProperty2->getName()->willReturn('description');
        $childProperty2->setValue($data[0]['description'])->shouldBeCalled();
        $blockPropertyType->getChildProperties()->willReturn([$childProperty1->reveal(), $childProperty2->reveal()]);

        $blockProperty->setValue($data);
    }
}
