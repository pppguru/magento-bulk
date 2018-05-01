<?php

    function loadSettings()
    {
        $xml = file_get_contents('../../app/etc/local.xml');

        $xmlDoc = new DomDocument();
        $xmlDoc->loadXML($xml);
        $xpath = new DOMXPath($xmlDoc);

        $settings = array();
        $settings['host'] = getSetting($xpath, "/config/global/resources/default_setup/connection/host");
        $settings['login'] = getSetting($xpath, "/config/global/resources/default_setup/connection/username");
        $settings['pass'] = getSetting($xpath, "/config/global/resources/default_setup/connection/password");
        $settings['db'] = getSetting($xpath, "/config/global/resources/default_setup/connection/dbname");
        $settings['prefix'] = getSetting($xpath, "/config/global/resources/default_setup/db/table_prefix");
		
        return $settings;
    }
	
	function getSetting($xpath, $path)
	{
		$res = iterator_to_array($xpath->evaluate($path));
		if (isset($res[0]))
			return $res[0]->nodeValue;
		else
			return "";
	}

    function getConnection($host, $login, $pass, $dbName)
    {
        $conn = mysql_connect($host, $login, $pass);
        mysql_select_db($dbName, $conn);
        return $conn;
    }

    $settings = loadSettings();
    $conn = getConnection($settings['host'], $settings['login'], $settings['pass'], $settings['db']);

    /*
    echo "<h1>Cron tab</h1>";
    echo "<pre>";
    exec('crontab -l', $output);
    var_dump($output);
    echo "</pre>";
    */

    echo "<h1>Crontab executions</h1>";
    echo "<ul>";

    $result = mysql_query('select distinct DATE_FORMAT(executed_at, "%d %b, %H:%i") as executed_at from '.$settings['prefix'].'cron_schedule order by executed_at desc', $conn);
    while ($row = mysql_fetch_assoc($result))
    {
        if ($row['executed_at'])
            echo "<li>".$row['executed_at'].'</li>';
    }
    echo "</ul>";

    echo "<h1>Cron executions details</h1>";
    echo '<table border="1" cellspacing="0" cellpadding="3" width="100%">';
    echo '<tr><th>Created at</th><th>Job</th><th>Status</th><th>Scheduled at</th><th>executed at</th><th>Duration (s)</th><th>Messages</th></tr>';
    $result = mysql_query('select * from '.$settings['prefix'].'cron_schedule order by scheduled_at desc', $conn);
    while ($row = mysql_fetch_assoc($result))
    {
        $bgColor = 'white';
        switch($row['status'])
        {
            case 'pending': $bgColor = 'orange'; break;
            case 'success': $bgColor = 'green'; break;
            default: $bgColor = 'green'; break;
        }

        $duration = strtotime($row['finished_at']) - strtotime($row['executed_at']);

        echo '<tr bgcolor="'.$bgColor.'">';
        echo "<td align='center'>".$row['created_at']."</td>";
        echo "<td>".$row['job_code']."</td>";
        echo "<td align='center'>".$row['status']."</td>";
        echo "<td align='center'>".$row['scheduled_at']."</td>";
        echo "<td align='center'>".$row['executed_at']."</td>";
        echo "<td align='center'>".$duration."</td>";
        echo "<td>".$row['messages']."</td>";
        echo "</tr>";
    }
    echo "</table>";
