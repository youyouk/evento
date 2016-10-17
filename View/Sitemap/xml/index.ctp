<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<url>
		<loc><?php echo Router::url('/',true); ?></loc>
		<changefreq>daily</changefreq>
		<priority>1.0</priority>
	</url>
<?php foreach ($events as $event) { ?>
	<url>
		<loc><?php
			echo Router::url(array('controller'=>'events', 'action'=>'view'
				, $event['Country']['slug'], $event['City']['slug'], $event['Venue']['slug']
				, $event['Event']['slug']), true);
		?></loc>
		<lastmod><?php echo $this->Time->toAtom($event['Event']['created']); ?></lastmod>
		<priority>0.8</priority>
	</url>
<?php } ?>
</urlset>