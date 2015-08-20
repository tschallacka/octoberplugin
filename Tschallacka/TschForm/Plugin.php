<?php namespace Tschallacka\TschForm;

use App;
use Event;
use Backend;
use System\Classes\PluginBase;

/**
 * rjsliders Plugin Information File
 */
class Plugin extends PluginBase
{

	public function registerFormWidgets() {
		return [
				'Tschallacka\TschForm\FormWidgets\AjaxSearchDropdownTsch'	=> [
						'label' => 'Search Field',
						'code' => 'relaxedsearchtsch'
			
				],
				'Tschallacka\TschForm\FormWidgets\WeekDaySelector'	=> [
						'label' => 'Week Day Selector',
						'code' => 'weekdayselector'
		
				],
				'Tschallacka\TschForm\FormWidgets\TschDatePicker'	=> [
						'label' => 'Tsch Date Picker',
						'code' => 'tschdatepicker'
				
				],
				'Tschallacka\TschForm\FormWidgets\SelectAndAddToMany'	=> [
						'label' => 'Select and AddToMany',
						'code' => 'selectandaddtomany'
				
				],
		];
	}
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'EEB',
            'description' => 'Tsch Reizen EEB',
            'author'      => 'Michael Dibbets',
            'icon'        => 'icon-play-circle-o',
        ];
    }

}
