<?php

namespace Dynamic\ElementalSets\Admin;

use Dynamic\ElementalSets\Model\ElementalSet;
use SilverStripe\Admin\ModelAdmin;

/**
 * Class ElementalSetAdmin
 * @package Dynamic\ElementalSets\Admin
 */
class ElementalSetAdmin extends ModelAdmin
{
    /**
     * @var string
     */
    private static $menu_title = 'Elemental Sets';

    /**
     * @var string
     */
    private static $url_segment = 'elemental-sets-admin';

    /**
     * @var array
     */
    private static $managed_models = [
        ElementalSet::class,
    ];
}