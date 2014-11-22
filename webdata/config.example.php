<?php

/**
 * @file config.example.php
 *
 * This is an example of a config.php file.
 *
 * If you do not set your configuration through $ENV (i.e. Linode-way),
 * you need to copy this file and update the database URL string
 * according your own database.
 *
 */

putenv('DATABASE_URL=#mysql://database_user:database_pass@databas_host/database');
