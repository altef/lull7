<?php
namespace altef\keyvaluestore;


class LocalSoft extends Local {
	public function get($key = null) {
		try {
			return parent::get($key);
		} catch (\Exception $e) {
			return null;
		}
	}
}

?>