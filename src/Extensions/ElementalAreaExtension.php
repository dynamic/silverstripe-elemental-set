<?php

namespace Dynamic\ElementalSets\Extensions;

use DNADesign\Elemental\Models\BaseElement;
use Dynamic\ElementalSets\Model\ElementalSet;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\UnsavedRelationList;

/**
 * Class ElementalAreaExtension
 * @package Dynamic\ElementalSets\Extensions
 */
class ElementalAreaExtension extends DataExtension
{
    /**
     * Used in template instead of {@link Elements()} to wrap each element in
     * its' controller, making it easier to access and process form logic and
     * actions stored in {@link ElementController}.
     *
     * @return ArrayList
     * @throws \Exception
     */
    public function SetsElementControllers()
    {
        // Don't worry about elemental sets building their own list
        if ($this->owner->OwnerClassName == ElementalSet::class) return ArrayList::create();

        // Don't try and process unsaved lists
        if ($this->owner->Elements() instanceof UnsavedRelationList) {
            return ArrayList::create();
        }

        $controllers = ArrayList::create();
        $elements = ArrayList::create();

        $nativeItems = $this->owner->Elements()->filterByCallback(function (BaseElement $item) {
            return $item->canView();
        });

        $elements->merge($nativeItems);

        $currentPage = Controller::curr()->data();
        $blackList = (array)$currentPage->config()->get('black_list_areas');
        $areaCheck = $currentPage->getElementalRelations();
        $activeAreas = [];

        if (count($areaCheck)) {
            foreach ($areaCheck as $area) {
                if ($currentPage->$area() && !in_array($area, $blackList)) {
                    $activeAreas[$area] = $currentPage->$area()->ID;
                }
            }
        }

        if ($currentPage->InheritElementalSets) {
            $elementsFromSets = $this->getElementsFromAppliedElementalSets($currentPage);

            if ($elementsFromSets->count()) {
                foreach ($elementsFromSets as $setElement) {
                    if (array_key_exists($setElement->ElementalArea, $activeAreas) && $activeAreas[$setElement->ElementalArea] == $this->owner->ID) {
                        if ($setElement->AboveOrBelow == 'Above') {
                            $elements->unshift($setElement);
                        } else {
                            $elements->push($setElement);
                        }
                    }
                }
            }
        }

        if ($elements->exists()) {
            foreach ($elements as $element) {
                $controller = $element->getController();
                $controllers->push($controller);
            }
        }

        return $controllers;
    }

    protected function getElementsFromAppliedElementalSets($page)
    {
        $elements = ArrayList::create();

        $sets = $this->getAppliedSets($page);

        if (!$sets->count()) {
            return $elements;
        }

        foreach ($sets as $set) {
            $setElements = $set->ElementalArea()->Elements()->sort('Sort DESC');

            $this->owner->extend('updateSetElements', $setElements);

            $elements->merge($setElements);
        }

        $elements->removeDuplicates();

        return $elements;
    }

    protected function getAppliedSets($page)
    {
        $list = ArrayList::create();

        if (!$page->InheritElementalSets) {
            return $list;
        }

        $sets = ElementalSet::get()->where("(PageTypesValue IS NULL) OR (PageTypesValue='[]') OR (PageTypesValue LIKE '%:{$page->ClassName}%')");
        $ancestors = $page->getAncestors()->column('ID');

        foreach ($sets as $set) {
            $restrictedToParentIDs = $set->PageParents()->column('ID');
            if (count($restrictedToParentIDs)) {
                if ($set->IncludePageParent && in_array($page->ID, $restrictedToParentIDs)) {
                    $list->add($set);
                } else {
                    if (count($ancestors)) {
                        foreach ($ancestors as $ancestor) {
                            if (in_array($ancestor, $restrictedToParentIDs)) {
                                $list->add($set);
                                continue;
                            }
                        }
                    }
                }
            }
        }

        return $list;
    }
}
