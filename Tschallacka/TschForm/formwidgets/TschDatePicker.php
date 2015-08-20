<?php 
namespace Tschallacka\TschForm\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Db;

class TschDatePicker extends FormWidgetBase {
	public $defaultAlias = 'tschdatepicker';
    public function widgetDetails()
    {
        return [
            'name'        => 'Tsch Date Picker',
            'description' => 'Renders a datepicker custom for Tsch Reizen.'
        ];
    }
    public function prepareVars()
    {
    	$this->vars['name'] = $this->formField->getName();
    	$this->vars['value'] = $this->model->{$this->fieldName};
    }

    public function render() {
    	$this->addJs('/plugins/Tschallacka/TschForm/formwidgets/tschdatepicker/assets/js/bootstrap-datepicker.js');
    	$this->addJs('/plugins/Tschallacka/TschForm/formwidgets/tschdatepicker/assets/js/tschdatepicker.js');
    	$this->addCss('/plugins/Tschallacka/TschForm/formwidgets/tschdatepicker/assets/css/bootstrap-datepicker.css');
    	
    	$this->prepareVars();
    	return $this->makePartial('form');
    	
    }
}