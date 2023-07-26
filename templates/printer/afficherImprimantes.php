<?php

// discover_printers.php

$printers = array();

// IP range to scan (adjust as needed)
$startIP = '192.168.1.1';
$endIP = '192.168.1.255';

// SNMP community (default is 'public')
$community = 'public';

// SNMP OID for printer name
$oidName = '1.3.6.1.2.1.1.5.0';

// Loop through IP range and query printers
$currentIP = ip2long($startIP);
$endIP = ip2long($endIP);

while ($currentIP <= $endIP) {
    $ip = long2ip($currentIP);
    $snmp = @snmpget($ip, $community, $oidName, 500000, 1);

    if ($snmp !== false) {
        $printers[] = array(
            'ip' => $ip,
            'name' => $snmp,
        );
    }

    // Increment the IP address
    $currentIP = ($currentIP + 1) & 0xFFFFFFFF;
}

// Save the results to a file (or database)
file_put_contents('files/printers/printers.json', json_encode($printers));