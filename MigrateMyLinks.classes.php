<?php
/**
 * Class for MigrateMyLinks extension
 *
 * @file
 * @ingroup Extensions
 */

// MigrateMyLinks class
class MigrateMyLinks {

	/* Static fields */

	/**
	 * Array of regexp to replace URLs
	 *
	 * @var array
	 */
	private static $domainRegexp = null;

	/**
	 * Singleton instance of the class
	 *
	 * @var MigrateMyLinks
	 */
	private static $instance = null;

	/* Static functions */

	public static function onInternalParseBeforeLinks( Parser &$parser, &$text, &$stripState ) {
		global $wgMigrateMyLinksMaxRevisionId, $wgMigrateMyLinksDomain;
		if ( !$wgMigrateMyLinksDomain ) {
			return true;
		}
		// Initialize the class if there's no configured maximum revision ID,
		// or if the current page being parsed is from a revision within the range.
		// Skip parser objects without revisions to avoid interface messages.
		// NOTE: This won't work on page save: page will be saved in parser cache
		// untouched because the revision ID hasn't been assigned when the text
		// was parsed. However, this shouldn't matter for this extension since this
		// should work on old revisions only. Page purge would work just fine
		if ( !is_null( $parser->getRevisionId() ) &&
			( !$wgMigrateMyLinksMaxRevisionId  ||
			$parser->getRevisionId() <= $wgMigrateMyLinksMaxRevisionId )
		) {
			wfDebugLog( 'MigrateMyLinks', "Replacement class initialized for title {$parser->getTitle()}." );
			self::$instance = new MigrateMyLinks();
		}
		return true;
	}

	public static function onParserAfterParse( Parser &$parser, &$text, &$stripState ) {
		// Destroy instance so it doesn't perform further substitutions in other parser contexts
		self::$instance = null;
		return true;
	}

	public static function onLinkerMakeExternalLink( &$url, &$text, &$link, &$attribs, $linkType ) {
		if ( self::$instance ) {
			self::$instance->migrateTextLinks( $url, $text );
		}
		return true;
	}

	private function __construct() {
		if ( !self::$domainRegexp ) {
			$this->registerDomainRegexp();
		}
	}

	/* Methods */

	private function registerDomainRegexp() {
		global $wgMigrateMyLinksDomain;
		if ( !is_array( $wgMigrateMyLinksDomain ) ) {
			$wgMigrateMyLinksDomain = [ $wgMigrateMyLinksDomain ];
		}
		self::$domainRegexp = array_map( function( $domain ) {
			// Optional protocol http or https and optional trailing slash
			return sprintf( '/^(?:https?:)?\/\/%s(\/|$)/', preg_quote( $domain ) );
		}, $wgMigrateMyLinksDomain );
	}

	public function migrateTextLinks( &$url, &$text ) {
		global $wgServer;
		$replacement = "$wgServer\$1";
		$oldUrl = $url;
		$url = preg_replace( self::$domainRegexp, $replacement, $url, -1, $count );
		if ( $count > 0 ) {
			wfDebugLog( 'MigrateMyLinks', "URL replaced: {$oldUrl} --> {$url}." );
			// If url was replaced, try to replace also the text, in case it was
			// a plain link without a different text
			$text = preg_replace( self::$domainRegexp, $replacement, $text );
		}
	}
}
