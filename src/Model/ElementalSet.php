<?php

namespace Dynamic\ElementalSets\Model;

use DNADesign\Elemental\Extensions\ElementalAreasExtension;
use DNADesign\Elemental\Forms\ElementalAreaField;
use DNADesign\Elemental\Models\BaseElement;
use DNADesign\Elemental\Models\ElementalArea;
use SilverStripe\CMS\Controllers\CMSPageEditController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\Debug;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

/**
 * Class ElementalSet
 * @package Dynamic\ElementalSets\Model
 *
 * @property string Title
 *
 * @mixin Versioned
 */
class ElementalSet extends DataObject
{
    /**
     * @var string
     */
    private static $singular_name = 'Elemental Set';

    /**
     * @var string
     */
    private static $plural_name = 'Elemental Sets';

    /**
     * @var string
     */
    private static $table_name = 'ElementalSet';

    /**
     * @var array
     */
    private static $extensions = [
        Versioned::class,
        //ElementalAreasExtension::class,
    ];

    /**
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar(255)',
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'ElementalArea' => ElementalArea::class,
    ];

    /**
     * @var array
     */
    private static $owns = [
        'ElementalArea',
    ];

    /**
     * @var array
     */
    private static $cascade_duplicates = [
        'ElementalArea',
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Title' => 'Title',
        'ElementsCount' => 'Total Elements',
    ];

    /**
     * @return \SilverStripe\Forms\FieldList
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeFieldFromTab('Root', 'PageParents');

            $fields->addFieldsToTab(
                'Root.Main',
                [
                    TextField::create('Title')
                        ->setTitle(_t("{$this->ClassName}.Title", 'Title')),
                ]
            );

            if (!$this->exists()) {
                $fields->addFieldToTab('Root.Main', LiteralField::create('NotSaved',
                    "<p class='message warning'>" . _t("{$this->ClassName}.YouCanAddElementsToThisSetOnceYouHaveSavedIt",
                        'You can add Elements to this set once you have saved it for the first time') . '</p>'));
            }

            $fields->removeByName('ElementalAreaID');

            if ($this->exists()) {
                $area = ElementalAreaField::create('ElementalArea', $this->ElementalArea(), $this->getElementalTypes());

                $fields->addFieldToTab('Root.Main', $area);
            }
        });

        $fields = parent::getCMSFields();

        //$elements = $fields->dataFieldByName('ElementalArea');

        /*if ($elements instanceof GridField) {
            $config = $elements->getConfig();
            //$addElement = $config->getComponentByType(GridFieldAddNewMultiClass::class);
            //$addElement = $addElement->setFragment('toolbar-header-right');

            $config->removeComponentsByType([
                GridFieldAddExistingAutocompleter::class,
                GridFieldAddNewMultiClass::class,
            ])
                ->addComponent(new GridFieldAddExistingSearchButton('toolbar-header-left'))
                ->addComponent($addElement);
        }*/

        return $fields;
    }

    public function getElementalTypes()
    {
        $config = $this->config();

        if (is_array($config->get('allowed_elements'))) {
            if ($config->get('stop_element_inheritance')) {
                $availableClasses = $config->get('allowed_elements', Config::UNINHERITED);
            } else {
                $availableClasses = $config->get('allowed_elements');
            }
        } else {
            $availableClasses = ClassInfo::subclassesFor(BaseElement::class);
        }

        if ($config->get('stop_element_inheritance')) {
            $disallowedElements = (array) $config->get('disallowed_elements', Config::UNINHERITED);
        } else {
            $disallowedElements = (array) $config->get('disallowed_elements');
        }
        $list = [];

        foreach ($availableClasses as $availableClass) {
            /** @var BaseElement $inst */
            $inst = singleton($availableClass);

            if (!in_array($availableClass, $disallowedElements) && $inst->canCreate()) {
                if ($inst->hasMethod('canCreateElement') && !$inst->canCreateElement()) {
                    continue;
                }

                $list[$availableClass] = $inst->getType();
            }
        }

        if ($config->get('sort_types_alphabetically') !== false) {
            asort($list);
        }

        if (isset($list[BaseElement::class])) {
            unset($list[BaseElement::class]);
        }

        $this->invokeWithExtensions('updateAvailableTypesForClass', $class, $list);

        return $list;
    }


    /**
     * @return mixed
     */
    public function getElementsCount()
    {
        return $this->ElementalArea()->Elements()->count();
    }

    /**
     * Generates a link to edit this page in the CMS.
     *
     * @return string
     */
    public function CMSEditLink()
    {
        $link = Controller::join_links(
            Controller::curr()->Link('show'),
            $this->ID
        );

        return Director::absoluteURL($link);
    }

    /**
     * @param null $action
     * @return string
     */
    public function Link($action = null)
    {
        return Controller::curr()->Link($action);
    }

    public function inlineEditable()
    {
        return false;
    }


}
