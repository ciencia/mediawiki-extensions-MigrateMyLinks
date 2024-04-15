# mediawiki-extensions-MigrateMyLinks
Extension to migrate external links pointing to the own wiki after moving it
to another host

When a page contains external links pointing to a defined host, it rewrites
them (only when viewing the page, not when editing) so they point to the
current URL of the wiki instead.

This is useful when people post external links pointing to the own wiki on
talk pages and such, and then you move your wiki to another URL, make those
links point to the current URL.

To install this extension, put the extension folder in a folder called
MigrateMyLinks inside the extensions folder, and add the following in
LocalSettings.php:

```lang=php
wfLoadExtension( 'MigrateMyLinks' );
# RevisionID to stop rewriting URLs.
# Set to -1 (the default) to rewrite all links
# Page revisions after this revision won't have the links rewritten
# Useful to stop rewriting URLs if your wiki is a fork and you allow links to
# the old URL after the fork. Note that editing a page having such a link will
# cause it to not be rewritten anymore, since the revision ID will be higher
$wgMigrateMyLinksMaxRevisionId = -1;
# Domain to rewrite links from, don't include protocol nor slashes
$wgMigrateMyLinksDomain = 'www.oldwikisite.com';
```
