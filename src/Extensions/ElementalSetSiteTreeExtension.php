<?php

namespace Dynamic\ElementalSets\Extensions;

use SilverStripe\ORM\DataExtension;

/**
 * Class ElementalSetSiteTreeExtension
 * @package Dynamic\ElementalSets\Extensions
 */
class ElementalSetSiteTreeExtension extends DataExtension
{
    private static $db = [
        'InheritElemenalSets' => 'Boolean',
    ];
    
    private static $defaults = [
        'InheritElementalSets' => 1,
    ];
}