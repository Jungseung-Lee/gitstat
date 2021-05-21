<?
//
// This 'library' file is intended to be used for data gathering.
// Currently all data is (only) retrieved from MySQL.
//

//
// Retrieve the number of commits per day.
//
// Input:
//	$showcount	- The number of items per page
//	$page		- The page we should show. The last page is 0.
//
// Output:
//	$commitarray	- Array that contains the day and the number of commits for that day.
//
// Remarks:
//
// The database is opened by the caller of this function
//
function commits_per_day($showcount, $page) {

	// Get the day after the last commit
	$query = "SELECT MAX(`commitdate`) FROM `ChangeLog`";
	$result = mysql_result(mysql_query($query), 0, 0);
	$data = gmmktime(0,0,0, gmdate("m",$result), gmdate("d",$result)+1, gmdate("Y",$result));
	$endofgraph = $data - ($page * $showcount * 86400);
	$beginofgraph = $endofgraph - ($showcount * 86400);
	
	$commitarray = array();
	// Get the number of commits for every day in the graph
	$dayingraph = $beginofgraph;
	while ($dayingraph < $endofgraph) {

		$query = "SELECT COUNT(`commitdate`) FROM `ChangeLog` WHERE `commitdate` >= $dayingraph AND `commitdate` < ($dayingraph + 86400)";
		$result = @mysql_query($query);
		$data = @mysql_fetch_row($result);

		$commitarray[$dayingraph] = $data[0];

		// Add one day
		$dayingraph += 86400;
	}

	return $commitarray;
}

//
// Retrieve the number of commits per month.
//
// Input:
//	$showcount	- The number of items per page
//	$page		- The page we should show. The last page is 0.
//
// Output:
//	$commitarray	- Array that contains the month and the number of commits for that month.
//
// Remarks:
//
// The database is opened by the caller of this function
//
function commits_per_month($showcount, $page) {

	// Get the number of months between first and last commit
	$query = "SELECT PERIOD_DIFF(FROM_UNIXTIME(MAX(`commitdate`),'%Y%m'), FROM_UNIXTIME(MIN(`commitdate`),'%Y%m')) FROM `ChangeLog`";
	$monthcount = mysql_result(mysql_query($query), 0, 0) + 1;

	// Calculate where we are.
	$monthindex = $monthcount - ($page + 1) * $showcount;
	// Don't go to far back in time
	if ($monthindex < 0)
		$monthindex = 0;

	// Get the last commitdate
	$query = "SELECT MAX(`commitdate`) FROM `ChangeLog`";
	$lastcommitdate = mysql_result(mysql_query($query), 0, 0);

	// Get $showcount months where start and end depend on the index
	for ($month = 1; $month <= $showcount; $month++) {

		$start = $monthcount - $monthindex - $month;
		$startmonth = gmmktime(0, 0, 0, date('n', $lastcommitdate) - $start, 1, date('Y', $lastcommitdate));
		$end = $start - 1;
		$endmonth = gmmktime(0, 0, 0, date('n', $lastcommitdate) - $end, 1, date('Y', $lastcommitdate));

		//Fix me: should gitstat calculate changeset icluding 'merging commit'? 
		//For cosistency with Number of changesets (Day) 
		//removed query [ AND 'path' != '' ]
		$query = "SELECT COUNT(`commitdate`) AS commits FROM `ChangeLog`
			WHERE `commitdate` >= $startmonth AND `commitdate` < $endmonth";

		$result = mysql_query($query);
		$data = mysql_fetch_array($result);

		$commitarray[$startmonth] = $data[0];
	}
	return $commitarray;
}

//
// Retrieve the number of commits per release.
//
// Input:
//	$showcount	- The number of items per page
//	$page		- The page we should show. The last page is 0.
//
// Output:
//	$commitarray	- Array that contains the name of release and the number of commits for that release.
//
// Remarks:
//
// The database is opened by the caller of this function
//
function commits_per_release($showcount, $page) {

	// Get the number of visible releases/tags
	$query = "SELECT COUNT(*) FROM `v_tag` WHERE ((`epoch` IS NOT NULL) AND (`visible` = TRUE))";
	$totalcount = mysql_result(mysql_query($query), 0, 0);

	$start = $totalcount - ($page + 1) * $showcount;
	// Don't go too far back
	if ($start < 0)
		$start = 0;

	// Retrieve all releases and the count of the commits for that same release
	$query = "SELECT t1.`name`, t1.`visible`, COUNT(t2.`commit`)
		AS commits FROM `v_tag` AS `t1` INNER JOIN
		`ChangeLog` AS `t2` ON t1.`no` = t2.`version`
		GROUP BY t1.`name` ORDER BY t1.`epoch`";
	$result = mysql_query($query);

	$commitcount = 0;
	$visiblecount = 0;
	while ($data = mysql_fetch_array($result)) {

		$commitcount += $data['commits'];
		// If this release is visible than the number of commits for this release
		// is the sum of all releases after the previous visible one up to this one
		if ($data['visible'] == true) {

               		if (($visiblecount >= $start) && ($visiblecount < $start + $showcount)) {

				$commitarray[$data['name']] = $commitcount;
			}
			$commitcount = 0;
			$visiblecount++;
		}
	}

	return $commitarray;
}

//
// Retrieve the number of commits per hour per release.
//
// Input:
//	$showcount	- The number of items per page
//	$page		- The page we should show. The last page is 0.
//
// Output:
//	$commitarray	- Array that contains the name of release and the number of commits per hour for that release.
//
// Remarks:
//
// The database is opened by the caller of this function
//
// TODO : Merge this somehow with commits_per_release as they are very similar.
//
function hourly_commits_per_release($showcount, $page) {

	// Get the number of visible releases/tags
	$query = "SELECT COUNT(*) FROM `v_tag` WHERE ((`epoch` IS NOT NULL) AND (`visible` = TRUE))";
	$totalcount = mysql_result(mysql_query($query), 0, 0);

	$start = $totalcount - ($page + 1) * $showcount;
	// Don't go too far back, we compare to 1 as we skip the first visible release
	if ($start < 1)
		$start = 1;

	// Retrieve all releases and the count of the commits for that same release
	$query = "SELECT t1.`name`, t1.`epoch`, t1.`visible`, COUNT(t2.`commit`)
		AS commits FROM `v_tag` AS `t1` INNER JOIN
		`ChangeLog` AS `t2` ON t1.`no` = t2.`version`
		GROUP BY t1.`name` ORDER BY t1.`epoch`";
	$result = mysql_query($query);

	$commitcount = 0;
	$visiblecount = 0;
	$epoch = 0;
	while ($data = mysql_fetch_array($result)) {

		$commitcount += $data['commits'];
		// If this release is visible than the number of commits for this release
		// is the sum of all releases after the previous visible one up to this one
		if ($data['visible'] == true) {

			// If it's the first one we skip it (more-or-less) as we can't calculate the
			// time difference
			if ($epoch == 0) {

				$epoch = $data['epoch'];
			} else {

				$epochdiff = $data['epoch'] - $epoch;
				$epoch = $data['epoch'];

				if (($visiblecount >= $start) && ($visiblecount < $start + $showcount)) {

					$commitarray[$data['name']] = $commitcount / ceil($epochdiff / ( 60 * 60));
				}
			}
			$commitcount = 0;
			$visiblecount++;
		}
	}

	return $commitarray;
}

//
// Retrieve the time between each release
//
// Input:
//	$showcount	- The number of items per page
//	$page		- The page we should show. The last page is 0.
//
// Output:
//	$releasearray	- Array that contains the name of the release and the time since the previous release (in days)
//
// Remarks:
//
// The database is opened by the caller of this function
//
function release_frequency($showcount, $page) {

	// Get the number of releases/tags
	$query = "SELECT COUNT(*) AS count FROM `v_tag` WHERE ((`epoch` IS NOT NULL) AND (`visible` = TRUE))";
	$totalcount = mysql_result(mysql_query($query), 0, 0);

	// 'Calculate' how many releases we should show
	$start = $totalcount - (($page + 1) * $showcount) - 1;
	if ($start < 0)
		$start = 0;

	// Get only the releases/tags we want to see
	$query = "SELECT `name`, `epoch` FROM `v_tag` WHERE ((`epoch` IS NOT NULL) AND (`visible` = TRUE)) ORDER BY `epoch` LIMIT $start,".($showcount + 1);
	$result = mysql_query($query);

	$isfirst = TRUE;
	while ($data = mysql_fetch_array($result)) {

		if ($isfirst) {

			$prevepoch = $data['epoch'];
			$isfirst = FALSE;
			continue;
		}

		$intervalepoch = $data['epoch'] - $prevepoch;
		$prevepoch = $data['epoch'];
		$intervalepochday = ceil($intervalepoch / (60 * 60 * 24));

		$releasearray[$data['name']] = $intervalepochday;
	}

	return $releasearray;
}

//
// Retrieve the last release
//
// Output:
//	$lastrelease	- A string containing the name of the last release
//
// Remarks:
//
// The database is opened by the caller of this function
//
function last_release() {

	// Get the last visible release
	$query = "SELECT `name` FROM `v_tag` WHERE ((`epoch` IS NOT NULL) AND (`visible` = TRUE)) ORDER BY `epoch` DESC LIMIT 1";
	$lastrelease = mysql_result(mysql_query($query), 0, 0);

	return $lastrelease;
}

//
// Retrieve the authors between two dates
//
// Input:
//	$startdate	- The startdate
//	$endate		- The enddate
//
// Output:
//	$authorarray	- Array that contains the name of the author and the number of commits
//
// Remarks:
//
// The database is opened by the caller of this function
//
// TODO : Merge this somehow with authors_by_release and maybe even with domains_by_[date|release]
//        as they are very similar.
//
function authors_by_date($startdate, $enddate) {

	// Get the authors
	$query = "SELECT `author` FROM `ChangeLog` WHERE `commitdate` >= $startdate AND `commitdate` < $enddate AND `path` != ''";
	$result = mysql_query($query);

	while ($data = mysql_fetch_array($result)) {

		$author = preg_replace("/(.*)<(.*)@(.*)>/","$1",$data['author']);
		$authorarray[$author]++;
	}

	// Sort the array
	arsort($authorarray);

	return $authorarray;
}

//
// Retrieve the authors for a particular release
//
// Input:
//	$release	- The release for which we want the authors
//
// Output:
//	$authorarray	- Array that contains the name of the author and the number of commits
//
// Remarks:
//
// The database is opened by the caller of this function
//
// As we are now working with visible releases it's not just a matter of getting the commits
// for a release. We have to get the authors for all releases between the given one and the previous
// visible release. There could be several releases in between. An example is the kernel where most of 
// the time we don't want to see the Release Candidates. So if we want to see the authors for v2.6.24 we 
// actually have to look for v2.6.24-rc1 .. v2.6.24 (so after v2.6.23).
//
function authors_by_release($release) {

	// Get all the visible releases
	$query = "SELECT `no`, `name`, `epoch` FROM `v_tag` WHERE ((`epoch` IS NOT NULL) AND (`visible` = TRUE)) ORDER BY `epoch` DESC";
	$result = mysql_query($query);

	// Search for the release we want
	while ($data = mysql_fetch_array($result)) {

		if ($data['name'] == $release) {

			// Database number of the tag we want to see
			$end_tag = $data['no'];
			break;
		}
	}

	// Get the previous visible release
	$data = mysql_fetch_array($result);
	// Database number of the previous visible release
	$start_tag = $data['no'];

	// Get the authors
	$query = "SELECT `author` FROM `ChangeLog` WHERE `version` > $start_tag AND `version` <= $end_tag AND `path` != ''";
	$result = mysql_query($query);

	while ($data = mysql_fetch_array($result)) {

		$author = preg_replace("/(.*)<(.*)@(.*)>/","$1",$data['author']);
		$authorarray[$author]++;
	}

	// Sort the array
	arsort($authorarray);

	return $authorarray;
}

//
// Retrieve the number of authors per release
//
// Input:
//	$showcount	- The number of items per page
//	$page		- The page we should show. The last page is 0.
//
// Output:
//	$releasearray	- Array that contains the name of the release and the number of authors
//
// Remarks:
//
// The database is opened by the caller of this function
//
function authorcount_by_release($showcount, $page) {

	// Get the number of visible releases/tags
	$query = "SELECT COUNT(*) FROM `v_tag` WHERE ((`epoch` IS NOT NULL) AND (`visible` = TRUE))";
	$totalcount = mysql_result(mysql_query($query), 0, 0);

	$start = $totalcount - ($page + 1) * $showcount;
	// Don't go too far back, we compare to 1 as we skip the first visible release
	if ($start < 1)
		$start = 1;

	// Get the visible releases/tags
	$query = "SELECT `name`, `no` FROM `v_tag` WHERE ((`epoch` IS NOT NULL) AND (`visible` = TRUE))";
	$result = mysql_query($query);

	$firsttag = true;
	$visiblecount = 0;
	while ($data = mysql_fetch_array($result)) {

		if ($firsttag == true) {

			$starttag = $data['no'];
			$firsttag = false;
			$visiblecount++;
			continue;
		}

		$endtag = $data['no'];

		if (($visiblecount >= $start) && ($visiblecount < $start + $showcount)) {

			// Get distinct authors. We don't make a distinction between real commits and merges
			// as both should count as authors on this project
			$query2 = "SELECT DISTINCT(`author`) AS distinctauthor FROM `ChangeLog`
				WHERE `version` > $starttag AND `version` <= $endtag
				GROUP BY distinctauthor";
			$result2 = mysql_query($query2);

			$temparray = array();
			while ($data2 = mysql_fetch_array($result2)) {

				// We parse the authors as some have changed their email address or use different
				// email addresses.
				// FIXME: We need better ways to distinguish between authors/domains
				//
				$author = preg_replace("/(.*) <(.*)@(.*)>/","$1",$data2['distinctauthor']);

				$temparray[$author]++;
			}

			$releasearray[$data['name']] = count($temparray);
		}

		$starttag = $endtag;
		$visiblecount++;
	}

	return $releasearray;
}

//
// Retrieve the domains between two dates
//
// Input:
//	$startdate	- The startdate
//	$endate		- The enddate
//
// Output:
//	$domainarray	- Array that contains the name of the domain and the number of commits
//
// Remarks:
//
// The database is opened by the caller of this function
//
// TODO : Merge this somehow with domains_by_release and maybe even with authors_by_release
//        as they are very similar.
//
function domains_by_date($startdate, $enddate) {

	// Get the authors
	$query = "SELECT `author` FROM `ChangeLog` WHERE `commitdate` >= $startdate AND `commitdate` < $enddate AND `path` != ''";
	$result = mysql_query($query);

	while ($data = mysql_fetch_array($result)) {

		// There are several entries for author that contain spaces in the mail, have
		// multiple authors or don't contain a @.
		// For example:
		// commit 2c8a3a33558d3f5aa18b56eada66fbe712ee6bb7 in the 2.6 kernel (names obfuscated)
		// 		name at host.domain
		// commit 73d72cffe53407e447df0cbb0bf15a2c931108b3 in the 2.6 kernel 
		// 		firstname lastname user@x.y.org
		// commit 00b3b3e6605d7446cd410c7c9bb98f5336a15ca1 in the 2.6 kernel
		//		user1@domain, user2@domain
		// 
		// Some commits have unexpected data in the author field
		// like commit 45ae5e968ea01d8326833ca2863cec5183ce1930 from the linux 2.6 kernel
		//
		// FIXME : We need a better way to get the domain part

		if (strstr($data['author'], "@")) {

			$domain = preg_replace("/(.*) <(.*)@(.*)>/","$3",$data['author']);
			$domainarray[$domain]++;
		}
	}

	// Sort the array
	arsort($domainarray);

	return $domainarray;
}

//
// Retrieve the domains for a particular release
//
// Input:
//	$release	- The release for which we want the authors
//
// Output:
//	$domainarray	- Array that contains the name of the author and the number of commits
//
// Remarks:
//
// The database is opened by the caller of this function
//
// See also the remark for authors_by_release
//
function domains_by_release($release) {

	// Get all the visible releases
	$query = "SELECT `no`, `name`, `epoch` FROM `v_tag` WHERE ((`epoch` IS NOT NULL) AND (`visible` = TRUE)) ORDER BY `epoch` DESC";
	$result = mysql_query($query);

	// Search for the release we want
	while ($data = mysql_fetch_array($result)) {

		if ($data['name'] == $release) {

			// Database number of the tag we want to see
			$end_tag = $data['no'];
			break;
		}
	}

	// Get the previous visible release
	$data = mysql_fetch_array($result);
	// Database number of the previous visible release
	$start_tag = $data['no'];

	// Get the authors
	$query = "SELECT `author` FROM `ChangeLog` WHERE `version` > $start_tag AND `version` <= $end_tag AND `path` != ''";
	$result = mysql_query($query);

	while ($data = mysql_fetch_array($result)) {

		// There a several entries for author that contain spaces in the mail, have
		// multiple authors or don't contain a @.
		// For example:
		// commit 2c8a3a33558d3f5aa18b56eada66fbe712ee6bb7 in the 2.6 kernel (names obfuscated)
		// 		name at host.domain
		// commit 73d72cffe53407e447df0cbb0bf15a2c931108b3 in the 2.6 kernel 
		// 		firstname lastname user@x.y.org
		// commit 00b3b3e6605d7446cd410c7c9bb98f5336a15ca1 in the 2.6 kernel
		//		user1@domain, user2@domain
		// 
		// FIXME : We need a better way to get the domain part

		if (strstr($data['author'], "@")) {

			$domain = preg_replace("/(.*) <(.*)@(.*)>/","$3",$data['author']);
			$domainarray[$domain]++;
		}
	}

	// Sort the array
	arsort($domainarray);

	return $domainarray;
}

//
// Special sorting of internal used array. Helper for directory_changes_by_date.
//
// Remarks:
//
// We sort on the number of 'Changes'. If they are equal we sort on 'Name'.
//
function infosort($x, $y) {

	if ($x['Changes'] == $y['Changes'])
		return strcasecmp($x['Name'], $y['Name']);

	return ($x['Changes'] < $y['Changes']);
}

//
// Retrieve the changes to files in a specific timeframe
//
// Input:
//	$startdate	- The startdate
//	$endate		- The enddate
//	$level		- How deep are we in the directory structure
//	$filter		- What do we want to see
//
// Output:
//	$changesarray	- Array that contains information about the changes:
//
//  <directory>	=> 'Name'			(Name of the directory)
//				=> 'Changes'		(Number of changes done in the directory and below)
//				=> 'ChangesInDir'	(Number of changes in the directory itself)
//				=> 'SubDirectories'	(Number of subdirectories)
//
// Remarks:
//
// The database is opened by the caller of this function
//
function directory_changes_by_date($startdate, $enddate, $level, $filter) {

	$query = "
		SELECT  COUNT(s1.`subcategory1`) AS subcount,
		s1.`subcategory1`, s2.`subcategory2`, s3.`subcategory3`, s4.`subcategory4`
		FROM Logcategory AS t1 INNER JOIN ChangeLog AS t2 ON t1.`commit` = t2.`commit`
		LEFT JOIN `category1` AS s1 ON t1.`subcategory1` = s1.`no`
		LEFT JOIN `category2` AS s2 ON t1.`subcategory2` = s2.`no`
		LEFT JOIN `category3` AS s3 ON t1.`subcategory3` = s3.`no`
		LEFT JOIN `category4` AS s4 ON t1.`subcategory4` = s4.`no`
		WHERE t2.`commitdate` >= $startdate AND t2.`commitdate` < $enddate
		GROUP BY s1.`subcategory1`, s2.`subcategory2`, s3.`subcategory3`, s4.`subcategory4`
		ORDER BY s1.`subcategory1`, s2.`subcategory2`, s3.`subcategory3`, s4.`subcategory4`";

	// Things we need to figure out
	//
	// - Number of commits to files in the directory itself
	// - Number of commits to all underlying files (including in subdirectories) of this directory
	// - Whether we need to show a link to the next directory. Only if:
	//   - There is more than 1 subdirectory
	//   - There is 1 subdirectory and there are commits (1 or more) to files in that next directory

	$totalcount = 0;
	$result = mysql_query($query);
	while ($data = mysql_fetch_array($result)) {

		//
		// FIXME: We don't store commits to the root of the git tree into the Logcategory table
		//
		if ($level == 1)
			$levelstring = $data['subcategory0'];
		else if ($level == 2)
			$levelstring = $data['subcategory0']."/".$data['subcategory1'];
		else if ($level == 3)
			$levelstring = $data['subcategory0']."/".$data['subcategory1']."/".$data['subcategory2'];
		else if ($level == 4)
			$levelstring = $data['subcategory0']."/".$data['subcategory1']."/".$data['subcategory2']."/".$data['subcategory3'];

		if ($levelstring != $filter) {
			// Skip the ones we don't want
			continue;
		}

		if (!$data['subcategory'.$level]) {
			// We have some commits for files in the current directory itself
			$changesarray['Files in this directory'][Changes] = $data['subcount'];
			// Count all commits in the current directory
			$totalcount += $data['subcount'];
			continue;
		}

		// Count all commits below the current directory
		$totalcount += $data['subcount'];

		// Add the name of the subdirectory to the array to make the sorting easier
		$changesarray[$data['subcategory'.$level]][Name] = $data['subcategory'.$level];

		// Calc the total of commits in this subdirectory (and it's subdirectories)
		$changesarray[$data['subcategory'.$level]][Changes] += $data['subcount'];

		if (!$data['subcategory'.($level + 1)]) {
			// We have some commits in a subdirectory
			$changesarray[$data['subcategory'.$level]][ChangesInDir] = $data['subcount'];
		} else if (!$data['subcategory'.($level + 2)]) {
			// There is a subdirectory in the subdirectory
			$changesarray[$data['subcategory'.$level]][SubDirectories]++;
		}
	}

	// Special sorting
	uasort($changesarray, 'infosort');

	return $changesarray;
}

//
// Retrieve the changes to files for a specific release
//
// Input:
//	$release	- The release for which we want the changes to files
//	$level		- How deep are we in the directory structure
//	$filter		- What do we want to see
//
// Output:
//	$changesarray	- Array that contains information about the changes:
//
// Remarks:
//
// The database is opened by the caller of this function
//
function directory_changes_by_release($release, $level, $filter) {

	// Get all the visible releases
	$query = "SELECT `name`, `epoch` FROM `v_tag` WHERE ((`epoch` IS NOT NULL) AND (`visible` = TRUE)) ORDER BY `epoch` DESC";
	$result = mysql_query($query);

	// Search for the release we want
	while ($data = mysql_fetch_array($result)) {

		if ($data['name'] == $release) {

			// Database number of the tag we want to see
			$enddate = $data['epoch'];
			break;
		}
	}

	// Get the previous visible release
	$data = mysql_fetch_array($result);
	// Database number of the previous visible release
	$startdate = $data['epoch'];

	$changesarray = directory_changes_by_date($startdate, $enddate, $level, $filter);

	return $changesarray;
}
?>
