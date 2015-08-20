<?php 
namespace Tschallacka\TschForm\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Db;

class AjaxSearchDropdownTsch extends FormWidgetBase {
	public $defaultAlias = 'relaxedsearch';
    public function widgetDetails()
    {
        return [
            'name'        => 'Interactive Dropdown',
            'description' => 'Renders a ajax bases dropdownsearch.'
        ];
    }
    public static function onSearchFieldExpand() {
    	/** First check if searchkey has been defined */
    	if(!empty(post('searchkey'))) {
    		/** No nulls please. This is supposed to be a full model qualifier
    		 * A class will be instantiated from this.
    		 **/
	    	$searchin = ''.post('searchin');
	    	if(!empty($searchin)) {
	    		/** Instantiate the model capt'n **/
		    	$model = new $searchin();
		    	$ret = [];
		    	/** Search in this, but first make it safe(SQL server safe) **/
		    	$searchfield = str_replace(']','',''.post('searchfield'));
		    	$list = Db::connection($model->connection)->select('select tmp.field from (SELECT lower(['.$searchfield.']) as [field] FROM ['.$model->table.']) tmp WHERE field LIKE ? GROUP BY field',[strtolower(post('searchkey')).'%']);
		    	$model = null;		
		    	//		$model->table)->(Db::raw('lower([]) as [field] '));
		    	/** Return the results **/
		    	foreach($list as $item) {
		    		$ret[] = ['id' => ucfirst($item->field),
		    						'text' => ucfirst($item->field),
		    				];
		    	}
		    	/** Just to be sure we output something proper **/
		    	if(count($ret) > 0) {
		    		return json_encode($ret);
		    	}
	    	}
    	}
    	return '[]';
    	
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