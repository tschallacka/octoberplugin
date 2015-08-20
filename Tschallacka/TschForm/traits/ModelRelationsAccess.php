<?php namespace Tschallacka\TschForm\Traits;
trait ModelRelationsAccess {
	public function isRelation($name) {
		
		return array_key_exists ($name, $this->hasOne) 
							|| array_key_exists ($name, $this->hasMany) 
							|| array_key_exists ($name, $this->attachOne) 
							|| array_key_exists ($name, $this->attachMany)
							|| array_key_exists ($name, $this->hasManyThrough)
							|| array_key_exists ($name, $this->relations)
							|| array_key_exists ($name, $this->belongsTo)
							|| array_key_exists ($name, $this->belongsToMany) 
							|| array_key_exists ($name, $this->morphTo)
							|| array_key_exists ($name, $this->morphOne)
							|| array_key_exists ($name, $this->morphMany);
	}
	public function getRelationFromKey($name) {
		if(array_key_exists ($name, $this->hasOne)) {
			return $this->hasOne[$name];
		}
		if(array_key_exists ($name, $this->attachOne)) {
			return $this->attachOne[$name];
		}
		if(array_key_exists ($name, $this->attachMany)) {
			return $this->attachMany[$name];
		}
		if(array_key_exists ($name, $this->hasManyThrough)) {
			return $this->hasManyThrough[$name];
		}
		if(array_key_exists ($name, $this->relations)) {
			return $this->relations[$name];
		}
		//traceLog($name);
		//traceLog($this->belongsTo); 
		if(array_key_exists ($name, $this->belongsTo)) {
			return $this->belongsTo[$name];
		}
		if(array_key_exists ($name, $this->belongsToMany)) {
			return $this->belongsToMany[$name];
		}
		if(array_key_exists ($name, $this->morphTo)) {
			return $this->morphTo[$name];
		}
		if(array_key_exists ($name, $this->morphOne)) {
			return $this->morphOne[$name];
		}
		if(array_key_exists ($name, $this->morphMany)) {
			return $this->morphMany[$name];
		}
		
		
	}   
}
?>
