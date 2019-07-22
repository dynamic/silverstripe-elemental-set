<?php

namespace Dynamic\ElementalSets\Model;

use SilverStripe\CMS\Model\RedirectorPage;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\CMS\Model\VirtualPage;
use SilverStripe\Control\Controller;
use SilverStripe\ErrorPage\ErrorPage;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeMultiselectField;
use SilverStripe\ORM\ArrayLib;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Symbiote\GridFieldExtensions\GridFieldAddExistingSearchButton;
use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClass;
use Symbiote\MultiValueField\Fields\MultiValueCheckboxField;
use Symbiote\MultiValueField\ORM\FieldType\MultiValueField;

/**
 * Class ElementalSet
 * @package Dynamic\ElementalSets\Model
 *
 * @property string Title
 * @property array PageTypes
 * @property boolean IncludePageParent
 *
 * @method \SilverStripe\ORM\ManyManyList PageParents()
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
    ];

    /**
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar(255)',
        'PageTypes' => MultiValueField::class,
        'IncludePageParent' => 'Boolean',
    ];

    /**
     * @var array
     */
    private static $many_many = [
        'PageParents' => SiteTree::class,
    ];

    /**
     * @var array
     */
    private static $above_or_below_options = [
        'Above' => 'Above Page Elements',
        'Below' => 'Below Page Elements',
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
                    HeaderField::create('SettingsHeading', _t("{$this->ClassName}.Settings", 'Settings')),
                    TextField::create('Title')
                        ->setTitle(_t("{$this->ClassName}.Title", 'Title')),
                    MultiValueCheckboxField::create('PageTypes')
                        ->setTitle(_t("{$this->ClassName}.OnlyApplyToThesePageTypes", 'Only apply to these page types'))
                        ->setDescription(_t("{$this->ClassName}.OnlyApplyToThesePageTypesDescription",
                            'Selected Page Types will inherit this Element Set automatically. Leave all unchecked to apply to all page types.'))
                        ->setSource($this->pageTypeOptions()),
                    TreeMultiselectField::create('PageParents')
                        ->setTitle(_t("{$this->ClassName}.OnlyApplyToChildrenOfThesePages",
                            'Only apply to children of these Pages:'))
                        ->setSourceObject(SiteTree::class),
                    CheckboxField::create('IncludePageParent')
                        ->setTitle(_t("{$this->ClassName}.ApplyBlockSetToSelectedPageParentsAsWellAsChildren",
                            'Apply elemental set to selected page parents as well as children')),
                ]
            );

            if (!$this->exists()) {
                $fields->addFieldToTab('Root.Main', LiteralField::create('NotSaved',
                    "<p class='message warning'>" . _t("{$this->ClassName}.YouCanAddElementsToThisSetOnceYouHaveSavedIt",
                        'You can add Elements to this set once you have saved it for the first time') . '</p>'));
            }

            $fields->removeByName('Elements');
        });

        $fields = parent::getCMSFields();

        $elements = $fields->dataFieldByName('ElementalArea');

        if ($elements instanceof GridField) {
            $config = $elements->getConfig();
            $addElement = $config->getComponentByType(GridFieldAddNewMultiClass::class);
            $addElement = $addElement->setFragment('toolbar-header-right');

            $config->removeComponentsByType([
                GridFieldAddExistingAutocompleter::class,
                GridFieldAddNewMultiClass::class
            ])
                ->addComponent(new GridFieldAddExistingSearchButton('toolbar-header-left'))
                ->addComponent($addElement);
        }

        return $fields;
    }

    /**
     * @return mixed
     */
    public function getElementsCount()
    {
        return $this->ElementalArea()->Elements()->count();
    }

    /**
     * @return array
     */
    protected function pageTypeOptions()
    {
        $pageTypes = [];
        $classes = ArrayLib::valuekey(SiteTree::page_type_classes());

        unset($classes[VirtualPage::class]);
        unset($classes[ErrorPage::class]);
        unset($classes[RedirectorPage::class]);

        foreach ($classes as $class) {
            $pageTypes[$class] = $class::singleton()->i18n_singular_name();
        }

        asort($pageTypes);

        return $pageTypes;
    }

    /**
     * @return \SilverStripe\ORM\DataList
     */
    public function Pages()
    {
        $pages = SiteTree::get();
        $types = $this->PageTypes->getValue();
        if (count($types)) {
            $pages = $pages->filter('ClassName', $types);
        }

        $parents = $this->PageParents()->column();
        if (count($parents)) {
            $pages = $pages->filter('ParentID', $parents);
        }

        return $pages;
    }

    /**
     * @return string
     */
    public function Link()
    {
        return Controller::curr()->Link();
    }
}
