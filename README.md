Reindexer4PostIndexer
=====================

Wordpress plugin that extends the functionality of Post Indexer (http://premium.wpmudev.org/project/post-indexer/)

Post Indexer is possibly the most powerful tool available for WordPress Multisite and BuddyPress. It takes all the posts across your network and brings them into one table 'wp_site_posts'

From this location it is easier to perform search queries across your entire blog network. 

However, Post Indexer only indexes posts that are created once the plugin has been activated - not old posts. Oooops! So if you have a blog network with thousands of posts, you are literally up shitcreek.

Reindexer4PostIndexer to the rescue! 

Reindexer4PostIndexer will iterate over every blog in your network and 'force' a reindex of every post. 

-   Only indexes posts of type POST
-   Extends Post Indexer by adding the column 'post_featured_image' to the 'wp_site_posts' table. This is the posts featured image.

