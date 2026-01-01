#!/usr/bin/env php
<?php

/**
 * Database Seeder Script
 *
 * Usage:
 *   php database/seed.php           - Add data to existing database
 *   php database/seed.php --clear    - Clear existing data first, then seed
 *
 * This script will seed the database with:
 * - 30 Items
 * - ~730 Recipes (over the last year, 1-3 recipes per day)
 * - ~3000+ Ingredients (3-8 ingredients per recipe)
 * - Transactions (automatically created via model events)
 */
$clearExisting = in_array('--clear', $argv);

require_once __DIR__.'/seeders/DatabaseSeeder.php';

$seeder = new DatabaseSeeder($clearExisting);
$seeder->run();
