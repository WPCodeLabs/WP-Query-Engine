jQuery( function($) {
    'use strict';

    var _create = function() {

        $.each( $('.wpcl_rules_wrapper'), function (index, el) {
            // // Create jquery object
            var $el = $( el );
            // // Exclude hidden objects
            if (!$el.is( ":visible" ) ) {
                return null;
            }
            // // Exclude objects that have already been created
            if ($el.hasClass('wpcl-js') ) {
                return null;
            }
            // Finally create the new object
            return new acfField( $el );
        });
    };
    // run initial create
    _create();
    // Add action to do mapping on append
    acf.add_action('append', _create);

    function acfField( $el ) {

        var $rules, template, count, $button, $container, $notice;

        var _add = function (event) {
            event.preventDefault();
            // Create new rule
            var newRule = _createRule($(template.replace(new RegExp('{{INDEX}}', 'gi'), ++count)));
            // Append to our list of rules
            $container.append(newRule);
            // Push into our array of rules
            $rules.push(newRule);
            // hide notice
            $notice.addClass( 'hidden' );
        };

        var _remove = function (event) {
            event.preventDefault();
            // Flag for finding the index
            var found = _getRule(event.data.rule);
            // Bail if we didn't find the element
            if (found === false) {
                return;
            }
            // Remove from DOM
            $rules[found].remove();
            // Remove from rules
            $rules.splice(found, 1);
            // Display notice
            if( $rules.length === 0 ) {
            	$notice.removeClass( 'hidden' );
            }
        };

        var _update = function (event) {
            // Flag for finding the index
            var found = _getRule(event.data.rule);
            // Bail if we didn't find the element
            if (found === false) {
                return;
            }
            // // Get all of the possible choice options
            var data = wpcl_query_engine_acf.query_options[$rules[found].$type.val()].choices;
            // Remove previous options
            $rules[found].$selection.find('option').remove();
            // Add all new selectors
            for (var option in data) {
                $rules[found].$selection.append('<option value="' + data[option] + '">' + data[option] + '</option>');
            }
            if ( $rules[found].$type.val() === 'post_type' ) {
                // Update disabled
                $rules[found].$operator.prop('disabled', 'disabled' );
                // Update value
                $rules[found].$operator.val( 'IN' );
            } else {
                $rules[found].$operator.removeAttr( 'disabled' );
            }
        };

        var _getRule = function (rule) {
            // Flag for finding the index
            var found = false;
            // Get the index of the rule
            for (var i = 0; i < $rules.length; i++) {
                if (rule.is($rules[i])) {
                    found = i;
                    break;
                }
            }
            return found;
        };

        var _createRule = function ($rule) {
            // Add some elements
            $rule.$button = $rule.find('[data-action="remove"]');
            $rule.$type = $rule.find('.type');
            $rule.$selection = $rule.find('.selection');
            $rule.$operator = $rule.find('.operator');
            // Bind some events
            $rule.$button.on('click', { rule: $rule }, _remove);
            $rule.$type.on('change', { rule: $rule }, _update);
            // Return our rule
            return $rule;
        };

        var _init = function () {

            // Add a class to flag that js has been instantiated
            $el.addClass( 'wpcl-js' );
            // set the rule container
            $container = $el.find('.rule-container');
            // set the template for new rules
            template = $el.find('#wpcl-field-template').html();
            // set the add button
            $button = $el.find('[data-action="add"]');
            // Set the notice box
            $notice = $el.find( '.wpcl-notice' );
            // map all of the existing rules
            $rules = $.map($el.find('.wpcl_rule_fieldset'), function (rule) {
                return _createRule($(rule));
            });
            // Bind some events
            $button.on('click', _add);
            // Set initial rulecount
            count = $rules.length;
            // return $el;
            return $el;
        };

        return _init();
    }

});