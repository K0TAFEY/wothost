<?php
return array(
	'jquery'=>array(
		'basePath'=>'ext.clientscript.scripts',
		'js'=>array('jquery-1.11.0.min.js '),
	),
	'jquery.ui'=>array(
		'basePath'=>'ext.clientscript.jquery-ui',
		'js'=>array('jquery-ui-1.10.3.custom.min.js'),
		'css'=>array('jquery-ui-1.10.3.custom.min.css '),
		'depends'=>array('jquery'),
	),
	'font-awesome'=>array(
		'basePath'=>'ext.clientscript.font-awesome',
		'css'=>array('css/font-awesome.min.css'),
	),
	'bootstrap'=>array(
		'basePath'=>'ext.clientscript.bootstrap',
		'js'=>array('js/bootstrap.min.js'),
		'css'=>array('css/bootstrap.min.css'),
		'depends'=>array('font-awesome', 'jquery.ui'),
	),
	'bootstrap-hover-dropdown'=>array(
		'basePath'=>'ext.clientscript.bootstrap-hover-dropdown',
		'js'=>array('bootstrap-hover-dropdown.min.js'),
		'depends'=>array('bootstrap'),
	),
	'jquery-slimscroll'=>array(
		'basePath'=>'ext.clientscript.jquery-slimscroll',
		'js'=>array('jquery.slimscroll.min.js'),
		'depends'=>array('jquery'),
	),
	'jquery-migrate'=>array(
		'basePath'=>'ext.clientscript.scripts',
		'js'=>array('jquery-migrate-1.2.1.min.js'),
		'depends'=>array('jquery'),
	),
	'jquery-blockui'=>array(
		'basePath'=>'ext.clientscript.scripts',
		'js'=>array('jquery.blockui.min.js'),
		'depends'=>array('jquery'),
	),
	'jquery-peity'=>array(
		'basePath'=>'ext.clientscript.scripts',
		'js'=>array('jquery.peity.min.js'),
		'depends'=>array('jquery'),
	),
	'jquery-cookie'=>array(
			'basePath'=>'ext.clientscript.scripts',
			'js'=>array('jquery.cokie.min.js'),
			'depends'=>array('jquery'),
	),
	'uniform'=>array(
		'basePath'=>'ext.clientscript.uniform',
		'js'=>array('jquery.uniform.min.js'),
		'css'=>array('css/uniform.default.min.css'),
	),
	'simple-line-icons'=>array(
			'basePath'=>'ext.clientscript.simple-line-icons',
			'css'=>array('simple-line-icons.min.css'),
	),
	'data-tables'=>array(
		'basePath'=>'ext.clientscript.data-tables',
		'js'=>array('media/js/jquery.dataTables.min.js','bootstrap/3/dataTables.bootstrap.js'),
		'css'=>array('bootstrap/3/dataTables.bootstrap.css','font-awesome/dataTables.fontAwesome.css'),
		'depends'=>array('jquery', 'font-awesome', 'bootstrap'),
	),
	'breakpoints'=>array(
		'basePath'=>'ext.clientscript.plugins.breakpoints',
		'js'=>array('breakpoints.js'),
		// 'css'=>array('css/font-awesome.min.css'),
		'depends'=>array('jquery'),
	),
	'flot'=>array(
		'basePath'=>'ext.conquer.flot',
		'js'=>array(
				'excanvas.min.js',
				'jquery.flot.min.js',
				'jquery.flot.time.min.js',
			//	'jquery.flot.crosshair.js',
			//	'jquery.flot.fillbetween.js',
			//	'jquery.flot.image.js',
			//	'jquery.flot.navigate.js',
			//	'jquery.flot.pie.js',
			//	'jquery.flot.resize.js',
			//	'jquery.flot.selection.js',
			//	'jquery.flot.stack.js',
			//	'jquery.flot.symbol.js',
			//	'jquery.flot.threshold.js'
		),
		'depends'=>array('jquery'),
	),
	'jqGrid'=>array(
			'basePath'=>'ext.jqGrid-master',
			'js'=>array('js/i18n/grid.locale-en.js','js/minified/jquery.jqGrid.min.js'),
			'css'=>array('css/ui.jqgrid.css', 'css/bootstrap.css'),
			'depends'=>array('jquery'),
	),
	'wysihtml'=>array(
		'basePath'=>'ext.clientscript.bootstrap-wysihtml5',
		'js'=>array('wysihtml5-0.3.0.js','bootstrap-wysihtml5.js'),
		'css'=>array('wysiwyg-color.css','bootstrap-wysihtml5.css'),
		'depends'=>array('jquery'),
	),
);