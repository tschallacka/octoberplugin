<?php 
namespace Tschallacka\TschForm\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Db;

class WeekDaySelector extends FormWidgetBase {
	public $defaultAlias = 'weekdayselector';
    public function widgetDetails()
    {
        return [
            'name'        => 'Week day selector',
            'description' => 'Renders a week day selector.'
        ];
    }
    public function prepareVars()
    {
    	$this->vars['name'] = $this->formField->getName();
    	$this->vars['value'] = $this->model->{$this->fieldName};
    }

    public function render() {
    	$this->prepareVars();
    	return $this->makePartial('form');
    	
    }
}