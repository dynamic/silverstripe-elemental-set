<?php

namespace Dynamic\ElementalSets\Extensions;

use Dynamic\ElementalSets\Model\ElementalSet;
use SilverStripe\ORM\DataExtension;

/**
 * Class ElementExtension
 * @package Dynamic\ElementalSets\Extensions
 */
class ElementExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $belongs_many_many = [
        'ElementSets' => ElementalSet::class,
    ];
}
