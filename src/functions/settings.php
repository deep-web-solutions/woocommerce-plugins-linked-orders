<?php

use DWS_LO_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;

defined( 'ABSPATH' ) || exit;

/**
 * Returns the raw database value of a plugin's option.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string          $field_id   The ID of the option field to retrieve.
 * @param   string|null     $group      The group to retrieve the setting from.
 *
 * @return  mixed|null
 */
function dws_lowc_get_raw_setting( string $field_id, ?string $group = null ) {
	$group = is_null( $group ) ? 'settings' : Strings::maybe_suffix( $group, '-settings' );
	return dws_lowc_component( $group )->get_option_value( $field_id );
}

/**
 * Returns the validated database value of a plugin's option.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string          $field_id   The ID of the option field to retrieve.
 * @param   string|null     $group      The group to retrieve the setting from.
 *
 * @return  mixed|null
 */
function dws_lowc_get_validated_setting( string $field_id, ?string $group = null ) {
	$group = is_null( $group ) ? 'settings' : Strings::maybe_suffix( $group, '-settings' );
	return dws_lowc_component( $group )->get_validated_option_value( $field_id );
}
