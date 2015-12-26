<?php
ini_set('memory_limit', '4000M');
if (count($argv) >= 2) {
    $filename = $argv[1];
    @$fp = fopen($filename, "r");
    if (false === $fp) {
        die("Failed to open file : {$filename}");
    }
    //read file.
    $issuer_map = [];
    while(!feof($fp)) {
        $line = fgets($fp);
        if (false === $line) {
            break;
        }
        $json_info = json_decode($line, true);
        if (!is_null($json_info) and count($json_info) > 0) {
            if (isset($json_info['parsed']['issuer'])) {
                $issuer_dn = 'Unknown';
                $discard_flag = false;
                if (isset($json_info['parsed']['issuer_dn'])) {
                    $issuer_dn = $json_info['parsed']['issuer_dn'];
                    $issuer_dn_arr = preg_split('/,\s/', $issuer_dn);
                    $keyname = [];
                    foreach ($issuer_dn_arr as $name) {
                        if (false !== strpos($name, 'CN=')) {
                            $ip_address = explode('CN=', $name)[1];
                            if (filter_var($ip_address, FILTER_VALIDATE_IP)) {
                                $discard_flag = true;
                                unset($ip_address);
                            }
                        }
                        if (false !== strpos($name, 'O=') 
                            or false !== strpos($name, 'OU=')
                            or false !== strpos($name, 'CN=')
                            or false !== strpos($name, 'C=')
                            ) {
                            $keyname[] = $name;
                        }
                    }
                    asort($keyname);
                    $issuer_dn = implode(',', $keyname);
                    unset($keyname);
                    unset($issuer_dn_arr);
                    unset($name);
                }
                if (!$discard_flag) {
                    if (isset($issuer_map[$issuer_dn])) {
                        $issuer_map[$issuer_dn]++;
                    } else {
                        $issuer_map[$issuer_dn] = 1;
                    }
                }
                unset($issuer_dn);
            }
        }
        unset($json_info);
        unset($line);
    }
    if ($fp) {
        fclose($fp);
    }
    print "Sorting the output..\n";
    arsort($issuer_map);
    print "Going to dump static data:\n";
    $filename = tempnam('./', 'output_');
    print $filename."\n";
    @$outfp = fopen($filename, "w");
    if (false === $outfp) {
        print "Failed to write to output file. Dump to stdout.\n";
        foreach ($issuer_map as $key => $value) {
            print  "{$key}, {$value}\n";
        }
    } else {
        fwrite($outfp, "Issuer Common Name, Number of Certificates\n");
        foreach ($issuer_map as $key => $value) {
            fwrite($outfp, "{$key}, {$value}\n");
            fflush($outfp);
        }
        fclose($outfp);
    }
} else {
    die("Please specify the filepath");
}
