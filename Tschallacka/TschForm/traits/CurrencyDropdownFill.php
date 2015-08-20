<?php namespace Tschallacka\TschForm\Traits;
use \Tschallacka\ExchangeRate\Models\ExchangeModel;
trait CurrencyDropdownFill {
	public function getmunteenheidOptions() {
		$options = ExchangeModel::all();
		$ret = [''=>''];
		foreach($options as $key) {
			$ret[$key->szMunteenheid] = $key->szMunteenheid;
		}
		$options = null;
		return $ret;
	}
}
?>
