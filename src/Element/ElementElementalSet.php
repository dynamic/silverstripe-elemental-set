<?php

namespace Dynamic\ElementalSets\Element;

use DNADesign\Elemental\Models\BaseElement;
use Dynamic\ElementalSets\Model\ElementalSet;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Class ElementElementalSet
 * @package Dynamic\ElementalSets\Element
 */
class ElementElementalSet extends BaseElement
{
    /**
     * @var string
     */
    private static $icon = 'font-icon-block-content';

    /**
     * @var string
     */
    private static $table_name = 'ElementElementalSet';

    /**
     * @var array
     */
    private static $has_one = [
        'ElementalSet' => ElementalSet::class,
    ];

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            //$fields->addFieldToTab('Root.Main', )
        });

        return parent::getCMSFields();
    }

    /**
     * @return DBHTMLText
     */
    public function getSummary()
    {
        /*$count = $this->Panels()->count();
        $label = _t(
            BaseElement::class . '.PLURALS',
            '{count} Element|{count} Elements',
            [ 'count' => $count ]
        );//*/
        return DBField::create_field('HTMLText', '')->Summary(20);
    }

    /**
     * @return array
     */
    protected function provideBlockSchema()
    {
        $blockSchema = parent::provideBlockSchema();
        $blockSchema['content'] = $this->getSummary();

        return $blockSchema;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return _t(__CLASS__ . '.BlockType', 'Element Set');
    }

}
