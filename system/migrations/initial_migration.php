<?php

if (DB::query("SHOW TABLES LIKE 'todos'")->num_rows() > 0) {
	echo "Migration already run";
	return;
}

DB::migrate_query(
	"CREATE TABLE `settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`key` varchar(255) NOT NULL,
	`value` text NOT NULL
	);"
);

echo "Migration completed";
