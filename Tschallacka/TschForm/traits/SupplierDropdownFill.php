<?php namespace Tschallacka\TschForm\Traits;
use \Tschallacka\Suppliers\Models\SupplierModel;
trait SupplierDropdownFill {
	public function getsupplierOptions() {
		$options = SupplierModel::all();
		$ret = [''=>''];
		foreach($options as $key) {
			$ret[$key->szSupplier] = $key->szSupplier.(!empty($key->szFaxNr) ? ' ('.$key->szFaxNr.')':'');
		}
		$options = null;
		return $ret;
	}
}
?>
