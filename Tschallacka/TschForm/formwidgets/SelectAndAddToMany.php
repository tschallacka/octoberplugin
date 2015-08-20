<?php 
namespace Tschallacka\TschForm\FormWidgets;

use Backend\Classes\FormWidgetBase;
use ApplicationException;
use Db;

/**
 *   The intention of this class is to allow easy insertion in to the belongstomany tables where things belong to eachother.
 *   The following implementation example assumes you have a belongsToMany relationship named "hotels"
 * 
 *   You'll need to add in your model the method onFormFIELDNAMEInject(Modelclass, session value)
 *   onFormHotelsInject($orig, $session=null) {
	     $this->hotels()->attach($orig);
 *   }
 *   And in fields.yaml you can use this to implement the correct list display and which relationfield.
 *   hotels:
 *      buttonlabel: Add Hotels
 *      type: selectandaddtomany
 *      relationfield: hotels
 *      notpossibleyet: Please save this thing if you wish to be able to add standard segments(CTRL + S)
 *      listconfig: ~/plugins/author/someplugin/controllers/hotelcontroller/config_list.yaml
 *

class SelectAndAddToMany extends FormWidgetBase {
	public $defaultAlias = 'selectandaddtomany';
    public function widgetDetails()
    {
        return [
            'name'        => 'Interactive Dropdown',
            'description' => 'Renders a ajax bases dropdownsearch.'
        ];
    }
    /**
     * @var array List definitions, keys for alias and value for configuration.
     */
    protected $listDefinitions;

    /**
     * @var string The primary list alias to use. Default: list
     */
    protected $primaryDefinition;

    /**
     * @var Backend\Classes\WidgetBase Reference to the list widget object.
     */
    protected $listWidgets = [];

    /**
     * @var WidgetBase Reference to the toolbar widget objects.
     */
    protected $toolbarWidgets = [];

    /**
     * @var WidgetBase Reference to the filter widget objects.
     */
    protected $filterWidgets = [];

    /**
     * {@inheritDoc}
     */
    protected $requiredProperties = ['listConfig'];

    /**
     * @var array Configuration values that must exist when applying the primary config file.
     * - modelClass: Class name for the model
     * - list: List column definitions
     */
    protected $requiredConfig = ['modelClass', 'list'];
    /**
     * Behavior constructor
     * @param Backend\Classes\Controller $controller
     */
    /**
     * Creates all the list widgets based on the definitions.
     * @return array
     */
    public function __construct($one, $two, $three) {
    	parent::__construct($one,$two,$three);
    	$this->makeList();
    	$this->bindToController();
    	
    	
    }
    public function makeLists()
    {
    	foreach ($this->listDefinitions as $definition => $config) {
    		$this->listWidgets[$definition] = $this->makeList($definition);
    	}
    
    	return $this->listWidgets;
    }
    
    public function onPaginate() {
    	return $this->listWidgets[$this->primaryDefinition]->onPaginate();
    }
    public function onRefresh() {
    	return $this->listWidgets[$this->primaryDefinition]->onRefresh();
    }
    public function onLoadSetup() {
    	return $this->listWidgets[$this->primaryDefinition]->onLoadSetup();
    }
    public function onApplySetup() {
    	return $this->listWidgets[$this->primaryDefinition]->onLoadSetup();
    }
    public function onSort() {
    	return $this->listWidgets[$this->primaryDefinition]->onSort();
    }
    public function onRelationClickViewList() {
    	//return $this->listWidgets[$this->primaryDefinition]->onRelationClickViewList();
    	return $this->onListItemClicked();
    }
    
    public function onListItemClicked() {
    	/** function to call in model */
    	$func = 'on'.ucfirst($this->alias).'Inject';
    	/** Load configuration */
    	$definition = $this->config->listconfig;
    	$listConfig = $this->makeConfig($definition, $this->requiredConfig);
    	/** Initialise blank foreign model */
    	$class = $listConfig->modelClass;
    	$model = new $class();
    	/** Get our id by primary key */
    	$id = post($model->primaryKey);
    	/** Load up the model with data */
    	$newmodel = $model->where($model->primaryKey,'=',$id)->get()->first();
    	/** Feed it to this instance(Model belonging to the calling controller */
    	$ret = $this->model->{$func}($newmodel,$this->sessionKey); 
    	/** Feed back primary key to the json parser */
    	if(isset($ret) && !empty($ret) && $ret !== null) {
    		$arr = $ret->toArray();
    		$arr['primaryKey']=$newmodel->primaryKey;
    		return json_encode($arr); 
    	}
    	else {
    		return json_encode(['yup'=>'did']);
    	}
    }
    public function makeList()
    {
    	
        $definition = $this->config->listconfig;
        $this->primaryDefinition = $definition;

        $listConfig = $this->makeConfig($definition, $this->requiredConfig);

        /*
         * Create the model
         */
        $class = $listConfig->modelClass;
        $model = new $class();
        
        /** This is needed so it can be accessed in the list config **/
        $listConfig->recordOnClick = 'var that=this;$(this).closest(\'form\').request(\''.$this->config->alias.'::onListItemClicked\',{data:{id:\':'.$model->primaryKey.'\'}}).done(function(data){ $(that).trigger(\'close.oc.popup\');window[\''.$this->alias.('handleClick').'\'](that,data);});';
        
        $model = $this->controller->listExtendModel($model, $definition);

        /*
         * Prepare the list widget
         */
        $columnConfig = $this->makeConfig($listConfig->list);
        $columnConfig->model = $model;
        $columnConfig->alias = $this->alias;

        /*
         * Prepare the columns configuration
         */
        $configFieldsToTransfer = [
            'recordUrl',
            'recordOnClick',
            'recordsPerPage',
            'noRecordsMessage',
            'defaultSort',
            'showSorting',
            'showSetup',
            'showCheckboxes',
            'showTree',
            'treeExpanded',
        ];

        foreach ($configFieldsToTransfer as $field) {
            if (isset($listConfig->{$field})) {
                $columnConfig->{$field} = $listConfig->{$field};
            }
        }

        /*
         * List Widget with extensibility
         */
        $widget = $this->makeWidget('Backend\Widgets\Lists', $columnConfig);

        $widget->bindToController();

        /*
         * Prepare the toolbar widget (optional)
         */
        if (isset($listConfig->toolbar)) {
            $toolbarConfig = $this->makeConfig($listConfig->toolbar);
            $toolbarConfig->alias = $widget->alias . 'Toolbar';
            $toolbarWidget = $this->makeWidget('Backend\Widgets\Toolbar', $toolbarConfig);
            $toolbarWidget->bindToController();
            $toolbarWidget->cssClasses[] = 'list-header';

            /*
             * Link the Search Widget to the List Widget
             */
            if ($searchWidget = $toolbarWidget->getSearchWidget()) {
                $searchWidget->bindEvent('search.submit', function () use ($widget, $searchWidget) {
                    $widget->setSearchTerm($searchWidget->getActiveTerm());
                    return $widget->onRefresh();
                });

                // Find predefined search term
                $widget->setSearchTerm($searchWidget->getActiveTerm());
            }

            $this->toolbarWidgets[$definition] = $toolbarWidget;
        }

        /*
         * Prepare the filter widget (optional)
         */
        if (isset($listConfig->filter)) {
            $widget->cssClasses[] = 'list-flush';

            $filterConfig = $this->makeConfig($listConfig->filter);
            $filterConfig->alias = $widget->alias . 'Filter';
            $filterWidget = $this->makeWidget('Backend\Widgets\Filter', $filterConfig);
            $filterWidget->bindToController();

            /*
             * Filter the list when the scopes are changed
             */
            $filterWidget->bindEvent('filter.update', function () use ($widget, $filterWidget) {
                $widget->addFilter([$filterWidget, 'applyAllScopesToQuery']);
                return $widget->onRefresh();
            });

            /*
             * Extend the query of the list of options
             */
            $filterWidget->bindEvent('filter.extendQuery', function($query, $scope) {
                $this->controller->listFilterExtendQuery($query, $scope);
            });

            // Apply predefined filter values
            $widget->addFilter([$filterWidget, 'applyAllScopesToQuery']);

            $this->filterWidgets[$definition] = $filterWidget;
        }
        $this->listWidgets[$definition] = $widget;
        return $widget;
    }
    /**
     * Renders the widget collection.
     * @param  string $definition Optional list definition.
     * @return string Rendered HTML for the list.
     */
    public function listRender($definition = null)
    {
    	//ob_clean();
    	//dump($this);
    	//return ob_get_clean();
    	if (!count($this->listWidgets)) {
    		$this->makeList();
    		//throw new ApplicationException('form is not ready');
    	}
    
    	if (!$definition || !isset($this->listDefinitions[$definition])) {
    		$definition = $this->primaryDefinition;
    	}
    
    	$collection = [];
    
    	if (isset($this->toolbarWidgets[$definition])) {
    		$collection[] = $this->toolbarWidgets[$definition]->render();
    	}
    
    	if (isset($this->filterWidgets[$definition])) {
    		$collection[] = $this->filterWidgets[$definition]->render();
    	}
    
    	$collection[] = $this->listWidgets[$definition]->render();
    
    	return implode(PHP_EOL, $collection);
    }
    /**
     * Refreshes the list container only, useful for returning in custom AJAX requests.
     * @param  string $definition Optional list definition.
     * @return array The list element selector as the key, and the list contents are the value.
     */
    public function listRefresh($definition = null)
    {
    	if (!count($this->listWidgets)) {
    		$this->makeLists();
    	}
    
    	if (!$definition || !isset($this->listDefinitions[$definition])) {
    		$definition = $this->primaryDefinition;
    	}
    
    	return $this->listWidgets[$definition]->onRefresh();
    }
    
    /**
     * Returns the widget used by this behavior.
     * @return Backend\Classes\WidgetBase
     */
    public function listGetWidget($definition = null)
    {
    	if (!$definition) {
    		$definition = $this->primaryDefinition;
    	}
    
    	return array_get($this->listWidgets, $definition);
    }

    public function prepareVars()
    {
    	
    	//$this->vars['value'] = $this->model->{$this->fieldName};
    }
	public function index() {
		$this->makeLists();
	}
	public function onContentPopup() {
		$value = $this->listRender();
		//return $value;
		return '<div style="width:100%;height:100%;overflow:scroll;padding:10px;">'.$value.'</div>';
		//return $this->listWidgets[$definition];
	}
    public function render() {
    	$this->addJs('/modules/system/assets/js/framework.extras.js');
    	$this->prepareVars();
    	if(!empty($this->model->{$this->model->primaryKey})) {
    		return $this->makePartial('form');
    	}
    	else {
    		return $this->makePartial('nolinkingpossibleyet');
    	}
    	
    }
}