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
    public function onSearchFieldExpand() {
    	/** First check if searchkey has been defined */
    	if(!empty(post('searchkey'))) {
    		/** No nulls please. This is supposed to be a full model qualifier
    		 * A class will be instantiad from this.
    		 **/
    		$searchkey = post('searchkey');
    		if($loc = strpos($searchkey,'(')) {
    			$searchkey = substr($searchkey,0,$loc);
    		}
    		$searchin = $this->config->modelToSearchIn;
    		$searchfield = $this->config->fieldToSearchIn;
	    	//$searchin = ''.post('searchin');
	    	if(!empty($searchin)) {
	    		/** Instantiate the model capt'n **/
		    	$model = new $searchin();
		    	$key = isset($this->config->keyField) ? $this->config->keyField : $model->primaryKey;
		    	$ret = [];
		    	/** Search in this, but first make it safe(SQL server safe) **/
		    	//$searchfield = str_replace(']','',''.post('searchfield'));
		    	//$list = Db::connection($model->connection)->select('select tmp.field from (SELECT lower(['.$searchfield.']) as [field] FROM ['.$model->table.']) tmp WHERE field LIKE ? GROUP BY field',[strtolower(post('searchkey')).'%']);
		    	$list = $model->where($searchfield,'like',strtolower($searchkey).'%')->get();
		    	//$list = Db::connection($model->connection)->select('select ['.$searchfield.'] as field ,['.$key.'] as accesskey FROM ['.$model->table.'] WHERE ['.$searchfield.'] LIKE ?',[strtolower(post('searchkey')).'%']);
		    	$model = null;		
		    	//		$model->table)->(Db::raw('lower([]) as [field] '));
		    	/** Return the results **/
		    	foreach($list as $item) {
		    		$ret[] = ['id' => ucfirst($item->{$key}),
		    						'text' => ucfirst($item->{$searchfield}).$this->composeText($item),
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
    private function composeText($item) {
    	$str = "(";
    	$first = true;
    	$fields = $this->config->extrafields;
    	foreach($fields as $field => $value) {
    		foreach($value as $lost => $prop) {
    			//$str.= var_export($item,true);
	    		if(isset($prop['isRelation']) && $prop['isRelation']===true) {
	    			if($first) {
	    				$first = false;
	    			}
	    			else {
	    				$str .= ',';
	    			}
	    			$str .= $item->{$prop['valueVia']}->{$prop['relatedValueFrom']} ;
	    			
	    		}
    		}
    	}
    	return $str === '(' ? '' : $str.')';
    }
    public function prepareVars()
    {
    	$searchin = $this->config->modelToSearchIn;
    	$searchfield = $this->config->fieldToSearchIn;
    	//$searchin = ''.post('searchin');
    	if(!empty($searchfield)) {
    		/** Instantiate the model capt'n **/
    		$this->vars['translatedvalue'] = $this->model->{$this->formField->fieldName}->{$searchfield}.$this->composeText($this->model->{$this->fieldName}->first());
    	}
    	$this->vars['name'] = $this->formField->getName();
    	$this->vars['value'] = $this->model->{$this->fieldName};
    	
    	
    }

    public function render() {
    	$this->prepareVars();
    	return $this->makePartial('form');
    	
    }
}