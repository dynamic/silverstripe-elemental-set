<?php

namespace Dynamic\ElementalSets\Extensions;

use Dynamic\ElementalSets\Model\ElementalSet;
use SilverStripe\ORM\DataExtension;

/**
 * Class BaseElementDataExtension
 * @package Dynamic\ElementalSets\Extensions
 */
class BaseElementDataExtension extends DataExtension
{
    public function updateRenderTemplates(&$templates, &$suffix)
    {
        $ownerPage = $this->owner->Parent()->getOwnerPage();
        if ($ownerPage instanceof ElementalSet) {
            $classes = array_keys($templates);
            if (isset($classes[0])) {
                $class = $classes[0];
                if ($this->owner->ElementalArea) {
                    array_unshift($templates[$class], "{$class}_{$this->owner->ElementalArea}");
                }
            }
        }
    }
}