<?php
namespace tool_pluginskel\local\skel;

defined('MOODLE_INTERNAL') || die();

class privacy_file extends php_internal_file {
	public function set_provider_name($name) {
		if (empty($this->data)) {
			throw new exception('Skeleton data not set');
		}

		if (!empty($this->data['privacy']['provider_name'])) {
			throw new exception("Privacy provider {$name} already set");
		}

		$this->data['privacy']['provider_name'] = $name;
	}

	function set_file_namespace($namespace) {
		if (empty($this->data)) {
			throw new exception('Skeleton data not set');
		}
		$this->data['privacy']['namespace'] = 'namespace '. $namespace;
	}

	public function set_data(array $data) {
	    parent::set_data($data);
    }

}
