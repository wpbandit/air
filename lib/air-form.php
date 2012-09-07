<?php

/**
	Form Library :: Air Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 2.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2012 WPBandit
	Jermaine Marée

		@package AirForm
		@version 1.1
**/

//! Creates form fields
class AirForm {

	/**
		Button
			@return string
			@param $attrs array
			@public
	**/
	static function button(array $attrs) {
		// Default attributes
		$defaults = array(
			'type'	=> 'button',
			'value'	=> __('Save Changes','air')
		);

		// Parse attributes and merge with $defaults
		$attrs = wp_parse_args($attrs,$defaults);

		// Create field
		return '<input'.air_attrs($attrs).'/>';
	}

	/**
		Checkbox
			@return string
			@param $attrs array
			@param $value string
			@param $std string
			@public
	**/
	static function checkbox(array $attrs,$value,$std='1') {
		// Default attributes
		$defaults = array(
			'name'	=> '',
			'type'	=> 'checkbox',
			'value'	=> $std
		);
		// Parse args
		$attrs = wp_parse_args($attrs,$defaults);
		// Checked ?
		if ( $std==$value ) { $attrs['checked'] = 'checked'; }
		// Create field
		$field = '<input'.air_attrs($attrs).'/>';
		return $field;
	}

	/**
		Radio
			@return string
			@param $attrs array
			@param $value string
			@param $selected string
			@public
	**/
	static function radio(array $attrs,$value,$selected) {
		// Default attributes
		$defaults = array(
			'name'	=> '',
			'type'	=> 'radio',
			'value'	=> $value
		);
		// Parse args
		$attrs = wp_parse_args($attrs,$defaults);
		// Checked ?
		if ( $selected == $value ) { $attrs['checked'] = 'checked'; }
		// Create field
		$field = '<input' . air_attrs($attrs) . '/>';
		return $field;
	}

	/**
		Select
			@return string
			@param $attrs array
			@param $value string
			@param $options array
			@public
	**/
	static function select(array $attrs,$value,array $options) {
		// Default attributes
		$defaults = array('name'=>'');
		// Parse args
		$attrs = wp_parse_args($attrs,$defaults);
		// Create field
		$field = '<select' . air_attrs($attrs) . '>';
		// Build options
		foreach ( $options as $ovalue=>$oname ) {
			// Option attributes
			$oattrs = array('value'=>$ovalue);
			if ( $value==$ovalue ) { $oattrs['selected'] = 'selected'; }
			// Option
			$field .= '<option' . air_attrs($oattrs) . '>' . $oname . '</option>';
			// Reset $oattrs
			unset($oattrs);
		}
		$field .= '</select>';
		return $field;
	}

	/**
		Text
			@return string
			@param $attrs array
			@param $value string
			@public
	**/
	static function text(array $attrs,$value='') {
		// Default attributes
		$defaults = array(
			'name'	=> '',
			'type'	=> 'text',
			'value'	=> $value
		);
		// Parse args
		$attrs = wp_parse_args($attrs,$defaults);
		// Create field
		$field = '<input' . air_attrs($attrs) . '/>';
		return $field;
	}

	/**
		Textarea
			@return string
			@param $attrs array
			@param $value string
			@public
	**/
	static function textarea(array $attrs,$value='') {
		// Default attributes
		$defaults = array('name'=>'');
		// Parse args
		$attrs = wp_parse_args($attrs,$defaults);
		// Create field
		$field = '<textarea' . air_attrs($attrs) . '>' . $value . '</textarea>';
		return $field;
	}

}
