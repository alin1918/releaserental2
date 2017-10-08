<?php

namespace SalesIgniter\Rental\Model\Config;

class ThemeStyle implements \Magento\Framework\Option\ArrayInterface
{

    const DEFAULT_STYLE = 'default';
    const START_STYLE = 'start';
    const LIGHTNESS_STYLE = 'lightness';
    const DARKNESS_STYLE = 'darkness';
    const SUNNY_STYLE = 'sunny';
    const REDMOND_STYLE = 'redmond';
    const LEFROG_STYLE = 'lefrog';
    const EXCITEBIKE_STYLE = 'excitebike';
    const SWANKYPURSE_STYLE = 'swankypurse';
    const PEPPERGRINDER_STYLE = 'peppergrinder';
    const BLITZER_STYLE = 'blitzer';
    const CUPERTINO_STYLE = 'cupertino';
    const OVERCAST_STYLE = 'overcast';
    const BLACKTIE_STYLE = 'blacktie';
    const DOTLOVE_STYLE = 'dotlove';
    const HOTSNEAKS_STYLE = 'hotsneaks';
    const CUSTOM1_STYLE = 'custom1';
    const CUSTOM2_STYLE = 'custom2';

    public function toOptionArray()
    {
        return array(
            array('value' => self::DEFAULT_STYLE, 'label' => __('Default')),
            array('value' => self::START_STYLE, 'label' => __('Start')),
            array('value' => self::LIGHTNESS_STYLE, 'label' => __('Lightness')),
            array('value' => self::DARKNESS_STYLE, 'label' => __('Darkness')),
            array('value' => self::SUNNY_STYLE, 'label' => __('Sunny')),
            array('value' => self::REDMOND_STYLE, 'label' => __('Redmond')),
            array('value' => self::LEFROG_STYLE, 'label' => __('Le Frog')),
            array('value' => self::EXCITEBIKE_STYLE, 'label' => __('Excite Bike')),
            array('value' => self::SWANKYPURSE_STYLE, 'label' => __('Swanky Purse')),
            array('value' => self::PEPPERGRINDER_STYLE, 'label' => __('Pepper Grinder')),
            array('value' => self::BLITZER_STYLE, 'label' => __('Blitzer')),
            array('value' => self::CUPERTINO_STYLE, 'label' => __('Cupertino')),
            array('value' => self::OVERCAST_STYLE, 'label' => __('Overcast')),
            array('value' => self::BLACKTIE_STYLE, 'label' => __('Black Tie')),
            array('value' => self::DOTLOVE_STYLE, 'label' => __('Dot Love')),
            array('value' => self::HOTSNEAKS_STYLE, 'label' => __('Hot Sneaks')),
            array('value' => self::CUSTOM1_STYLE, 'label' => __('Custom Style 1')),
            array('value' => self::CUSTOM2_STYLE, 'label' => __('Custom Style 2')),
        );
    }
}
