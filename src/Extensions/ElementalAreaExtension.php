<?php

namespace Dynamic\ElementalSets\Extensions;

use Dynamic\ElementalSets\Model\ElementalSet;
use SilverStripe\ORM\DataExtension;

/**
 * Class ElementalAreaExtension
 * @package Dynamic\ElementalSets\Extensions
 */
class ElementalAreaExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $belongs_many_many = [
        'ElementalSets' => ElementalSet::class,
    ];
}
