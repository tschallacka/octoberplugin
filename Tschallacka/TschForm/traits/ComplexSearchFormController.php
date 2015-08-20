<?php namespace Tschallacka\TschForm\Traits;
use Input;
use Session;
use DB;
/**
 * Use this in a controller to give yourself easy to access
 * to a search form of all the fields in fields.yaml
 * and results formatted according to columns.yaml
 * 
 * Only requirements are that you add a partial _search.htm similarly structured to update.htm
 * and a partial _searchresults.htm similar to index.htm
 * @author michael dibbets
 *
 */
trait ComplexSearchFormController {
	public function onClearSarch() {
    	Session::forget($this->uniqueIdentifierForSearch);
    	return $this->listRefresh();
    
    }
    public function search() {
    	$this->create('backend');    	
    	return $this->makePartial('search',['form'=>$this]);
    	 
    }
    public function listExtendQueryBefore($query) {
    	/**
    	 * Load configuration file
    	 */
    	$this->loadConfig();
    	/**
    	 * Get a model instance
    	 */
    	$model = new $this->loadedConfig->modelClass();
    	/**
    	 * Get session data
    	 */
    	$this->goforth = Session::get($this->uniqueIdentifierForSearch);
    	if(is_array($this->goforth)) {
    		$c=0;
    		foreach($this->goforth as $key => $value) {
    			$c++;
    			/**
    			 * If it has a method that makes our life easier
    			 * to determine if there are relations
    			 */
    			if(method_exists($model,'isRelation')) { 
    				/**
    				 * if it's not a relation do a normal like search
    				 */
    				if(!$model->isRelation($key)) {
    					if(!empty(trim($value))) {
    						$query->where($key,'like','%'.$value.'%');
    					}
    				}
    				/**
    				 * It is a relation, lets go funky wunky
    				 */
    				else {
    					$relation = $model->getRelationFromKey($key);
    					/**
    					 * We really have a relation don't we!
    					 */
    					if(!empty($relation)) {
    						/** first key contains the model path */
	    					$relmodel = new $relation[0]();
	    					/**
	    					 * Is there isn't an "other" key we need to go funkeh
	    					 */
	    					if(!isset($relation['otherKey'])) {
	    						$query->join($relmodel->getTable(),
	    								$model->getTable().'.'.$relation['key'],
	    								'=',
	    								$relmodel->getTable().'.'.$relmodel->getKeyName()
	    						)->where($relmodel->getTable().'.'.$relmodel->getKeyName(),'like','%'.$value.'%');
	    					}
	    					/**
	    					 * Other key? Let's do this
	    					 */
	    					else {
	    						$query->join($relmodel->getTable(),
	    										$relmodel->getTable().'.'.$relation['otherKey'],
	    										'=',
	    										$model->getTable().'.'.$relation['key']
	    								)->where($relmodel->getTable().'.'.$relation['otherKey'],'like','%'.$value.'%');
	    					}
    					}
    				}
    			}
    			else {
    				$query->where($key,'like','%'.$value.'%');
    			}
    		}
    	}
    }
    public function loadConfig() {
    	if(!isset($this->loadedConfig)) {
    		$this->loadedConfig = $this->makeConfig($this->formConfig);
    	}
    }
    protected $goforth = null;
    public function onSearch() {
    	$this->goforth = [];    	
    	$this->loadConfig();
    	/** get the post input based on model class so we don't have to rely on hard coded values **/
    	$this->goforth = Input::all()[substr($this->loadedConfig->modelClass,strrpos($this->loadedConfig->modelClass,'\\')+1)];
    	Session::set($this->uniqueIdentifierForSearch,$this->goforth);
    	return [];
    }
}