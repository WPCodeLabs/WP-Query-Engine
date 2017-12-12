jQuery( function( $ ) {
	'use strict';

	var $widgets;

	var init = function() {
		$widgets = $.map( $( '#widgets-right .wpcl_ruleset_wrapper' ), function( el ) {
			return new Widget( $( el ) );
		});
	};

	( function() {
		init();
		$( document ).on( 'widget-updated', init );
		$( document ).on( 'widget-added', init );
	})();

	function Widget( $el ) {

		var template, $rules, $wrapper, ruledex;

		var createRule = function( event ) {
			// Prevent the button click from reloading page
			event.preventDefault();
			// Create an object from the new rule
			var $new_rule = $( template.replace( new RegExp( '{{INDEX}}', 'gi'), ruledex++ ) );
			// Append new rule to DOM
			bindEvents( $new_rule ).hide().appendTo( $wrapper ).fadeIn( 300 );

		};

		var removeRule = function( event ) {
			event.preventDefault();
			event.data.obj.hide( 300 ).remove();
		};

		var bindEvents = function( $obj ) {
			// Grab our two select elements to cache them
			var $type     = $obj.find( '.rule_type' );
			var $selector = $obj.find( '.rule_selector' );
			$type.on( 'change', { type : $type, selector : $selector }, changeSelector );
			$obj.find( '.remove_rule' ).on( 'click', { obj : $obj }, removeRule );
			return $obj;
		};

		var changeSelector = function( event) {
			if( typeof wpcl_query_engine_admin[ event.data.type.val() ] !== 'undefined' ) {
				// Cache the value
				var data = wpcl_query_engine_admin[ event.data.type.val() ];
				// Remove previous options
				event.data.selector.find( 'option' ).remove();
				// Add all new selectors
				for( var option in data ) {
					event.data.selector.append( '<option value="' + data[option] + '">' + data[option] + '</option>' );
				}
			}
		};

		(function(){
			// Do some initial DOM Caching
			$wrapper = $el.find( '.rulesets' );
			template = $el.find( '#ruleset_template' ).html();
			ruledex  = parseInt( $wrapper.data( 'ruleset-index' ) );
			// Get index of all current rules
			$rules = $.map( $wrapper.find( '.ruleset' ), function( obj ) {
				return bindEvents( $( obj ) );
			});
			// Bind add rule event
			$el.on( 'click', '.add_rule', createRule );

		})();
	}

});