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
        $areaCheck = $currentPage->getElementalRelations();
        $activeAreas = [];

        if (count($areaCheck)) {
            foreach ($areaCheck as $area) {
                if ($currentPage->$area() && $currentPage->$area()->ID == $this->owner->ID) {
                    $activeAreas[$area] = $area;
                }
            }
        }

        if ($currentPage->InheritElemenalSets) {
            $elementsFromSets = $this->getElementsFromAppliedElementalSets($currentPage);

            if ($elementsFromSets->count()) {
                foreach ($elementsFromSets as $setElement) {
                    if (!$nativeItems->find('ID', $setElement->ID) && in_array($setElement->ElementalArea, $activeAreas)) {
                        if ($setElement->AboveOrBelow == 'Above') {
                            $elements->unshift($setElement);
                        } else {
                            $elements->push($setElement);
                        }
                    }
                }
            }
        }

        if (!is_null($elements)) {
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

        if (!$page->InheritElemenalSets) {
            return $list;
        }

        $sets = ElementalSet::get()->where("(PageTypesValue IS NULL) OR (PageTypesValue LIKE '%:\"{$page->ClassName}%')");
        $ancestors = $page->getAncestors()->column('ID');

        foreach ($sets as $set) {
            $restrictedToParentIDs = $set->PageParents()->column('ID');
            if (count($restrictedToParentIDs)) {
                if ($set->IncludePageParent && in_array($page->ID, $restrictedToParentIDs)) {
                    $list->add($set);
                } else {
                    if (count($ancestors)) {
                        foreach ($ancestors as $ancestor) {
                            if (in_array($ancestors, $restrictedToParentIDs)) {
                                $list->add($set);
                                continue;
                            }
                        }
                    }
                }
            } else {
                $list->add($set);
            }
        }

        return $list;
    }
}