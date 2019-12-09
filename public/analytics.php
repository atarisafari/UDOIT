<?php
/**
*   Copyright (C) 2014 University of Central Florida, created by Jacob Bates, Eric Colon, Fenel Joseph, and Emily Sachs.
*
*   This program is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   This program is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
*   Primary Author Contact:  Jacob Bates <jacob.bates@ucf.edu>
*/

require_once('../config/settings.php');

session_start();
$user_id = $_SESSION['launch_params']['custom_canvas_user_id'];
UdoitUtils::$canvas_base_url = $_SESSION['base_url'];
session_write_close();

//Number of scans per month
$sth = UdoitDB::prepare("SELECT results, Date_completed, FROM {$db_job_queue_table} WHERE user_id = :user_id");
$sth->bindValue(":user_id", $user_id);
$sth->execute();
$jobs = $sth->fetchAll();