<?php
/**
 * Plugin Name: Spotify Embed
 * Version: 0.2.1
 * Description: Just add Spotify links into your posts and the plugin will add the embed code for you.
 * Author: Dominik Schilling
 * Author URI: http://wphelper.de/
 * Plugin URI: http://wpgrafie.de/wp-plugins/spotify-embed/en/
 *
 * License: GPLv2 or later
 *
 *	Copyright (C) 2011-2012 Dominik Schilling
 *
 *	This program is free software; you can redistribute it and/or
 *	modify it under the terms of the GNU General Public License
 *	as published by the Free Software Foundation; either version 2
 *	of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * Don't call this file directly.
 */
if ( ! class_exists( 'WP' ) ) {
	die();
}

/**
 * The class.
 */
final class DS_Spotify_Embed {
	/**
	 * The Spotify API URL.
	 */
	private static $spotify_embed_url = 'https://embed.spotify.com/';

	/**
	 * Register the embed handler.
	 */
	public static function init() {
		wp_embed_register_handler(
			'spotify',
			'#https?:\/\/open\.spotify\.com\/(track|album|user\/.+?\/playlist)\/([a-z0-9]{22})\/?#i',
			array( __CLASS__, 'embed_handler_spotify' )
		);
	}

	/**
	 * Callback function for the spotify embeds. Prints the HTML embed code.
	 *
	 * @param  mixed $matches Matches through the regex search.
	 *               $matches[0] The Spotify URL.
	 *               $matches[1] The type of embed music (album, track or playlist).
	 *               $matches[2] The Spotify ID.
	 * @param  array $attr    Custom attributes from the [embed][/embed] shortcut
	 * @param  string $url    The matched URL.
	 * @param  array $rawattr Includes the height and width from media settings
	 *
	 * @return string         The HTML embed code.
	 */
	public static function embed_handler_spotify( $matches, $attr, $url, $rawattr ) {
		if ( empty( $matches ) )
			return;

		$type = str_replace( '/', ':', $matches[1] );
		$id = $matches[2];

		// Set the size of the embed iframe
		if ( ! empty( $attr['size'] ) && 'compact' == $attr['size'] ) {
			$width  = 250;
			$height = 80;
		} elseif ( ! empty( $attr['size'] ) && 'large' == $attr['size'] ) {
			$width  = 640;
			$height = 720;
		} else {
			if ( ! empty( $rawattr['width'] ) && ! empty( $rawattr['height'] ) ) {
				$width  = $rawattr['width'];
				$height = $rawattr['height'];
			} else {
				list( $width, $height ) = wp_expand_dimensions( 300, 380, $attr['width'], $attr['height'] );
			}
		}

		// Generate the base embed source
		$embed_src = sprintf(
			'%s?uri=spotify:%s:%s',
			self::$spotify_embed_url,
			$type,
			$id
		);

		// Check for custom settings
		if ( in_array( 'coverart', $attr ) )
			$embed_src = add_query_arg( 'view', 'coverart', $embed_src );

		if ( in_array( 'light', $attr ) )
			$embed_src = add_query_arg( 'theme', 'white', $embed_src );

		// The embed code
		return sprintf(
			'<iframe src="%s" width="%d" height="%d" frameborder="0" allowTransparency="true"></iframe>',
			esc_url( $embed_src ),
			(int) $width,
			(int) $height
		);
	}
}

// Gogogo.
add_action( 'plugins_loaded', array( 'DS_Spotify_Embed', 'init' ) );
