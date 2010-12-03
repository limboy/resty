<?php
return array(
	'example' => array(
		'get' => array(
			'filters' => array(
				'id' => array(
					'trim' => null,
				),
			),
			'rules' => array(
				'id' => array(
					'not_empty' => null,
					'min_length' => array(2),
					'digit' => null,
				),
			),
		),
	),
);
